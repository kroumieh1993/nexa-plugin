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
 * Add capabilities on plugin activation
 */
register_activation_hook( __FILE__, 'nexa_re_add_capabilities' );

function nexa_re_add_capabilities() {
    // Give WP administrators the ability to access Nexa dashboard
    $role = get_role( 'administrator' );
    if ( $role && ! $role->has_cap( 'manage_nexa_properties' ) ) {
        $role->add_cap( 'manage_nexa_properties' );
    }
}

/**
 * Enqueue frontend assets for the Nexa agency dashboard.
 */
function nexa_re_enqueue_front_assets() {
    if ( ! is_singular() ) {
        return;
    }

    global $post;
    if ( ! $post instanceof WP_Post ) {
        return;
    }

    // Only load CSS if the page content has the dashboard shortcode
    if ( has_shortcode( $post->post_content, 'nexa_agency_dashboard' ) ) {
        wp_enqueue_style(
            'nexa-re-dashboard',
            NEXA_RE_PLUGIN_URL . 'assets/css/nexa-dashboard.css',
            [],
            NEXA_RE_VERSION
        );
    }
}
add_action( 'wp_enqueue_scripts', 'nexa_re_enqueue_front_assets' );


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
