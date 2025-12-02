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
            'show_filter'   => 'true',
            'show_map'      => 'true',
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
        
        // Determine if filter form should be shown
        $show_filter = filter_var( $atts['show_filter'], FILTER_VALIDATE_BOOLEAN );
        $show_map    = filter_var( $atts['show_map'], FILTER_VALIDATE_BOOLEAN );

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

        ob_start();
        ?>
        <div class="nexa-properties-wrapper">
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
                            <input type="text" id="nexa-filter-city" name="nexa_city" value="<?php echo esc_attr( $filter_city ); ?>" placeholder="Any city">
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
                            <input type="text" id="nexa-filter-type" name="nexa_type" value="<?php echo esc_attr( $filter_type ); ?>" placeholder="e.g. Apartment">
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
                                $first_image = '';
                                if ( ! empty( $property['images'] ) && is_array( $property['images'] ) ) {
                                    $first_image = $property['images'][0]['url'] ?? '';
                                }

                                $prop_id     = $property['id'] ?? $index;
                                $title       = $property['title'] ?? '';
                                $city        = $property['city'] ?? '';
                                $category    = $property['category'] ?? '';
                                $price       = isset( $property['price'] ) ? $property['price'] : null;
                                $bedrooms    = $property['bedrooms'] ?? null;
                                $bathrooms   = $property['bathrooms'] ?? null;
                                $detail_url  = self::get_property_url( $property );
                                ?>
                                <a class="nexa-property-card" href="<?php echo esc_url( $detail_url ); ?>" data-property-id="<?php echo esc_attr( $prop_id ); ?>">
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
                background: #4f46e5;
                border-color: #4f46e5;
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
                background: #4f46e5;
                color: #ffffff;
            }
            .nexa-filter-btn-primary:hover {
                background: #4338ca;
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

                // Validate and sanitize latitude/longitude
                $latitude  = null;
                $longitude = null;

                if ( isset( $_POST['latitude'] ) && $_POST['latitude'] !== '' ) {
                    $lat_val = floatval( $_POST['latitude'] );
                    if ( $lat_val >= -90 && $lat_val <= 90 ) {
                        $latitude = $lat_val;
                    }
                }

                if ( isset( $_POST['longitude'] ) && $_POST['longitude'] !== '' ) {
                    $lng_val = floatval( $_POST['longitude'] );
                    if ( $lng_val >= -180 && $lng_val <= 180 ) {
                        $longitude = $lng_val;
                    }
                }

                $payload = [
                    'title'         => sanitize_text_field( $_POST['title'] ?? '' ),
                    'description'   => wp_kses_post( $_POST['description'] ?? '' ),
                    'category'      => sanitize_text_field( $_POST['category'] ?? '' ),
                    'city'          => sanitize_text_field( $_POST['city'] ?? '' ),
                    'property_type' => sanitize_text_field( $_POST['property_type'] ?? '' ),
                    'area'          => $_POST['area'] !== '' ? (int) $_POST['area'] : null,
                    'address'       => sanitize_text_field( $_POST['address'] ?? '' ),
                    'latitude'      => $latitude,
                    'longitude'     => $longitude,
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

        // Enqueue map assets for the dashboard
        nexa_re_enqueue_map_assets();

        ob_start();
        // variables are already in scope: $messages, $properties, $total_properties,
        // $properties_this_week, $current_user
        include NEXA_RE_PLUGIN_DIR . 'views/dashboard-shell.php';
        return ob_get_clean();
    }


}
