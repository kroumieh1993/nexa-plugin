<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Shortcodes {

    /**
     * Valid property card layout options.
     */
    const VALID_CARD_LAYOUTS = [ 'default', 'modern', 'elegant', 'compact', 'minimal', 'bold' ];

    public static function register_shortcodes() {
        add_shortcode( 'nexa_properties', [ __CLASS__, 'render_properties' ] );
        add_shortcode( 'nexa_property_search', [ __CLASS__, 'render_property_search' ] );
    }

    protected static function get_property_url( array $property ) {
        $id = isset( $property['id'] ) ? intval( $property['id'] ) : 0;
        if ( ! $id ) {
            return home_url();
        }

        return home_url( '/properties/' . $id . '/' );
    }

    /**
     * Render a property card using the selected layout template.
     *
     * @param array  $property       Property data array.
     * @param string $layout         Layout template name.
     * @param bool   $show_price     Whether to show price.
     * @param bool   $show_bedrooms  Whether to show bedrooms.
     * @param bool   $show_bathrooms Whether to show bathrooms.
     * @param bool   $show_city      Whether to show city.
     * @param bool   $show_area      Whether to show area.
     * @param bool   $show_location  Whether to show address.
     * @return string The rendered HTML.
     */
    protected static function render_property_card( $property, $layout, $show_price, $show_bedrooms, $show_bathrooms, $show_city, $show_area, $show_location ) {
        // Validate layout
        if ( ! in_array( $layout, self::VALID_CARD_LAYOUTS, true ) ) {
            $layout = 'default';
        }

        $template_path = NEXA_RE_PLUGIN_DIR . 'views/property-cards/' . $layout . '.php';

        // Fallback to default if template doesn't exist
        if ( ! file_exists( $template_path ) ) {
            $template_path = NEXA_RE_PLUGIN_DIR . 'views/property-cards/default.php';
        }

        // Get property URL
        $detail_url = self::get_property_url( $property );

        // Start output buffering
        ob_start();

        // Make variables available to the template
        include $template_path;

        return ob_get_clean();
    }

    /**
     * Render the property search bar shortcode.
     *
     * This creates a search bar that can be placed on any page (e.g., homepage)
     * to allow users to quickly filter properties and be redirected to the
     * properties listing page with the selected filters applied.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public static function render_property_search( $atts = [] ) {
        // Fetch configuration from API
        $api = new Nexa_RE_Api_Client();
        $config = $api->get_shortcode_config( 'nexa_property_search' );

        // Default attributes merged with API config
        $defaults = [
            'properties_page' => '',
            'show_city'       => 'true',
            'show_category'   => 'true',
            'show_type'       => 'true',
            'show_price'      => 'true',
            'show_bedrooms'   => 'true',
            'show_bathrooms'  => 'true',
            'button_text'     => 'Search Properties',
            'primary_color'   => '#4f46e5',
        ];

        // Apply API config
        if ( $config ) {
            // Support both 'show_city' and 'show_city_filter' naming conventions
            if ( isset( $config['show_city_filter'] ) ) {
                $defaults['show_city'] = $config['show_city_filter'] ? 'true' : 'false';
            } elseif ( isset( $config['show_city'] ) ) {
                $defaults['show_city'] = $config['show_city'] ? 'true' : 'false';
            }
            if ( isset( $config['show_category_filter'] ) ) {
                $defaults['show_category'] = $config['show_category_filter'] ? 'true' : 'false';
            } elseif ( isset( $config['show_category'] ) ) {
                $defaults['show_category'] = $config['show_category'] ? 'true' : 'false';
            }
            if ( isset( $config['show_type_filter'] ) ) {
                $defaults['show_type'] = $config['show_type_filter'] ? 'true' : 'false';
            } elseif ( isset( $config['show_type'] ) ) {
                $defaults['show_type'] = $config['show_type'] ? 'true' : 'false';
            }
            if ( isset( $config['show_price_filter'] ) ) {
                $defaults['show_price'] = $config['show_price_filter'] ? 'true' : 'false';
            } elseif ( isset( $config['show_price'] ) ) {
                $defaults['show_price'] = $config['show_price'] ? 'true' : 'false';
            }
            if ( isset( $config['show_bedrooms_filter'] ) ) {
                $defaults['show_bedrooms'] = $config['show_bedrooms_filter'] ? 'true' : 'false';
            } elseif ( isset( $config['show_bedrooms'] ) ) {
                $defaults['show_bedrooms'] = $config['show_bedrooms'] ? 'true' : 'false';
            }
            if ( isset( $config['show_bathrooms_filter'] ) ) {
                $defaults['show_bathrooms'] = $config['show_bathrooms_filter'] ? 'true' : 'false';
            } elseif ( isset( $config['show_bathrooms'] ) ) {
                $defaults['show_bathrooms'] = $config['show_bathrooms'] ? 'true' : 'false';
            }
            if ( ! empty( $config['button_text'] ) ) {
                $defaults['button_text'] = $config['button_text'];
            }
            if ( ! empty( $config['primary_color'] ) ) {
                $defaults['primary_color'] = $config['primary_color'];
            }
        }

        $atts = shortcode_atts( $defaults, $atts, 'nexa_property_search' );

        // Determine the properties page URL
        $properties_url = $atts['properties_page'];
        if ( empty( $properties_url ) ) {
            // Default to home URL if not specified
            $properties_url = home_url( '/' );
        }

        // Convert show_* attributes to boolean
        $show_city      = filter_var( $atts['show_city'], FILTER_VALIDATE_BOOLEAN );
        $show_category  = filter_var( $atts['show_category'], FILTER_VALIDATE_BOOLEAN );
        $show_type      = filter_var( $atts['show_type'], FILTER_VALIDATE_BOOLEAN );
        $show_price     = filter_var( $atts['show_price'], FILTER_VALIDATE_BOOLEAN );
        $show_bedrooms  = filter_var( $atts['show_bedrooms'], FILTER_VALIDATE_BOOLEAN );
        $show_bathrooms = filter_var( $atts['show_bathrooms'], FILTER_VALIDATE_BOOLEAN );

        // Fetch agency parameters for dropdowns
        $city_options          = [];
        $property_type_options = [];
        $api                   = new Nexa_RE_Api_Client();
        $params_result         = $api->list_agency_parameters();

        if ( $params_result['ok'] && ! empty( $params_result['data']['parameters'] ) ) {
            $parameters = $params_result['data']['parameters'];
            if ( ! empty( $parameters['city'] ) && is_array( $parameters['city'] ) ) {
                $city_options = array_column( $parameters['city'], 'value' );
            }
            if ( ! empty( $parameters['property_type'] ) && is_array( $parameters['property_type'] ) ) {
                $property_type_options = array_column( $parameters['property_type'], 'value' );
            }
        }

        ob_start();
        ?>
        <div class="nexa-property-search-bar">
            <form method="get" action="<?php echo esc_url( $properties_url ); ?>" class="nexa-search-form">
                <div class="nexa-search-fields">
                    <?php if ( $show_city ) : ?>
                    <div class="nexa-search-field">
                        <label for="nexa-search-city">City</label>
                        <?php if ( ! empty( $city_options ) ) : ?>
                        <select id="nexa-search-city" name="nexa_city">
                            <option value="">All Cities</option>
                            <?php foreach ( $city_options as $city_opt ) : ?>
                                <option value="<?php echo esc_attr( $city_opt ); ?>"><?php echo esc_html( $city_opt ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php else : ?>
                        <input type="text" id="nexa-search-city" name="nexa_city" placeholder="Enter city">
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ( $show_category ) : ?>
                    <div class="nexa-search-field">
                        <label for="nexa-search-category">Category</label>
                        <select id="nexa-search-category" name="nexa_category">
                            <option value="">All</option>
                            <option value="rent">Rent</option>
                            <option value="buy">Buy</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ( $show_type ) : ?>
                    <div class="nexa-search-field">
                        <label for="nexa-search-type">Property Type</label>
                        <?php if ( ! empty( $property_type_options ) ) : ?>
                        <select id="nexa-search-type" name="nexa_type">
                            <option value="">All Types</option>
                            <?php foreach ( $property_type_options as $type_opt ) : ?>
                                <option value="<?php echo esc_attr( $type_opt ); ?>"><?php echo esc_html( $type_opt ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php else : ?>
                        <input type="text" id="nexa-search-type" name="nexa_type" placeholder="e.g. Apartment">
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ( $show_price ) : ?>
                    <div class="nexa-search-field nexa-search-field-price">
                        <label for="nexa-search-min-price">Price Range</label>
                        <div class="nexa-price-inputs">
                            <input type="number" id="nexa-search-min-price" name="nexa_min_price" placeholder="Min" min="0">
                            <span class="nexa-price-separator">-</span>
                            <input type="number" id="nexa-search-max-price" name="nexa_max_price" placeholder="Max" min="0">
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( $show_bedrooms ) : ?>
                    <div class="nexa-search-field">
                        <label for="nexa-search-bedrooms">Bedrooms</label>
                        <select id="nexa-search-bedrooms" name="nexa_bedrooms">
                            <option value="">Any</option>
                            <option value="1">1+</option>
                            <option value="2">2+</option>
                            <option value="3">3+</option>
                            <option value="4">4+</option>
                            <option value="5">5+</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ( $show_bathrooms ) : ?>
                    <div class="nexa-search-field">
                        <label for="nexa-search-bathrooms">Bathrooms</label>
                        <select id="nexa-search-bathrooms" name="nexa_bathrooms">
                            <option value="">Any</option>
                            <option value="1">1+</option>
                            <option value="2">2+</option>
                            <option value="3">3+</option>
                            <option value="4">4+</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="nexa-search-action">
                    <button type="submit" class="nexa-search-btn">
                        <span class="nexa-search-btn-icon">🔍</span>
                        <?php echo esc_html( $atts['button_text'] ); ?>
                    </button>
                </div>
            </form>
        </div>

        <style>
            .nexa-property-search-bar {
                background: #ffffff;
                border-radius: 12px;
                padding: 24px;
                box-shadow: 0 4px 20px rgba(15, 23, 42, 0.1);
                max-width: 1200px;
                margin: 0 auto;
            }
            .nexa-search-form {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            .nexa-search-fields {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 16px;
                align-items: end;
            }
            .nexa-search-field {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .nexa-search-field label {
                font-size: 12px;
                font-weight: 600;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }
            .nexa-search-field input,
            .nexa-search-field select {
                padding: 12px 14px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                font-size: 14px;
                color: #1e293b;
                background: #f8fafc;
                transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
                height: 46px;
            }
            .nexa-search-field input:focus,
            .nexa-search-field select:focus {
                outline: none;
                border-color: <?php echo esc_attr( $atts['primary_color'] ); ?>;
                background: #ffffff;
                box-shadow: 0 0 0 3px <?php echo esc_attr( $atts['primary_color'] ); ?>1a;
            }
            .nexa-search-field input::placeholder {
                color: #94a3b8;
            }
            .nexa-search-field-price {
                min-width: 220px;
            }
            .nexa-price-inputs {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .nexa-price-inputs input {
                flex: 1;
                min-width: 0;
            }
            .nexa-price-separator {
                color: #94a3b8;
                font-weight: 500;
            }
            .nexa-search-action {
                display: flex;
                justify-content: center;
            }
            .nexa-search-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 14px 32px;
                background: <?php echo esc_attr( $atts['primary_color'] ); ?>;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                color: #ffffff;
                cursor: pointer;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
                min-width: 200px;
            }
            .nexa-search-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px <?php echo esc_attr( $atts['primary_color'] ); ?>59;
            }
            .nexa-search-btn:active {
                transform: translateY(0);
            }
            .nexa-search-btn-icon {
                font-size: 18px;
            }
            @media (max-width: 768px) {
                .nexa-property-search-bar {
                    padding: 20px 16px;
                }
                .nexa-search-fields {
                    grid-template-columns: 1fr 1fr;
                }
                .nexa-search-field-price {
                    grid-column: span 2;
                }
            }
            @media (max-width: 480px) {
                .nexa-search-fields {
                    grid-template-columns: 1fr;
                }
                .nexa-search-field-price {
                    grid-column: span 1;
                }
                .nexa-search-btn {
                    width: 100%;
                }
            }
        </style>
        <?php
        return ob_get_clean();
    }

    public static function render_properties( $atts = [] ) {
        // Fetch configuration from API
        $api = new Nexa_RE_Api_Client();
        $config = $api->get_shortcode_config( 'nexa_properties' );

        // Default attributes
        $defaults = [
            'city'                 => '',
            'category'             => '',
            'property_type'        => '',
            'min_price'            => '',
            'max_price'            => '',
            'per_page'             => '10',
            'show_filter'          => 'true',
            'show_map'             => 'true',
            'columns'              => '3',
            'layout'               => 'grid',
            'card_style'           => 'default',
            'property_card_layout' => 'default',
            'primary_color'        => '#4f46e5',
            'secondary_color'      => '#16a34a',
            'show_price'           => 'true',
            'show_bedrooms'        => 'true',
            'show_bathrooms'       => 'true',
            'show_city'            => 'true',
            'show_area'            => 'true',
            'show_location'        => 'true',
        ];

        // Apply API config
        if ( $config ) {
            if ( isset( $config['per_page'] ) ) {
                $defaults['per_page'] = (string) intval( $config['per_page'] );
            }
            if ( isset( $config['show_filter'] ) ) {
                $defaults['show_filter'] = $config['show_filter'] ? 'true' : 'false';
            }
            if ( isset( $config['show_map'] ) ) {
                $defaults['show_map'] = $config['show_map'] ? 'true' : 'false';
            }
            if ( isset( $config['columns'] ) ) {
                $defaults['columns'] = (string) intval( $config['columns'] );
            }
            if ( ! empty( $config['layout'] ) ) {
                $defaults['layout'] = in_array( $config['layout'], [ 'grid', 'list' ], true ) ? $config['layout'] : 'grid';
            }
            if ( ! empty( $config['card_style'] ) ) {
                $defaults['card_style'] = $config['card_style'];
            }
            if ( ! empty( $config['primary_color'] ) ) {
                $defaults['primary_color'] = $config['primary_color'];
            }
            if ( ! empty( $config['secondary_color'] ) ) {
                $defaults['secondary_color'] = $config['secondary_color'];
            }
            if ( isset( $config['show_price'] ) ) {
                $defaults['show_price'] = $config['show_price'] ? 'true' : 'false';
            }
            if ( isset( $config['show_bedrooms'] ) ) {
                $defaults['show_bedrooms'] = $config['show_bedrooms'] ? 'true' : 'false';
            }
            if ( isset( $config['show_bathrooms'] ) ) {
                $defaults['show_bathrooms'] = $config['show_bathrooms'] ? 'true' : 'false';
            }
            if ( isset( $config['show_city'] ) ) {
                $defaults['show_city'] = $config['show_city'] ? 'true' : 'false';
            }
            if ( isset( $config['show_area'] ) ) {
                $defaults['show_area'] = $config['show_area'] ? 'true' : 'false';
            }
            if ( isset( $config['show_location'] ) ) {
                $defaults['show_location'] = $config['show_location'] ? 'true' : 'false';
            }
            if ( ! empty( $config['property_card_layout'] ) ) {
                if ( in_array( $config['property_card_layout'], self::VALID_CARD_LAYOUTS, true ) ) {
                    $defaults['property_card_layout'] = $config['property_card_layout'];
                }
            }
        }

        $atts = shortcode_atts( $defaults, $atts, 'nexa_properties' );

        // Fixed API URL from settings class
        $api_url   = rtrim( Nexa_RE_Settings::API_BASE_URL, '/' );
        $api_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );

        if ( empty( $api_token ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p><strong>Nexa Real Estate:</strong> please configure your Agency API Token in Settings → Nexa Real Estate.</p>';
            }
            return '';
        }

        // Get filter values from URL parameters (user submitted filters) or shortcode attributes
        $filter_city     = isset( $_GET['nexa_city'] ) ? sanitize_text_field( $_GET['nexa_city'] ) : $atts['city'];
        $filter_category = isset( $_GET['nexa_category'] ) ? sanitize_text_field( $_GET['nexa_category'] ) : $atts['category'];
        $filter_type     = isset( $_GET['nexa_type'] ) ? sanitize_text_field( $_GET['nexa_type'] ) : $atts['property_type'];
        
        // Numeric filters: validate and keep as strings for form value comparison
        $filter_min = '';
        if ( isset( $_GET['nexa_min_price'] ) && $_GET['nexa_min_price'] !== '' ) {
            $filter_min = (string) absint( $_GET['nexa_min_price'] );
        } elseif ( $atts['min_price'] !== '' ) {
            $filter_min = (string) absint( $atts['min_price'] );
        }
        
        $filter_max = '';
        if ( isset( $_GET['nexa_max_price'] ) && $_GET['nexa_max_price'] !== '' ) {
            $filter_max = (string) absint( $_GET['nexa_max_price'] );
        } elseif ( $atts['max_price'] !== '' ) {
            $filter_max = (string) absint( $atts['max_price'] );
        }
        
        $filter_bedrooms = '';
        if ( isset( $_GET['nexa_bedrooms'] ) && $_GET['nexa_bedrooms'] !== '' ) {
            $filter_bedrooms = (string) absint( $_GET['nexa_bedrooms'] );
        }
        
        $filter_bathrooms = '';
        if ( isset( $_GET['nexa_bathrooms'] ) && $_GET['nexa_bathrooms'] !== '' ) {
            $filter_bathrooms = (string) absint( $_GET['nexa_bathrooms'] );
        }

        // Build filters array for API client (keep only non-empty strings)
        $filters = array_filter( [
            'city'      => $filter_city,
            'category'  => $filter_category,
            'type'      => $filter_type,
            'min_price' => $filter_min,
            'max_price' => $filter_max,
            'bedrooms'  => $filter_bedrooms,
            'bathrooms' => $filter_bathrooms,
        ], function( $value ) {
            return $value !== '';
        } );

        // Use centralized API client
        $api = new Nexa_RE_Api_Client();
        $result = $api->list_properties( $filters );

        if ( ! $result['ok'] ) {
            if ( current_user_can( 'manage_options' ) ) {
                $error_msg = $result['error'] ? $result['error'] : 'API error (' . $result['code'] . ')';
                return '<p>Error connecting to Nexa API: ' . esc_html( $error_msg ) . '</p>';
            }
            return '';
        }

        // If Laravel uses pagination, we expect ["data"]; otherwise use whole array
        $data = $result['data'];
        $properties = $data['data'] ?? $data;
        
        // Determine display options from config
        $show_filter    = filter_var( $atts['show_filter'], FILTER_VALIDATE_BOOLEAN );
        $show_map       = filter_var( $atts['show_map'], FILTER_VALIDATE_BOOLEAN );
        $show_price     = filter_var( $atts['show_price'], FILTER_VALIDATE_BOOLEAN );
        $show_bedrooms  = filter_var( $atts['show_bedrooms'], FILTER_VALIDATE_BOOLEAN );
        $show_bathrooms = filter_var( $atts['show_bathrooms'], FILTER_VALIDATE_BOOLEAN );
        $show_city      = filter_var( $atts['show_city'], FILTER_VALIDATE_BOOLEAN );
        $show_area      = filter_var( $atts['show_area'], FILTER_VALIDATE_BOOLEAN );
        $show_location  = filter_var( $atts['show_location'], FILTER_VALIDATE_BOOLEAN );
        
        // Layout and styling options
        $layout              = in_array( $atts['layout'], [ 'grid', 'list' ], true ) ? $atts['layout'] : 'grid';
        $columns             = max( 1, min( 6, intval( $atts['columns'] ) ) );
        $card_style          = sanitize_key( $atts['card_style'] );
        $property_card_layout = in_array( $atts['property_card_layout'], self::VALID_CARD_LAYOUTS, true ) ? $atts['property_card_layout'] : 'default';
        $primary_color       = sanitize_hex_color( $atts['primary_color'] ) ?: '#4f46e5';
        $secondary_color     = sanitize_hex_color( $atts['secondary_color'] ) ?: '#16a34a';

        // Fetch agency parameters for filter dropdowns
        $city_options          = [];
        $property_type_options = [];
        $params_result         = $api->list_agency_parameters();

        if ( $params_result['ok'] && ! empty( $params_result['data']['parameters'] ) ) {
            $parameters = $params_result['data']['parameters'];
            if ( ! empty( $parameters['city'] ) && is_array( $parameters['city'] ) ) {
                $city_options = array_column( $parameters['city'], 'value' );
            }
            if ( ! empty( $parameters['property_type'] ) && is_array( $parameters['property_type'] ) ) {
                $property_type_options = array_column( $parameters['property_type'], 'value' );
            }
        }

        // Check if any properties have location data for the map
        $properties_with_location = [];
        if ( $show_map && is_array( $properties ) ) {
            foreach ( $properties as $prop ) {
                if ( ! empty( $prop['latitude'] ) && ! empty( $prop['longitude'] ) ) {
                    $properties_with_location[] = $prop;
                }
            }
        }
        $has_map_data = ! empty( $properties_with_location );

        // Enqueue map assets if showing map
        if ( $show_map && $has_map_data ) {
            nexa_re_enqueue_map_assets();
        }

        // Build wrapper classes
        $wrapper_classes = [ 'nexa-properties-wrapper' ];
        $wrapper_classes[] = 'nexa-layout-' . esc_attr( $layout );
        $wrapper_classes[] = 'nexa-columns-' . esc_attr( $columns );
        if ( $card_style !== 'default' ) {
            $wrapper_classes[] = 'nexa-card-style-' . esc_attr( $card_style );
        }
        $wrapper_classes[] = 'nexa-card-layout-' . esc_attr( $property_card_layout );

        ob_start();
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" style="--nexa-primary-color: <?php echo esc_attr( $primary_color ); ?>; --nexa-secondary-color: <?php echo esc_attr( $secondary_color ); ?>;">
            <?php if ( $show_filter ) : ?>
            <div class="nexa-filter-header">
                <button type="button" class="nexa-advanced-search-btn" id="nexa-toggle-filter">
                    <span class="nexa-search-icon">🔍</span>
                    Advanced Search
                    <span class="nexa-arrow-icon" id="nexa-arrow-icon">▼</span>
                </button>
            </div>
            <div class="nexa-properties-filter" id="nexa-filter-panel" style="display: none;">
                <form method="get" class="nexa-filter-form">
                    <div class="nexa-filter-row">
                        <div class="nexa-filter-field">
                            <label for="nexa-filter-city">City</label>
                            <?php if ( ! empty( $city_options ) ) : ?>
                            <select id="nexa-filter-city" name="nexa_city">
                                <option value="">All Cities</option>
                                <?php foreach ( $city_options as $city_opt ) : ?>
                                    <option value="<?php echo esc_attr( $city_opt ); ?>" <?php selected( $filter_city, $city_opt ); ?>><?php echo esc_html( $city_opt ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else : ?>
                            <input type="text" id="nexa-filter-city" name="nexa_city" value="<?php echo esc_attr( $filter_city ); ?>" placeholder="Any city">
                            <?php endif; ?>
                        </div>
                        <div class="nexa-filter-field">
                            <label for="nexa-filter-category">Category</label>
                            <select id="nexa-filter-category" name="nexa_category">
                                <option value="">All</option>
                                <option value="rent" <?php selected( $filter_category, 'rent' ); ?>>Rent</option>
                                <option value="buy" <?php selected( $filter_category, 'buy' ); ?>>Buy</option>
                            </select>
                        </div>
                        <div class="nexa-filter-field">
                            <label for="nexa-filter-type">Property Type</label>
                            <?php if ( ! empty( $property_type_options ) ) : ?>
                            <select id="nexa-filter-type" name="nexa_type">
                                <option value="">All Types</option>
                                <?php foreach ( $property_type_options as $type_opt ) : ?>
                                    <option value="<?php echo esc_attr( $type_opt ); ?>" <?php selected( $filter_type, $type_opt ); ?>><?php echo esc_html( $type_opt ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else : ?>
                            <input type="text" id="nexa-filter-type" name="nexa_type" value="<?php echo esc_attr( $filter_type ); ?>" placeholder="e.g. Apartment">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="nexa-filter-row">
                        <div class="nexa-filter-field">
                            <label for="nexa-filter-min-price">Min Price</label>
                            <input type="number" id="nexa-filter-min-price" name="nexa_min_price" value="<?php echo esc_attr( $filter_min ); ?>" placeholder="0" min="0">
                        </div>
                        <div class="nexa-filter-field">
                            <label for="nexa-filter-max-price">Max Price</label>
                            <input type="number" id="nexa-filter-max-price" name="nexa_max_price" value="<?php echo esc_attr( $filter_max ); ?>" placeholder="Any" min="0">
                        </div>
                        <div class="nexa-filter-field">
                            <label for="nexa-filter-bedrooms">Bedrooms</label>
                            <select id="nexa-filter-bedrooms" name="nexa_bedrooms">
                                <option value="">Any</option>
                                <option value="1" <?php selected( $filter_bedrooms, '1' ); ?>>1+</option>
                                <option value="2" <?php selected( $filter_bedrooms, '2' ); ?>>2+</option>
                                <option value="3" <?php selected( $filter_bedrooms, '3' ); ?>>3+</option>
                                <option value="4" <?php selected( $filter_bedrooms, '4' ); ?>>4+</option>
                                <option value="5" <?php selected( $filter_bedrooms, '5' ); ?>>5+</option>
                            </select>
                        </div>
                        <div class="nexa-filter-field">
                            <label for="nexa-filter-bathrooms">Bathrooms</label>
                            <select id="nexa-filter-bathrooms" name="nexa_bathrooms">
                                <option value="">Any</option>
                                <option value="1" <?php selected( $filter_bathrooms, '1' ); ?>>1+</option>
                                <option value="2" <?php selected( $filter_bathrooms, '2' ); ?>>2+</option>
                                <option value="3" <?php selected( $filter_bathrooms, '3' ); ?>>3+</option>
                                <option value="4" <?php selected( $filter_bathrooms, '4' ); ?>>4+</option>
                            </select>
                        </div>
                    </div>
                    <div class="nexa-filter-actions">
                        <button type="submit" class="nexa-filter-btn nexa-filter-btn-primary">Search Properties</button>
                        <?php
                        $clear_url = remove_query_arg( [
                            'nexa_city',
                            'nexa_category',
                            'nexa_type',
                            'nexa_min_price',
                            'nexa_max_price',
                            'nexa_bedrooms',
                            'nexa_bathrooms',
                        ] );
                        ?>
                        <a href="<?php echo esc_url( $clear_url ); ?>" class="nexa-filter-btn nexa-filter-btn-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>
            <script>
            (function() {
                var toggleBtn = document.getElementById('nexa-toggle-filter');
                var filterPanel = document.getElementById('nexa-filter-panel');
                var arrowIcon = document.getElementById('nexa-arrow-icon');
                
                if (toggleBtn && filterPanel) {
                    toggleBtn.addEventListener('click', function() {
                        if (filterPanel.style.display === 'none') {
                            filterPanel.style.display = 'block';
                            arrowIcon.textContent = '▲';
                            toggleBtn.classList.add('nexa-filter-open');
                        } else {
                            filterPanel.style.display = 'none';
                            arrowIcon.textContent = '▼';
                            toggleBtn.classList.remove('nexa-filter-open');
                        }
                    });
                }
            })();
            </script>
            <?php endif; ?>
            
            <?php if ( empty( $properties ) || ! is_array( $properties ) ) : ?>
                <p class="nexa-no-results">No properties found matching your criteria.</p>
            <?php else : ?>
                <?php if ( $show_map && $has_map_data ) : ?>
                <!-- Two-column layout with map -->
                <div class="nexa-properties-split-layout">
                    <div class="nexa-properties-list-column">
                        <div class="nexa-properties-grid nexa-properties-grid-compact">
                            <?php foreach ( $properties as $index => $property ) :
                                echo self::render_property_card( $property, $property_card_layout, $show_price, $show_bedrooms, $show_bathrooms, $show_city, $show_area, $show_location );
                            endforeach; ?>
                        </div>
                    </div>
                    <div class="nexa-properties-map-column">
                        <div id="nexa-properties-list-map" class="nexa-map-container nexa-map-list"></div>
                    </div>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof L === 'undefined') return;
                    
                    var mapEl = document.getElementById('nexa-properties-list-map');
                    if (!mapEl) return;

                    var propertiesData = <?php echo wp_json_encode( array_map( function( $p ) {
                        $first_image = '';
                        if ( ! empty( $p['images'] ) && is_array( $p['images'] ) ) {
                            $first_image = $p['images'][0]['url'] ?? '';
                        }
                        return [
                            'id'        => intval( $p['id'] ?? 0 ),
                            'title'     => esc_html( $p['title'] ?? '' ),
                            'city'      => esc_html( $p['city'] ?? '' ),
                            'price'     => isset( $p['price'] ) ? esc_html( number_format_i18n( $p['price'] ) ) : '',
                            'image'     => esc_url( $first_image ),
                            'latitude'  => $p['latitude'] ?? null,
                            'longitude' => $p['longitude'] ?? null,
                            'url'       => esc_url( self::get_property_url( $p ) ),
                        ];
                    }, $properties_with_location ), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ); ?>;

                    var map = L.map('nexa-properties-list-map');

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19
                    }).addTo(map);

                    // Create marker cluster group
                    var markers = L.markerClusterGroup({
                        iconCreateFunction: function(cluster) {
                            var count = cluster.getChildCount();
                            var size = 'small';
                            if (count >= 10) size = 'medium';
                            if (count >= 50) size = 'large';
                            
                            return L.divIcon({
                                html: '<div class="nexa-cluster-icon nexa-cluster-icon-' + size + '">' + count + '</div>',
                                className: '',
                                iconSize: null
                            });
                        }
                    });

                    var bounds = [];
                    var markerMap = {};

                    propertiesData.forEach(function(prop) {
                        if (!prop.latitude || !prop.longitude) return;
                        
                        var lat = parseFloat(prop.latitude);
                        var lng = parseFloat(prop.longitude);
                        if (isNaN(lat) || isNaN(lng)) return;

                        bounds.push([lat, lng]);

                        var imageHtml = prop.image ? '<div class="nexa-popup-image"><img src="' + prop.image + '" alt="' + prop.title + '"></div>' : '';
                        var popupContent = '<div class="nexa-popup-content">' +
                            imageHtml +
                            '<div class="nexa-popup-details">' +
                            '<p class="nexa-popup-title">' + prop.title + '</p>' +
                            (prop.city ? '<p class="nexa-popup-address">📍 ' + prop.city + '</p>' : '') +
                            (prop.price ? '<p class="nexa-popup-price">' + prop.price + '</p>' : '') +
                            '<a href="' + prop.url + '" class="nexa-popup-link">View Details →</a>' +
                            '</div></div>';

                        var marker = L.marker([lat, lng])
                            .bindPopup(popupContent, { minWidth: 200 });
                        
                        markerMap[prop.id] = marker;
                        markers.addLayer(marker);
                    });

                    map.addLayer(markers);

                    // Fit map to markers
                    if (bounds.length > 0) {
                        map.fitBounds(bounds, { padding: [30, 30] });
                    } else {
                        map.setView([33.8886, 35.4955], 8);
                    }

                    // Highlight property on map when hovering over card
                    document.querySelectorAll('.nexa-property-card[data-property-id]').forEach(function(card) {
                        card.addEventListener('mouseenter', function() {
                            var propId = this.dataset.propertyId;
                            if (markerMap[propId]) {
                                markerMap[propId].openPopup();
                            }
                        });
                    });
                });
                </script>
                <?php else : ?>
                <!-- Standard grid layout -->
                <div class="nexa-properties-grid">
                    <?php foreach ( $properties as $property ) :
                        echo self::render_property_card( $property, $property_card_layout, $show_price, $show_bedrooms, $show_bathrooms, $show_city, $show_area, $show_location );
                    endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <style>
            .nexa-properties-wrapper {
                max-width: 1800px;
                margin: 0 auto;
            }
            .nexa-properties-wrapper.nexa-has-map {
                max-width: 1600px;
            }
            .nexa-properties-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
                gap: 24px;
            }
            .nexa-properties-grid-compact {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 16px;
            }
            .nexa-property-card {
                background: #ffffff;
                border-radius: 18px;
                overflow: hidden;
                box-shadow: 0 10px 25px rgba(15,23,42,0.08);
                display: flex;
                flex-direction: column;
                transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
                text-decoration: none;
                color: inherit;
                position: relative;
                border: 2px solid transparent;
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
                color: var(--nexa-secondary-color, #16a34a);
                font-size: 16px;
                white-space: nowrap;
            }
            .nexa-property-view-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 12px;
                font-weight: 600;
                color: var(--nexa-primary-color, #4f46e5);
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
            .nexa-property-address {
                font-size: 11px;
                color: #94a3b8;
                margin: 0;
            }

            @media (max-width: 640px) {
                .nexa-properties-grid {
                    grid-template-columns: 1fr;
                }
            }
            
            /* Layout variants */
            .nexa-layout-list .nexa-properties-grid {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .nexa-layout-list .nexa-property-card {
                flex-direction: row;
                max-width: 100%;
            }
            .nexa-layout-list .nexa-property-image {
                width: 280px;
                flex-shrink: 0;
                padding-top: 0;
                height: 180px;
            }
            .nexa-layout-list .nexa-property-body {
                flex: 1;
                justify-content: center;
            }
            
            /* Column variants */
            .nexa-columns-1 .nexa-properties-grid { grid-template-columns: 1fr; }
            .nexa-columns-2 .nexa-properties-grid { grid-template-columns: repeat(2, 1fr); }
            .nexa-columns-3 .nexa-properties-grid { grid-template-columns: repeat(3, 1fr); }
            .nexa-columns-4 .nexa-properties-grid { grid-template-columns: repeat(4, 1fr); }
            .nexa-columns-5 .nexa-properties-grid { grid-template-columns: repeat(5, 1fr); }
            .nexa-columns-6 .nexa-properties-grid { grid-template-columns: repeat(6, 1fr); }
            
            @media (max-width: 1200px) {
                .nexa-columns-5 .nexa-properties-grid,
                .nexa-columns-6 .nexa-properties-grid { grid-template-columns: repeat(4, 1fr); }
            }
            @media (max-width: 992px) {
                .nexa-columns-4 .nexa-properties-grid,
                .nexa-columns-5 .nexa-properties-grid,
                .nexa-columns-6 .nexa-properties-grid { grid-template-columns: repeat(3, 1fr); }
            }
            @media (max-width: 768px) {
                .nexa-columns-3 .nexa-properties-grid,
                .nexa-columns-4 .nexa-properties-grid,
                .nexa-columns-5 .nexa-properties-grid,
                .nexa-columns-6 .nexa-properties-grid { grid-template-columns: repeat(2, 1fr); }
                .nexa-layout-list .nexa-property-card {
                    flex-direction: column;
                }
                .nexa-layout-list .nexa-property-image {
                    width: 100%;
                    height: 200px;
                }
            }
            
            /* Filter Header and Toggle Button */
            .nexa-filter-header {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 16px;
            }
            .nexa-advanced-search-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 18px;
                background: #ffffff;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                color: #374151;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .nexa-advanced-search-btn:hover {
                background: #f9fafb;
                border-color: #9ca3af;
            }
            .nexa-advanced-search-btn.nexa-filter-open {
                background: var(--nexa-primary-color, #4f46e5);
                border-color: var(--nexa-primary-color, #4f46e5);
                color: #ffffff;
            }
            .nexa-search-icon {
                font-size: 14px;
            }
            .nexa-arrow-icon {
                font-size: 10px;
                transition: transform 0.2s ease;
            }
            
            /* Filter Form Styles */
            .nexa-properties-filter {
                background: #ffffff;
                border-radius: 4px;
                padding: 20px 24px;
                margin-bottom: 28px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
                border: 1px solid #d1d5db;
            }
            .nexa-filter-form {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .nexa-filter-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
                align-items: end;
            }
            .nexa-filter-field {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .nexa-filter-field label {
                font-size: 12px;
                font-weight: 500;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }
            .nexa-filter-field input,
            .nexa-filter-field select {
                padding: 8px 12px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                font-size: 14px;
                color: #1f2937;
                background: #ffffff;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
                height: 40px;
            }
            .nexa-filter-field input:focus,
            .nexa-filter-field select:focus {
                outline: none;
                border-color: #4f46e5;
                box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
            }
            .nexa-filter-field input::placeholder {
                color: #9ca3af;
            }
            .nexa-filter-actions {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                padding-top: 16px;
                border-top: 1px solid #d1d5db;
                margin-top: 8px;
            }
            .nexa-filter-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 20px;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.15s ease;
                text-decoration: none;
                border: none;
            }
            .nexa-filter-btn-primary {
                background: var(--nexa-primary-color, #4f46e5);
                color: #ffffff;
            }
            .nexa-filter-btn-primary:hover {
                filter: brightness(0.9);
            }
            .nexa-filter-btn-secondary {
                background: #ffffff;
                color: #6b7280;
                border: 1px solid #d1d5db;
            }
            .nexa-filter-btn-secondary:hover {
                background: #f9fafb;
                color: #374151;
            }
            .nexa-no-results {
                text-align: center;
                color: #6b7280;
                font-size: 15px;
                padding: 40px 24px;
                background: #f9fafb;
                border-radius: 4px;
                border: 1px dashed #d1d5db;
            }
            
            @media (max-width: 640px) {
                .nexa-filter-row {
                    grid-template-columns: 1fr 1fr;
                }
                .nexa-filter-actions {
                    flex-direction: column;
                }
                .nexa-filter-btn {
                    width: 100%;
                }
            }
            @media (max-width: 480px) {
                .nexa-filter-row {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
        return ob_get_clean();
    }


}
