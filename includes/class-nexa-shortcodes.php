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
                    $description = $property['description'] ?? '';
                    ?>
                    <div class="nexa-property-card"
                         data-property="<?php echo esc_attr( wp_json_encode( $property ) ); ?>">
                        <div class="nexa-property-image">
                            <?php if ( $first_image ) : ?>
                                <img src="<?php echo esc_url( $first_image ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                            <?php else : ?>
                                <div class="nexa-property-image-placeholder">No image</div>
                            <?php endif; ?>
                        </div>
                        <div class="nexa-property-body">
                            <div class="nexa-property-badge">
                                <?php echo esc_html( ucfirst( $category ) ); ?>
                            </div>
                            <h3 class="nexa-property-title"><?php echo esc_html( $title ); ?></h3>
                            <p class="nexa-property-location"><?php echo esc_html( $city ); ?></p>

                            <div class="nexa-property-meta">
                                <?php if ( $bedrooms ) : ?>
                                    <span><?php echo intval( $bedrooms ); ?> bd</span>
                                <?php endif; ?>
                                <?php if ( $bathrooms ) : ?>
                                    <span><?php echo intval( $bathrooms ); ?> ba</span>
                                <?php endif; ?>
                            </div>

                            <?php if ( $price ) : ?>
                                <div class="nexa-property-price">
                                    <?php echo esc_html( number_format_i18n( $price ) ); ?>
                                </div>
                            <?php endif; ?>

                            <button type="button" class="nexa-property-view-btn">
                                View details
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Modal (scoped inside wrapper) -->
            <div class="nexa-modal-overlay" style="display:none;">
                <div class="nexa-modal">
                    <button type="button" class="nexa-modal-close">&times;</button>

                    <div class="nexa-modal-grid">
                        <div class="nexa-modal-gallery">
                            <div class="nexa-modal-main-image"></div>
                            <div class="nexa-modal-thumbs"></div>
                        </div>

                        <div class="nexa-modal-content">
                            <div class="nexa-modal-badge"></div>
                            <h2 class="nexa-modal-title"></h2>
                            <p class="nexa-modal-location"></p>

                            <div class="nexa-modal-meta"></div>
                            <div class="nexa-modal-price"></div>

                            <div class="nexa-modal-description"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .nexa-properties-wrapper {
                max-width: 1200px;
                margin: 0 auto;
            }
            .nexa-properties-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
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
                cursor: pointer;
            }
            .nexa-property-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 16px 40px rgba(15,23,42,0.14);
            }
            .nexa-property-image {
                position: relative;
                padding-top: 65%;
                overflow: hidden;
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
                background: #f1f5f9;
                color: #94a3b8;
                font-size: 12px;
            }
            .nexa-property-body {
                padding: 14px 16px 16px;
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .nexa-property-badge {
                display: inline-flex;
                align-items: center;
                padding: 2px 8px;
                font-size: 10px;
                text-transform: uppercase;
                border-radius: 999px;
                background: #e0e7ff;
                color: #4f46e5;
                font-weight: 600;
            }
            .nexa-property-title {
                font-size: 15px;
                font-weight: 600;
                color: #0f172a;
                margin: 4px 0 0;
            }
            .nexa-property-location {
                font-size: 12px;
                color: #64748b;
            }
            .nexa-property-meta {
                display: flex;
                gap: 10px;
                font-size: 11px;
                color: #64748b;
                margin-top: 4px;
            }
            .nexa-property-meta span::before {
                content: "•";
                margin-right: 4px;
                color: #cbd5f5;
            }
            .nexa-property-price {
                margin-top: 6px;
                font-weight: 700;
                color: #4f46e5;
                font-size: 16px;
            }
            .nexa-property-view-btn {
                margin-top: 10px;
                border: none;
                background: #4f46e5;
                color: #fff;
                border-radius: 999px;
                padding: 6px 12px;
                font-size: 12px;
                font-weight: 500;
                align-self: flex-start;
                cursor: pointer;
                transition: background 0.15s ease, transform 0.1s ease;
            }
            .nexa-property-view-btn:hover {
                background: #4338ca;
                transform: translateY(-1px);
            }

            /* Modal */
            .nexa-modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(15,23,42,0.55);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
            }
            .nexa-modal {
                background: #ffffff;
                width: min(1000px, 94%);
                max-height: 90vh;
                border-radius: 22px;
                padding: 20px;
                position: relative;
                overflow: hidden;
            }
            .nexa-modal-close {
                position: absolute;
                top: 14px;
                right: 16px;
                border: none;
                background: transparent;
                font-size: 26px;
                line-height: 1;
                cursor: pointer;
                color: #94a3b8;
            }
            .nexa-modal-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
                gap: 20px;
                max-height: calc(90vh - 40px);
            }
            .nexa-modal-gallery {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .nexa-modal-main-image {
                border-radius: 18px;
                overflow: hidden;
                background: #f1f5f9;
                min-height: 260px;
            }
            .nexa-modal-main-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .nexa-modal-thumbs {
                display: flex;
                gap: 8px;
                overflow-x: auto;
                padding-bottom: 4px;
            }
            .nexa-modal-thumbs img {
                width: 70px;
                height: 60px;
                object-fit: cover;
                border-radius: 10px;
                cursor: pointer;
                opacity: 0.65;
                border: 2px solid transparent;
            }
            .nexa-modal-thumbs img.nexa-thumb-active {
                opacity: 1;
                border-color: #4f46e5;
            }

            .nexa-modal-content {
                overflow-y: auto;
                padding-right: 4px;
            }
            .nexa-modal-badge {
                display: inline-flex;
                padding: 3px 10px;
                font-size: 10px;
                text-transform: uppercase;
                border-radius: 999px;
                background: #e0e7ff;
                color: #4f46e5;
                font-weight: 600;
                margin-bottom: 6px;
            }
            .nexa-modal-title {
                font-size: 20px;
                font-weight: 700;
                color: #0f172a;
                margin: 0 0 4px;
            }
            .nexa-modal-location {
                font-size: 13px;
                color: #64748b;
                margin-bottom: 10px;
            }
            .nexa-modal-meta {
                font-size: 12px;
                color: #64748b;
                display: flex;
                gap: 12px;
                margin-bottom: 8px;
            }
            .nexa-modal-meta span::before {
                content: "•";
                margin-right: 5px;
                color: #cbd5f5;
            }
            .nexa-modal-price {
                font-size: 18px;
                font-weight: 700;
                color: #4f46e5;
                margin-bottom: 12px;
            }
            .nexa-modal-description {
                font-size: 13px;
                color: #475569;
                line-height: 1.5;
            }

            @media (max-width: 800px) {
                .nexa-modal-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <script>
            (function() {
                var wrappers = document.querySelectorAll('.nexa-properties-wrapper');
                wrappers.forEach(function(wrapper) {
                    var cards   = wrapper.querySelectorAll('.nexa-property-card');
                    var overlay = wrapper.querySelector('.nexa-modal-overlay');
                    if (!overlay) return;

                    var modal   = overlay.querySelector('.nexa-modal');
                    var closeBtn = overlay.querySelector('.nexa-modal-close');

                    var mainImage = overlay.querySelector('.nexa-modal-main-image');
                    var thumbs    = overlay.querySelector('.nexa-modal-thumbs');
                    var badge     = overlay.querySelector('.nexa-modal-badge');
                    var titleEl   = overlay.querySelector('.nexa-modal-title');
                    var locEl     = overlay.querySelector('.nexa-modal-location');
                    var metaEl    = overlay.querySelector('.nexa-modal-meta');
                    var priceEl   = overlay.querySelector('.nexa-modal-price');
                    var descEl    = overlay.querySelector('.nexa-modal-description');

                    function openModal(property) {
                        var images = Array.isArray(property.images) ? property.images : [];

                        // badge, title, location
                        badge.textContent = (property.category || '').toUpperCase();
                        titleEl.textContent = property.title || '';
                        locEl.textContent = property.city || '';

                        // meta
                        var metaBits = [];
                        if (property.bedrooms) metaBits.push(property.bedrooms + ' bd');
                        if (property.bathrooms) metaBits.push(property.bathrooms + ' ba');
                        metaEl.textContent = metaBits.join('   ');

                        // price
                        if (property.price) {
                            try {
                                priceEl.textContent = new Intl.NumberFormat().format(property.price);
                            } catch (e) {
                                priceEl.textContent = property.price;
                            }
                        } else {
                            priceEl.textContent = '';
                        }

                        // description
                        descEl.textContent = property.description || '';

                        // gallery
                        mainImage.innerHTML = '';
                        thumbs.innerHTML = '';

                        if (images.length) {
                            var first = images[0].url || '';

                            if (first) {
                                var img = document.createElement('img');
                                img.src = first;
                                mainImage.appendChild(img);
                            }

                            images.forEach(function(imgData, index) {
                                if (!imgData.url) return;
                                var t = document.createElement('img');
                                t.src = imgData.url;
                                if (index === 0) t.classList.add('nexa-thumb-active');
                                t.addEventListener('click', function() {
                                    mainImage.innerHTML = '';
                                    var big = document.createElement('img');
                                    big.src = imgData.url;
                                    mainImage.appendChild(big);

                                    thumbs.querySelectorAll('img').forEach(function(i) {
                                        i.classList.remove('nexa-thumb-active');
                                    });
                                    t.classList.add('nexa-thumb-active');
                                });
                                thumbs.appendChild(t);
                            });
                        } else {
                            var placeholder = document.createElement('div');
                            placeholder.style.display = 'flex';
                            placeholder.style.alignItems = 'center';
                            placeholder.style.justifyContent = 'center';
                            placeholder.style.height = '260px';
                            placeholder.style.color = '#94a3b8';
                            placeholder.style.fontSize = '13px';
                            placeholder.textContent = 'No images available';
                            mainImage.appendChild(placeholder);
                        }

                        overlay.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }

                    function closeModal() {
                        overlay.style.display = 'none';
                        document.body.style.overflow = '';
                    }

                    cards.forEach(function(card) {
                        var btn = card.querySelector('.nexa-property-view-btn');
                        function handleClick(e) {
                            if (e) e.stopPropagation();
                            var json = card.getAttribute('data-property');
                            if (!json) return;
                            try {
                                var property = JSON.parse(json);
                                openModal(property);
                            } catch (err) {}
                        }

                        if (btn) {
                            btn.addEventListener('click', handleClick);
                        }
                        card.addEventListener('click', handleClick);
                    });

                    if (closeBtn) {
                        closeBtn.addEventListener('click', closeModal);
                    }
                    overlay.addEventListener('click', function(e) {
                        if (e.target === overlay) {
                            closeModal();
                        }
                    });
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape' && overlay.style.display === 'flex') {
                            closeModal();
                        }
                    });
                });
            })();
        </script>
        <?php
        return ob_get_clean();
    }
}
