<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Shortcodes {

    public static function register_shortcodes() {
        add_shortcode( 'nexa_properties', [ __CLASS__, 'render_properties' ] );
        add_shortcode( 'nexa_agency_dashboard', [ __CLASS__, 'render_agency_dashboard' ] );
    }

    protected static function get_property_url( array $property ) {
        $id = isset( $property['id'] ) ? intval( $property['id'] ) : 0;
        if ( ! $id ) {
            return home_url();
        }

        return home_url( '/properties/' . $id . '/' );
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

        // Fixed API URL from settings class
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

        if ( empty( $properties ) || ! is_array( $properties ) ) {
            return '<p>No properties found.</p>';
        }

        ob_start();
        ?>
        <div class="nexa-properties-wrapper">
            <div class="nexa-properties-grid">
                <?php foreach ( $properties as $property ) :
                    $first_image = '';
                    if ( ! empty( $property['images'] ) && is_array( $property['images'] ) ) {
                        $first_image = $property['images'][0]['url'] ?? '';
                    }

                    $title       = $property['title'] ?? '';
                    $city        = $property['city'] ?? '';
                    $category    = $property['category'] ?? '';
                    $price       = isset( $property['price'] ) ? $property['price'] : null;
                    $bedrooms    = $property['bedrooms'] ?? null;
                    $bathrooms   = $property['bathrooms'] ?? null;
                    $detail_url  = self::get_property_url( $property );
                    ?>
                    <a class="nexa-property-card" href="<?php echo esc_url( $detail_url ); ?>">
                        <div class="nexa-property-image">
                            <?php if ( $first_image ) : ?>
                                <img src="<?php echo esc_url( $first_image ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                            <?php else : ?>
                                <div class="nexa-property-image-placeholder">No image</div>
                            <?php endif; ?>
                            <?php if ( $category ) : ?>
                                <span class="nexa-property-chip"><?php echo esc_html( ucfirst( $category ) ); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="nexa-property-body">
                            <div class="nexa-property-top">
                                <h3 class="nexa-property-title"><?php echo esc_html( $title ); ?></h3>
                                <?php if ( $price ) : ?>
                                    <div class="nexa-property-price">
                                        <?php echo esc_html( number_format_i18n( $price ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="nexa-property-location"><?php echo esc_html( $city ); ?></p>

                            <div class="nexa-property-meta">
                                <?php if ( $bedrooms ) : ?>
                                    <span><?php echo intval( $bedrooms ); ?> Bedrooms</span>
                                <?php endif; ?>
                                <?php if ( $bathrooms ) : ?>
                                    <span><?php echo intval( $bathrooms ); ?> Bathrooms</span>
                                <?php endif; ?>
                            </div>

                            <span class="nexa-property-view-btn">View details</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <style>
            .nexa-properties-wrapper {
                max-width: 1200px;
                margin: 0 auto;
            }
            .nexa-properties-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
                gap: 24px;
            }
            .nexa-property-card {
                background: #ffffff;
                border-radius: 18px;
                overflow: hidden;
                box-shadow: 0 10px 25px rgba(15,23,42,0.08);
                display: flex;
                flex-direction: column;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
                text-decoration: none;
                color: inherit;
                position: relative;
            }
            .nexa-property-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 16px 40px rgba(15,23,42,0.14);
            }
            .nexa-property-image {
                position: relative;
                padding-top: 65%;
                overflow: hidden;
                background: linear-gradient(145deg, #e2e8f0, #f8fafc);
            }
            .nexa-property-image img,
            .nexa-property-image-placeholder {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .nexa-property-image-placeholder {
                display: flex;
                align-items: center;
                justify-content: center;
                color: #94a3b8;
                font-size: 12px;
            }
            .nexa-property-chip {
                position: absolute;
                top: 12px;
                left: 12px;
                background: rgba(255,255,255,0.9);
                color: #0f172a;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 600;
                box-shadow: 0 8px 20px rgba(15,23,42,0.12);
                text-transform: uppercase;
                letter-spacing: 0.02em;
            }
            .nexa-property-body {
                padding: 16px 18px 18px;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .nexa-property-top {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                align-items: flex-start;
            }
            .nexa-property-title {
                font-size: 16px;
                font-weight: 700;
                color: #0f172a;
                margin: 0;
                line-height: 1.35;
            }
            .nexa-property-location {
                font-size: 12px;
                color: #64748b;
                margin: 0;
            }
            .nexa-property-meta {
                display: flex;
                gap: 12px;
                font-size: 11.5px;
                color: #475569;
                flex-wrap: wrap;
            }
            .nexa-property-meta span::before {
                content: "•";
                margin-right: 6px;
                color: #cbd5f5;
            }
            .nexa-property-price {
                font-weight: 700;
                color: #16a34a;
                font-size: 16px;
                white-space: nowrap;
            }
            .nexa-property-view-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 12px;
                font-weight: 600;
                color: #4f46e5;
                margin-top: 4px;
            }
            .nexa-property-view-btn::after {
                content: '→';
                font-size: 14px;
                transition: transform 0.15s ease;
            }
            .nexa-property-card:hover .nexa-property-view-btn::after {
                transform: translateX(3px);
            }

            @media (max-width: 640px) {
                .nexa-properties-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
        return ob_get_clean();
    }

    public static function render_agency_dashboard( $atts = [] ) {

        // 1. Permissions
        if ( ! is_user_logged_in() ) {
            return '<p>You must be logged in to access your agency dashboard. <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Log in</a></p>';
        }

        // Allow either our custom cap OR standard administrators
        if ( ! current_user_can( 'manage_nexa_properties' ) && ! current_user_can( 'manage_options' ) ) {
            return '<p>You do not have permission to access this dashboard.</p>';
        }

        // 2. API setup
        $api_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );
        if ( ! $api_token ) {
            return '<p><strong>Nexa:</strong> Agency API token is not configured. Contact your site administrator.</p>';
        }

        // Centralized API client
        $api      = new Nexa_RE_Api_Client();
        $messages = [];



        // 3. Handle DELETE action (via GET)
        if ( isset( $_GET['nexa_action'], $_GET['property_id'] ) && $_GET['nexa_action'] === 'delete_property' ) {
            $property_id = (int) $_GET['property_id'];

            if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'nexa_delete_property_' . $property_id ) ) {
                $messages[] = [ 'type' => 'error', 'text' => 'Security check failed.' ];
            } else {
                $result = $api->delete_property( $property_id );

                if ( ! $result['ok'] ) {
                    $text = $result['error'] ? $result['error'] : 'API error (' . $result['code'] . ') deleting property.';
                    $messages[] = [ 'type' => 'error', 'text' => 'Error deleting property: ' . $text ];
                } else {
                    $messages[] = [ 'type' => 'success', 'text' => 'Property deleted.' ];
                }
            }
        }



        // 4. (Optional) Handle save property (create/update) – hook up later if needed
        // Keeping the structure here so design is ready for when you add the form.
        if ( isset( $_POST['nexa_action'] ) && $_POST['nexa_action'] === 'save_property' ) {
            if ( ! wp_verify_nonce( $_POST['nexa_property_nonce'] ?? '', 'nexa_save_property_front' ) ) {
                $messages[] = [ 'type' => 'error', 'text' => 'Security check failed.' ];
            } else {
                $property_id = isset( $_POST['property_id'] ) ? (int) $_POST['property_id'] : 0;
                $is_edit     = $property_id > 0;

                $payload = [
                    'title'         => sanitize_text_field( $_POST['title'] ?? '' ),
                    'description'   => wp_kses_post( $_POST['description'] ?? '' ),
                    'category'      => sanitize_text_field( $_POST['category'] ?? '' ),
                    'city'          => sanitize_text_field( $_POST['city'] ?? '' ),
                    'property_type' => sanitize_text_field( $_POST['property_type'] ?? '' ),
                    'area'          => $_POST['area'] !== '' ? (int) $_POST['area'] : null,
                    'address'       => sanitize_text_field( $_POST['address'] ?? '' ),
                    'price'         => $_POST['price'] !== '' ? (int) $_POST['price'] : null,
                    'bedrooms'      => $_POST['bedrooms'] !== '' ? (int) $_POST['bedrooms'] : null,
                    'bathrooms'     => $_POST['bathrooms'] !== '' ? (int) $_POST['bathrooms'] : null,
                ];

                $images = [];
                if ( ! empty( $_POST['images'] ) && is_array( $_POST['images'] ) ) {
                    foreach ( $_POST['images'] as $url ) {
                        $url = esc_url_raw( $url );
                        if ( $url ) {
                            $images[] = $url;
                        }
                    }
                }
                $payload['images'] = $images;

                // Handle floor plans
                $floor_plans = [];
                if ( ! empty( $_POST['floor_plans'] ) && is_array( $_POST['floor_plans'] ) ) {
                    foreach ( $_POST['floor_plans'] as $plan ) {
                        $file_url = isset( $plan['file_url'] ) ? esc_url_raw( $plan['file_url'] ) : '';
                        if ( $file_url ) {
                            $floor_plans[] = [
                                'file_url' => $file_url,
                                'label'    => isset( $plan['label'] ) ? sanitize_text_field( $plan['label'] ) : '',
                                'order'    => isset( $plan['order'] ) ? (int) $plan['order'] : 0,
                            ];
                        }
                    }
                }
                $payload['floor_plans'] = $floor_plans;

                // Use API client
                if ( $is_edit ) {
                    $result = $api->update_property( $property_id, $payload );
                } else {
                    $result = $api->create_property( $payload );
                }

                if ( ! $result['ok'] ) {
                    $text = $result['error'] ? $result['error'] : 'API error (' . $result['code'] . ') saving property.';
                    $messages[] = [ 'type' => 'error', 'text' => $text ];
                } else {
                    $messages[] = [ 'type' => 'success', 'text' => 'Property saved successfully.' ];
                }

            }
        }

        // 5. Fetch properties for list & stats
        $properties = [];
        $list_result = $api->list_properties();

        if ( $list_result['ok'] ) {
            $data       = $list_result['data'];
            $properties = $data['data'] ?? $data;
            if ( ! is_array( $properties ) ) {
                $properties = [];
            }
        } else {
            $messages[] = [ 'type' => 'error', 'text' => 'Error loading properties: ' . ( $list_result['error'] ?: 'API ' . $list_result['code'] ) ];
        }


        // Stats
        $total_properties       = count( $properties );
        $properties_this_week   = 0;
        $now                    = current_time( 'timestamp' );
        $week_start             = strtotime( 'monday this week', $now );

        foreach ( $properties as $p ) {
            if ( ! empty( $p['created_at'] ) && strtotime( $p['created_at'] ) >= $week_start ) {
                $properties_this_week++;
            }
        }

        $current_user = wp_get_current_user();


        // Needed for image picker (gallery) in frontend form
        if ( function_exists( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        }

        ob_start();
        // variables are already in scope: $messages, $properties, $total_properties,
        // $properties_this_week, $current_user
        include NEXA_RE_PLUGIN_DIR . 'views/dashboard-shell.php';
        return ob_get_clean();
    }


}
