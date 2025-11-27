<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Settings {

    const OPTION_API_URL   = 'nexa_re_api_url';
    const OPTION_API_TOKEN = 'nexa_re_api_token';

    public static function register_settings() {
        register_setting( 'nexa_re_settings', self::OPTION_API_URL, [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ] );

        register_setting( 'nexa_re_settings', self::OPTION_API_TOKEN, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ] );

        add_settings_section(
            'nexa_re_main',
            'API Settings',
            '__return_false',
            'nexa_re_settings'
        );

        add_settings_field(
            self::OPTION_API_URL,
            'API Base URL',
            [ __CLASS__, 'field_api_url' ],
            'nexa_re_settings',
            'nexa_re_main'
        );

        add_settings_field(
            self::OPTION_API_TOKEN,
            'Agency API Token',
            [ __CLASS__, 'field_api_token' ],
            'nexa_re_settings',
            'nexa_re_main'
        );
    }

    public static function field_api_url() {
        $value = esc_attr( get_option( self::OPTION_API_URL, '' ) );
        echo '<input type="text" name="' . esc_attr( self::OPTION_API_URL ) . '" value="' . $value . '" class="regular-text" placeholder="https://saas.nexapropertysuite.com/api" />';
    }

    public static function field_api_token() {
        $value = esc_attr( get_option( self::OPTION_API_TOKEN, '' ) );
        echo '<input type="text" name="' . esc_attr( self::OPTION_API_TOKEN ) . '" value="' . $value . '" class="regular-text" />';
        echo '<p class="description">Paste the API token provided by Nexa SaaS for this agency.</p>';
    }

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Nexa Real Estate</h1>
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
}
