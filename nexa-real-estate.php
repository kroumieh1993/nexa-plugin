<?php
/**
 * Plugin Name: Nexa Real Estate
 * Description: Connects your site to the Nexa real estate SaaS and displays properties via shortcodes.
 * Version: 0.1.0
 * Author: Kassem
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEXA_RE_VERSION', '0.1.0' );
define( 'NEXA_RE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEXA_RE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register REST API endpoints for image management.
 * These endpoints are used by the SaaS API to upload and delete images
 * when properties are created or updated through the SaaS dashboard.
 */
add_action( 'rest_api_init', 'nexa_re_register_rest_routes' );

function nexa_re_register_rest_routes() {
    register_rest_route( 'nexa-plugin/v1', '/upload-image', [
        'methods'             => 'POST',
        'callback'            => 'nexa_re_upload_image',
        'permission_callback' => 'nexa_re_verify_agency_token',
    ] );

    register_rest_route( 'nexa-plugin/v1', '/delete-image', [
        'methods'             => 'DELETE',
        'callback'            => 'nexa_re_delete_image',
        'permission_callback' => 'nexa_re_verify_agency_token',
    ] );

    register_rest_route( 'nexa-plugin/v1', '/upload-media', [
        'methods'             => 'POST',
        'callback'            => 'nexa_re_upload_media',
        'permission_callback' => 'nexa_re_verify_media_token',
    ] );
}

/**
 * Verify the X-AGENCY-TOKEN header matches the configured token.
 *
 * @param WP_REST_Request $request The incoming request.
 * @return bool True if token matches, false otherwise.
 */
function nexa_re_verify_agency_token( WP_REST_Request $request ) {
    $token = $request->get_header( 'X-AGENCY-TOKEN' );
    $expected_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );

    if ( empty( $expected_token ) ) {
        return false;
    }

    return hash_equals( $expected_token, $token );
}

/**
 * Verify the X-WP-MEDIA-TOKEN header matches the configured media token.
 *
 * @param WP_REST_Request $request The incoming request.
 * @return bool True if token matches, false otherwise.
 */
function nexa_re_verify_media_token( WP_REST_Request $request ) {
    $token = $request->get_header( 'X-WP-MEDIA-TOKEN' );
    $expected_token = trim( get_option( Nexa_RE_Settings::OPTION_MEDIA_TOKEN, '' ) );

    if ( empty( $expected_token ) ) {
        return false;
    }

    return hash_equals( $expected_token, $token );
}

/**
 * Handle image upload from the SaaS API.
 *
 * @param WP_REST_Request $request The incoming request.
 * @return WP_REST_Response|WP_Error The response or error.
 */
function nexa_re_upload_image( WP_REST_Request $request ) {
    $files = $request->get_file_params();

    if ( empty( $files['image'] ) ) {
        return new WP_Error( 'no_image', 'No image file provided.', [ 'status' => 400 ] );
    }

    $file = $files['image'];

    // Validate file extension
    $allowed_extensions = [ 'jpg', 'jpeg', 'png', 'gif', 'webp' ];
    $file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
    if ( ! in_array( $file_extension, $allowed_extensions, true ) ) {
        return new WP_Error( 'invalid_extension', 'Only image files are allowed (JPG, PNG, GIF, WebP).', [ 'status' => 400 ] );
    }

    // Validate MIME type - only allow images
    $allowed_types = [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ];
    $finfo = new finfo( FILEINFO_MIME_TYPE );
    $file_mime = $finfo->file( $file['tmp_name'] );

    if ( ! in_array( $file_mime, $allowed_types, true ) ) {
        return new WP_Error( 'invalid_type', 'Only image files are allowed (JPEG, PNG, GIF, WebP).', [ 'status' => 400 ] );
    }

    // Include necessary WordPress file handling functions
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    // Use wp_handle_upload to process the file
    $upload_overrides = [ 'test_form' => false ];
    $uploaded_file = wp_handle_upload( $file, $upload_overrides );

    if ( isset( $uploaded_file['error'] ) ) {
        return new WP_Error( 'upload_failed', $uploaded_file['error'], [ 'status' => 500 ] );
    }

    // Create an attachment
    $attachment = [
        'post_mime_type' => $uploaded_file['type'],
        'post_title'     => sanitize_file_name( pathinfo( $uploaded_file['file'], PATHINFO_FILENAME ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attachment_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );

    if ( is_wp_error( $attachment_id ) ) {
        return new WP_Error( 'attachment_failed', 'Failed to create attachment.', [ 'status' => 500 ] );
    }

    // Generate attachment metadata
    $attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $uploaded_file['file'] );
    wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

    return rest_ensure_response( [
        'success'       => true,
        'attachment_id' => $attachment_id,
        'url'           => $uploaded_file['url'],
    ] );
}

/**
 * Handle image deletion from the SaaS API.
 *
 * @param WP_REST_Request $request The incoming request.
 * @return WP_REST_Response|WP_Error The response or error.
 */
function nexa_re_delete_image( WP_REST_Request $request ) {
    $url = $request->get_param( 'url' );

    if ( empty( $url ) ) {
        return new WP_Error( 'no_url', 'No image URL provided.', [ 'status' => 400 ] );
    }

    // Validate URL format
    $url = esc_url_raw( $url );
    if ( empty( $url ) ) {
        return new WP_Error( 'invalid_url', 'Invalid URL format.', [ 'status' => 400 ] );
    }

    // Validate URL belongs to this site to prevent deletion of external images
    $parsed_url = wp_parse_url( $url );
    $site_url = wp_parse_url( home_url() );

    if ( ! isset( $parsed_url['host'] ) || $parsed_url['host'] !== $site_url['host'] ) {
        return new WP_Error( 'invalid_url', 'URL does not belong to this site.', [ 'status' => 400 ] );
    }

    // Find attachment by URL
    $attachment_id = attachment_url_to_postid( $url );

    if ( ! $attachment_id ) {
        return new WP_Error( 'not_found', 'Image not found.', [ 'status' => 404 ] );
    }

    // Delete the attachment and its files
    $deleted = wp_delete_attachment( $attachment_id, true );

    if ( ! $deleted ) {
        return new WP_Error( 'delete_failed', 'Failed to delete image.', [ 'status' => 500 ] );
    }

    return rest_ensure_response( [
        'success' => true,
        'message' => 'Image deleted successfully.',
    ] );
}

/**
 * Handle media upload from external API via X-WP-MEDIA-TOKEN authentication.
 *
 * @param WP_REST_Request $request The incoming request.
 * @return WP_REST_Response|WP_Error The response or error.
 */
function nexa_re_upload_media( WP_REST_Request $request ) {
    $files = $request->get_file_params();

    if ( empty( $files['file'] ) ) {
        return new WP_Error( 'no_file', 'No file provided.', [ 'status' => 400 ] );
    }

    $file = $files['file'];

    // Validate file extension
    $allowed_extensions = [ 'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf' ];
    $file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
    if ( ! in_array( $file_extension, $allowed_extensions, true ) ) {
        return new WP_Error( 'invalid_extension', 'Only image files (JPG, PNG, GIF, WebP) and PDF are allowed.', [ 'status' => 400 ] );
    }

    // Validate MIME type - allow images and PDF
    $allowed_types = [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf' ];
    
    // Check if fileinfo extension is available
    if ( ! class_exists( 'finfo' ) ) {
        return new WP_Error( 'server_error', 'File validation unavailable. Please contact the administrator.', [ 'status' => 500 ] );
    }
    
    $finfo = new finfo( FILEINFO_MIME_TYPE );
    $file_mime = $finfo->file( $file['tmp_name'] );
    
    // Validate that finfo returned a valid MIME type
    if ( false === $file_mime ) {
        return new WP_Error( 'invalid_file', 'Could not determine file type.', [ 'status' => 400 ] );
    }

    if ( ! in_array( $file_mime, $allowed_types, true ) ) {
        return new WP_Error( 'invalid_type', 'Only image files (JPEG, PNG, GIF, WebP) and PDF are allowed.', [ 'status' => 400 ] );
    }

    // Include necessary WordPress file handling functions
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    // Use wp_handle_upload to process the file
    $upload_overrides = [ 'test_form' => false ];
    $uploaded_file = wp_handle_upload( $file, $upload_overrides );

    if ( isset( $uploaded_file['error'] ) ) {
        return new WP_Error( 'upload_failed', $uploaded_file['error'], [ 'status' => 500 ] );
    }

    // Create an attachment
    $attachment = [
        'post_mime_type' => $uploaded_file['type'],
        'post_title'     => sanitize_file_name( pathinfo( $uploaded_file['file'], PATHINFO_FILENAME ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attachment_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );

    if ( is_wp_error( $attachment_id ) ) {
        return new WP_Error( 'attachment_failed', 'Failed to create attachment.', [ 'status' => 500 ] );
    }

    // Generate attachment metadata
    $attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $uploaded_file['file'] );
    wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

    return rest_ensure_response( [
        'success'       => true,
        'attachment_id' => $attachment_id,
        'url'           => $uploaded_file['url'],
    ] );
}

// Frontend styles for Nexa single Property pages.
function nexa_re_enqueue_frontend_assets() {
    // Always load the layout styles for property cards and single pages
    wp_enqueue_style(
        'nexa-re-layouts',
        NEXA_RE_PLUGIN_URL . 'assets/css/nexa-layouts.css',
        [],
        NEXA_RE_VERSION
    );

    // Only load single property specific styles on our single-property view.
    if ( get_query_var( 'nexa_property' ) ) {
        wp_enqueue_style(
            'nexa-re-single-property',
            NEXA_RE_PLUGIN_URL . 'assets/css/nexa-single-property.css',
            [ 'nexa-re-layouts' ],
            NEXA_RE_VERSION
        );
    }
}
add_action( 'wp_enqueue_scripts', 'nexa_re_enqueue_frontend_assets' );

/**
 * Enqueue Leaflet or Google Maps assets for pages that need maps.
 * 
 * This function should be called on pages that display property maps:
 * - Single property pages with location data
 * - Properties list pages with map view enabled
 * - Admin property forms with location picker
 * 
 * The function checks the configured map provider (Leaflet or Google Maps)
 * and enqueues the appropriate CSS and JavaScript files.
 *
 * @since 0.1.0
 * @return void
 */
function nexa_re_enqueue_map_assets() {
    $map_provider = Nexa_RE_Settings::get_map_provider();

    if ( 'leaflet' === $map_provider ) {
        // Enqueue Leaflet CSS
        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            [],
            '1.9.4'
        );

        // Enqueue Leaflet JS
        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            [],
            '1.9.4',
            true
        );

        // Enqueue MarkerCluster CSS
        wp_enqueue_style(
            'leaflet-markercluster',
            'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css',
            [ 'leaflet' ],
            '1.5.3'
        );
        wp_enqueue_style(
            'leaflet-markercluster-default',
            'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css',
            [ 'leaflet-markercluster' ],
            '1.5.3'
        );

        // Enqueue MarkerCluster JS
        wp_enqueue_script(
            'leaflet-markercluster',
            'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js',
            [ 'leaflet' ],
            '1.5.3',
            true
        );
    } elseif ( 'google' === $map_provider ) {
        $api_key = Nexa_RE_Settings::get_google_maps_key();
        if ( ! empty( $api_key ) ) {
            wp_enqueue_script(
                'google-maps',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_key ) . '&libraries=marker',
                [],
                null,
                true
            );
        }
    }

    // Enqueue our custom map CSS
    wp_enqueue_style(
        'nexa-re-map',
        NEXA_RE_PLUGIN_URL . 'assets/css/nexa-map.css',
        [],
        NEXA_RE_VERSION
    );
}



require_once NEXA_RE_PLUGIN_DIR . 'includes/class-nexa-settings.php';
require_once NEXA_RE_PLUGIN_DIR . 'includes/class-nexa-shortcodes.php';
require_once NEXA_RE_PLUGIN_DIR . 'includes/class-nexa-api-client.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-nexa-admin.php';
Nexa_RE_Admin::init();


class Nexa_Real_Estate_Plugin {

    public function __construct() {
        // Admin settings
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ 'Nexa_RE_Settings', 'register_settings' ] );

        // Shortcodes
        add_action( 'init', [ 'Nexa_RE_Shortcodes', 'register_shortcodes' ] );

        // Front-end routing for single property pages
        add_action( 'init', [ $this, 'add_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
        add_filter( 'template_include', [ $this, 'maybe_render_property_template' ] );
    }

    public function register_settings_page() {
        add_options_page(
            'Nexa Real Estate',
            'Nexa Real Estate',
            'manage_options',
            'nexa-real-estate',
            [ 'Nexa_RE_Settings', 'render_settings_page' ]
        );
    }

    public function add_rewrite_rules() {
        add_rewrite_rule( '^properties/([0-9]+)/?$', 'index.php?nexa_property_id=$matches[1]', 'top' );
    }

    public function register_query_vars( $vars ) {
        $vars[] = 'nexa_property_id';
        return $vars;
    }

    public function maybe_render_property_template( $template ) {
        $property_id = (int) get_query_var( 'nexa_property_id' );

        if ( ! $property_id && isset( $_GET['nexa_property_id'] ) ) {
            $property_id = (int) $_GET['nexa_property_id'];
        }

        if ( ! $property_id ) {
            return $template;
        }

        $api    = new Nexa_RE_Api_Client();
        $result = $api->get_property( $property_id );

        if ( ! $result['ok'] || empty( $result['data'] ) ) {
            status_header( 404 );
            set_query_var( 'nexa_property_error', 'Property not found.' );
            return NEXA_RE_PLUGIN_DIR . 'views/property-single.php';
        }

        set_query_var( 'nexa_property', $result['data'] );

        // Get single property layout from API config
        $single_property_layout = $api->get_single_property_layout();
        set_query_var( 'nexa_single_property_layout', $single_property_layout );

        return NEXA_RE_PLUGIN_DIR . 'views/property-single.php';
    }

    public static function activate() {
        $plugin = new self();
        $plugin->add_rewrite_rules();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
}

new Nexa_Real_Estate_Plugin();
register_activation_hook( __FILE__, [ 'Nexa_Real_Estate_Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Nexa_Real_Estate_Plugin', 'deactivate' ] );
