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

require_once NEXA_RE_PLUGIN_DIR . 'includes/class-nexa-settings.php';
require_once NEXA_RE_PLUGIN_DIR . 'includes/class-nexa-shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-nexa-admin.php';
Nexa_RE_Admin::init();


class Nexa_Real_Estate_Plugin {

    public function __construct() {
        // Admin settings
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ 'Nexa_RE_Settings', 'register_settings' ] );

        // Shortcodes
        add_action( 'init', [ 'Nexa_RE_Shortcodes', 'register_shortcodes' ] );
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
}

new Nexa_Real_Estate_Plugin();
