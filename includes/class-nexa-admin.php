<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Admin {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
    }

    public static function register_menu() {
        add_menu_page(
            'Nexa Properties',
            'Nexa Properties',
            'manage_options',
            'nexa-properties',
            [ __CLASS__, 'render_dashboard_page' ],
            'dashicons-admin-multisite',
            26
        );
    }

    public static function render_dashboard_page() {
        $api_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );
        $is_configured = ! empty( $api_token );

        // Fetch property stats if configured
        $total_properties = 0;
        if ( $is_configured ) {
            $api = new Nexa_RE_Api_Client();
            $result = $api->list_properties();
            if ( $result['ok'] ) {
                $data = $result['data'];
                $properties = $data['data'] ?? $data;
                if ( is_array( $properties ) ) {
                    $total_properties = count( $properties );
                }
            }
        }
        ?>
        <div class="wrap nexa-admin-wrap">
            <h1>Nexa Properties</h1>

            <?php if ( ! $is_configured ) : ?>
                <div class="notice notice-warning">
                    <p>
                        <strong>Setup Required:</strong> Please configure your API token in 
                        <a href="<?php echo esc_url( admin_url( 'options-general.php?page=nexa-real-estate' ) ); ?>">Settings → Nexa Real Estate</a>.
                    </p>
                </div>
            <?php else : ?>
                <div class="nexa-dashboard-cards" style="display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap;">
                    <div class="nexa-dashboard-card" style="background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); min-width: 200px;">
                        <h3 style="margin: 0 0 5px 0; font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total Properties</h3>
                        <p style="margin: 0; font-size: 36px; font-weight: 700; color: #1f2937;"><?php echo esc_html( $total_properties ); ?></p>
                    </div>
                </div>

                <div class="nexa-saas-link" style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 4px;">
                    <h3 style="margin: 0 0 10px 0; color: #0369a1;">Manage Your Agency</h3>
                    <p style="margin: 0 0 15px 0; color: #0c4a6e;">
                        Property management, customization options, and agency settings are now managed through the Nexa SaaS dashboard.
                    </p>
                    <a href="https://saas.nexapropertysuite.com" target="_blank" rel="noopener noreferrer" 
                       style="display: inline-flex; align-items: center; gap: 8px; background: #0ea5e9; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500;">
                        Go to SaaS Dashboard
                        <span style="font-size: 18px;">→</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

}
