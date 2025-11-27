<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Shortcodes {

    public static function register_shortcodes() {
        add_shortcode( 'nexa_properties', [ __CLASS__, 'render_properties' ] );
    }

    public static function render_properties( $atts = [] ) {
        $atts = shortcode_atts( [
            'city'          => '',
            'category'      => '',
            'property_type' => '',
            'min_price'     => '',
            'max_price'     => '',
            'per_page'      => '10',
        ], $atts, 'nexa_properties' );

        // Fixed API URL
        $api_url   = rtrim( Nexa_RE_Settings::API_BASE_URL, '/' );
        $api_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );

        if ( empty( $api_token ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p><strong>Nexa Real Estate:</strong> please configure your Agency API Token in Settings → Nexa Real Estate.</p>';
            }
            return '';
        }

        $endpoint = $api_url . '/properties';

        $query_args = array_filter( [
            'city'          => $atts['city'],
            'category'      => $atts['category'],
            'property_type' => $atts['property_type'],
            'min_price'     => $atts['min_price'],
            'max_price'     => $atts['max_price'],
            'per_page'      => $atts['per_page'],
        ] );

        if ( ! empty( $query_args ) ) {
            $endpoint = add_query_arg( $query_args, $endpoint );
        }

        $response = wp_remote_get( $endpoint, [
            'headers' => [
                'X-AGENCY-TOKEN' => $api_token,
                'Accept'         => 'application/json',
            ],
            'timeout' => 10,
        ] );

        if ( is_wp_error( $response ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p>Error connecting to Nexa API: ' . esc_html( $response->get_error_message() ) . '</p>';
            }
            return '';
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p>Nexa API returned error: ' . esc_html( $code ) . '</p>';
            }
            return '';
        }

        $data = json_decode( $body, true );

        // If Laravel uses pagination, we expect ["data"]; otherwise use whole array
        $properties = $data['data'] ?? $data;

        if ( empty( $properties ) ) {
            return '<p>No properties found.</p>';
        }

        ob_start();
        ?>
        <div class="nexa-properties">
            <?php foreach ( $properties as $property ) : ?>
                <div class="nexa-property">
                    <h3><?php echo esc_html( $property['title'] ?? '' ); ?></h3>
                    <p>
                        <?php if ( ! empty( $property['city'] ) ) : ?>
                            <strong>City:</strong> <?php echo esc_html( $property['city'] ); ?><br>
                        <?php endif; ?>

                        <?php if ( isset( $property['price'] ) ) : ?>
                            <strong>Price:</strong> <?php echo esc_html( $property['price'] ); ?><br>
                        <?php endif; ?>

                        <?php if ( isset( $property['bedrooms'] ) ) : ?>
                            <strong>Bedrooms:</strong> <?php echo esc_html( $property['bedrooms'] ); ?><br>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
