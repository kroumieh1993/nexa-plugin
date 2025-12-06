<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Api_Client {

    protected $base_url;
    protected $token;

    /**
     * Cache TTL for shortcode configs (5 minutes).
     */
    const CONFIG_CACHE_TTL = 300;

    public function __construct() {
        $this->base_url = rtrim( Nexa_RE_Settings::API_BASE_URL, '/' );
        $this->token    = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );
    }

    protected function request( $method, $path, $body = null, $extra_args = [] ) {
        $url = $this->base_url . $path;

        $args = array_merge(
            [
                'method'  => $method,
                'headers' => [
                    'X-AGENCY-TOKEN' => $this->token,
                    'Accept'         => 'application/json',
                ],
                'timeout' => 15,
            ],
            $extra_args
        );

        if ( $body !== null ) {
            $args['headers']['Content-Type'] = 'application/json';
            $args['body'] = wp_json_encode( $body );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return [
                'ok'    => false,
                'error' => $response->get_error_message(),
                'code'  => 0,
                'data'  => null,
            ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        // Extract error message, including validation errors for 422 responses
        $error_message = '';
        if ( $code >= 400 && is_array( $data ) ) {
            $error_message = $data['message'] ?? '';
            // For Laravel validation errors (422), append field-specific errors
            if ( ! empty( $data['errors'] ) && is_array( $data['errors'] ) ) {
                $validation_errors = [];
                foreach ( $data['errors'] as $field => $messages ) {
                    if ( is_array( $messages ) ) {
                        $validation_errors[] = implode( ', ', $messages );
                    }
                }
                if ( ! empty( $validation_errors ) ) {
                    $error_message .= ' ' . implode( ' ', $validation_errors );
                }
            }
        }

        return [
            'ok'    => $code >= 200 && $code < 300,
            'error' => trim( $error_message ),
            'code'  => $code,
            'data'  => $data,
        ];
    }

    /* -------- Properties endpoints (read-only) -------- */

    public function list_properties( array $filters = [] ) {
        $path = '/properties';
        
        // Build query string from filters, only including non-empty values
        // Note: Filters should be pre-validated before calling this method
        $query_params = [];
        
        if ( ! empty( $filters['city'] ) ) {
            $query_params['city'] = $filters['city'];
        }
        if ( ! empty( $filters['category'] ) ) {
            $query_params['category'] = $filters['category'];
        }
        if ( ! empty( $filters['type'] ) ) {
            $query_params['type'] = $filters['type'];
        }
        if ( isset( $filters['min_price'] ) && $filters['min_price'] !== '' ) {
            $query_params['min_price'] = (int) $filters['min_price'];
        }
        if ( isset( $filters['max_price'] ) && $filters['max_price'] !== '' ) {
            $query_params['max_price'] = (int) $filters['max_price'];
        }
        if ( isset( $filters['bedrooms'] ) && $filters['bedrooms'] !== '' ) {
            $query_params['bedrooms'] = (int) $filters['bedrooms'];
        }
        if ( isset( $filters['bathrooms'] ) && $filters['bathrooms'] !== '' ) {
            $query_params['bathrooms'] = (int) $filters['bathrooms'];
        }
        
        if ( ! empty( $query_params ) ) {
            $path .= '?' . http_build_query( $query_params );
        }
        
        return $this->request( 'GET', $path );
    }

    public function get_property( $id ) {
        return $this->request( 'GET', '/properties/' . (int) $id );
    }

    /* -------- Agency Parameters endpoints (read-only) -------- */

    /**
     * List all agency parameters (cities and property types) for filter dropdowns.
     *
     * @param string|null $type Optional filter by type ('city' or 'property_type').
     * @return array API response.
     */
    public function list_agency_parameters( $type = null ) {
        $path = '/agency-parameters';
        if ( $type ) {
            $path .= '?type=' . rawurlencode( $type );
        }
        return $this->request( 'GET', $path );
    }

    /* -------- Shortcode Configuration -------- */

    /**
     * Fetch shortcode configuration from the SaaS API with caching.
     *
     * @param string $type The shortcode type (e.g., 'nexa_properties', 'nexa_property_search').
     * @return array|null The configuration array or null if unavailable.
     */
    public function get_shortcode_config( $type ) {
        $cache_key = 'nexa_shortcode_config_' . sanitize_key( $type );

        // Check transient cache
        $cached = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        // Fetch from API
        $result = $this->request( 'GET', '/shortcode-configs?type=' . rawurlencode( $type ) );

        if ( $result['ok'] && ! empty( $result['data'] ) ) {
            $data = $result['data'];
            
            // The API may return config in a nested 'config' key or directly
            $config = isset( $data['config'] ) && is_array( $data['config'] ) ? $data['config'] : $data;

            // Cache the config for 5 minutes
            set_transient( $cache_key, $config, self::CONFIG_CACHE_TTL );

            return $config;
        }

        // Return null if API is unreachable or returns an error
        // Shortcodes will use sensible defaults
        return null;
    }

    /**
     * Fetch all shortcode configurations from the SaaS API.
     *
     * @return array|null The configurations array or null if unavailable.
     */
    public function get_all_shortcode_configs() {
        $cache_key = 'nexa_all_shortcode_configs';

        // Check transient cache
        $cached = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        // Fetch from API
        $result = $this->request( 'GET', '/shortcode-configs' );

        if ( $result['ok'] && ! empty( $result['data'] ) ) {
            $configs = $result['data'];

            // Cache the configs for 5 minutes
            set_transient( $cache_key, $configs, self::CONFIG_CACHE_TTL );

            return $configs;
        }

        return null;
    }

    /**
     * Update shortcode configuration for a specific shortcode.
     *
     * @param string $shortcode The shortcode name (e.g., 'nexa_properties', 'nexa_single_property').
     * @param array  $config    The configuration to update.
     * @return array API response.
     */
    public function update_shortcode_config( $shortcode, array $config ) {
        // Clear relevant caches
        delete_transient( 'nexa_all_shortcode_configs' );
        delete_transient( 'nexa_shortcode_config_' . sanitize_key( $shortcode ) );

        return $this->request( 'PUT', '/shortcode-configs/' . rawurlencode( $shortcode ), [ 'config' => $config ] );
    }

    /* -------- Layout Configuration Helpers -------- */

    /**
     * Get the property card layout setting.
     *
     * @return string The layout name (default, modern, elegant, compact, minimal, bold).
     */
    public function get_property_card_layout() {
        $config = $this->get_shortcode_config( 'nexa_properties' );
        $layout = $config['property_card_layout'] ?? 'default';

        // Validate layout
        $valid_layouts = [ 'default', 'modern', 'elegant', 'compact', 'minimal', 'bold' ];
        return in_array( $layout, $valid_layouts, true ) ? $layout : 'default';
    }

    /**
     * Get the single property page layout setting.
     *
     * @return string The layout name (default, modern, elegant, compact, minimal, bold).
     */
    public function get_single_property_layout() {
        $config = $this->get_shortcode_config( 'nexa_single_property' );
        $layout = $config['single_property_layout'] ?? 'default';

        // Validate layout
        $valid_layouts = [ 'default', 'modern', 'elegant', 'compact', 'minimal', 'bold' ];
        return in_array( $layout, $valid_layouts, true ) ? $layout : 'default';
    }
}
