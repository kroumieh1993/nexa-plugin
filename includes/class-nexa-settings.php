<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Settings {

    // Fixed API base URL for all agencies
    const API_BASE_URL           = 'https://saas.nexapropertysuite.com/api';
    const OPTION_API_TOKEN       = 'nexa_re_api_token';
    const OPTION_MEDIA_TOKEN     = 'nexa_re_media_token';
    const OPTION_MAP_PROVIDER    = 'nexa_re_map_provider';
    const OPTION_GOOGLE_MAPS_KEY = 'nexa_re_google_maps_key';

    public static function register_settings() {

        register_setting( 'nexa_re_settings', self::OPTION_API_TOKEN, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ] );

        register_setting( 'nexa_re_settings', self::OPTION_MEDIA_TOKEN, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ] );

        register_setting( 'nexa_re_settings', self::OPTION_MAP_PROVIDER, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'leaflet',
        ] );

        register_setting( 'nexa_re_settings', self::OPTION_GOOGLE_MAPS_KEY, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ] );

        add_settings_section(
            'nexa_re_main',
            'Nexa API Settings',
            '__return_false',
            'nexa_re_settings'
        );

        add_settings_field(
            self::OPTION_API_TOKEN,
            'Agency API Token',
            [ __CLASS__, 'field_api_token' ],
            'nexa_re_settings',
            'nexa_re_main'
        );

        add_settings_field(
            self::OPTION_MEDIA_TOKEN,
            'Media Upload Token',
            [ __CLASS__, 'field_media_token' ],
            'nexa_re_settings',
            'nexa_re_main'
        );

        add_settings_section(
            'nexa_re_map',
            'Map Settings',
            [ __CLASS__, 'section_map_description' ],
            'nexa_re_settings'
        );

        add_settings_field(
            self::OPTION_MAP_PROVIDER,
            'Map Provider',
            [ __CLASS__, 'field_map_provider' ],
            'nexa_re_settings',
            'nexa_re_map'
        );

        add_settings_field(
            self::OPTION_GOOGLE_MAPS_KEY,
            'Google Maps API Key',
            [ __CLASS__, 'field_google_maps_key' ],
            'nexa_re_settings',
            'nexa_re_map'
        );
    }

    public static function section_map_description() {
        echo '<p>Configure the map provider for displaying property locations. Leaflet (OpenStreetMap) is free and requires no API key. Google Maps requires an API key.</p>';
    }

    public static function field_map_provider() {
        $value = get_option( self::OPTION_MAP_PROVIDER, 'leaflet' );
        ?>
        <select name="<?php echo esc_attr( self::OPTION_MAP_PROVIDER ); ?>" id="<?php echo esc_attr( self::OPTION_MAP_PROVIDER ); ?>">
            <option value="leaflet" <?php selected( $value, 'leaflet' ); ?>>Leaflet (OpenStreetMap) - Free, no API key required</option>
            <option value="google" <?php selected( $value, 'google' ); ?>>Google Maps - Requires API key</option>
        </select>
        <p class="description">Select the map provider to use for property location display.</p>
        <?php
    }

    public static function field_google_maps_key() {
        $value = esc_attr( get_option( self::OPTION_GOOGLE_MAPS_KEY, '' ) );
        echo '<input type="text" name="' . esc_attr( self::OPTION_GOOGLE_MAPS_KEY ) . '" value="' . $value . '" class="regular-text" />';
        echo '<p class="description">Enter your Google Maps API key. Only required if you select Google Maps as the map provider.</p>';
        echo '<p class="description">Get an API key at <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener noreferrer">Google Cloud Console</a>.</p>';
    }

    public static function field_api_token() {
        $value = esc_attr( get_option( self::OPTION_API_TOKEN, '' ) );
        echo '<input type="text" name="' . esc_attr( self::OPTION_API_TOKEN ) . '" value="' . $value . '" class="regular-text" />';
        echo '<p class="description">Paste the API token provided by Nexa Property Suite for this agency.</p>';
        echo '<p class="description"><strong>API URL:</strong> ' . esc_html( self::API_BASE_URL ) . '</p>';
    }

    public static function field_media_token() {
        $value = get_option( self::OPTION_MEDIA_TOKEN, '' );
        ?>
        <div class="nexa-media-token-wrapper">
            <input type="text" name="<?php echo esc_attr( self::OPTION_MEDIA_TOKEN ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" id="nexa_media_token_field" />
            <button type="button" class="button" id="nexa_generate_token_btn">Generate Token</button>
        </div>
        <p class="description">This token is used for the media upload API endpoint. Use the <strong>X-WP-MEDIA-TOKEN</strong> header when uploading media.</p>
        <p class="description"><strong>Endpoint:</strong> POST <?php echo esc_html( home_url( '/wp-json/nexa-plugin/v1/upload-media' ) ); ?></p>
        <script>
        (function() {
            var generateBtn = document.getElementById('nexa_generate_token_btn');
            var tokenField = document.getElementById('nexa_media_token_field');
            if (generateBtn && tokenField) {
                generateBtn.addEventListener('click', function() {
                    var token = '';
                    if (window.crypto && window.crypto.getRandomValues) {
                        var array = new Uint8Array(32);
                        window.crypto.getRandomValues(array);
                        token = Array.from(array, function(byte) {
                            return ('0' + byte.toString(16)).slice(-2);
                        }).join('');
                    } else {
                        // Fallback for older browsers without Web Crypto API
                        var chars = 'abcdef0123456789';
                        for (var i = 0; i < 64; i++) {
                            token += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                    }
                    tokenField.value = token;
                });
            }
        })();
        </script>
        <style>
            .nexa-media-token-wrapper {
                display: flex;
                gap: 10px;
                align-items: center;
            }
        </style>
        <?php
    }

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Nexa Real Estate</h1>

            <div class="nexa-saas-notice" style="margin: 20px 0; padding: 15px 20px; background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 4px;">
                <p style="margin: 0; font-size: 14px; color: #0c4a6e;">
                    <strong>Manage your agency settings at:</strong>
                    <a href="https://saas.nexapropertysuite.com" target="_blank" rel="noopener noreferrer" style="color: #0369a1; text-decoration: underline;">
                        saas.nexapropertysuite.com
                    </a>
                </p>
                <p style="margin: 8px 0 0 0; font-size: 13px; color: #475569;">
                    Property management, custom parameters (cities, property types), and shortcode configurations are managed through the SaaS dashboard.
                </p>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'nexa_re_settings' );
                do_settings_sections( 'nexa_re_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get the current map provider setting.
     *
     * @return string Either 'leaflet' or 'google'.
     */
    public static function get_map_provider() {
        return get_option( self::OPTION_MAP_PROVIDER, 'leaflet' );
    }

    /**
     * Get the Google Maps API key.
     *
     * @return string The Google Maps API key or empty string.
     */
    public static function get_google_maps_key() {
        return get_option( self::OPTION_GOOGLE_MAPS_KEY, '' );
    }

    /**
     * Check if map features are properly configured.
     *
     * @return bool True if map can be used.
     */
    public static function is_map_configured() {
        $provider = self::get_map_provider();
        if ( 'leaflet' === $provider ) {
            return true; // Leaflet doesn't require API key
        }
        if ( 'google' === $provider && ! empty( self::get_google_maps_key() ) ) {
            return true;
        }
        return false;
    }
}
