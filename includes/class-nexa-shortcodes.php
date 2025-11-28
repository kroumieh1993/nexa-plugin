<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Shortcodes {

    public static function register_shortcodes() {
        add_shortcode( 'nexa_properties', [ __CLASS__, 'render_properties' ] );
        add_shortcode( 'nexa_agency_dashboard', [ __CLASS__, 'render_agency_dashboard' ] );
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
        $api_url   = rtrim( Nexa_RE_Settings::API_BASE_URL, '/' );
        $api_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );

        if ( ! $api_token ) {
            return '<p><strong>Nexa:</strong> Agency API token is not configured. Contact your site administrator.</p>';
        }

        $messages = [];

        // 3. Handle DELETE action (via GET)
        if ( isset( $_GET['nexa_action'], $_GET['property_id'] ) && $_GET['nexa_action'] === 'delete_property' ) {
            $property_id = (int) $_GET['property_id'];

            if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'nexa_delete_property_' . $property_id ) ) {
                $messages[] = [ 'type' => 'error', 'text' => 'Security check failed.' ];
            } else {
                $endpoint = $api_url . '/properties/' . $property_id;

                $response = wp_remote_request( $endpoint, [
                    'method'  => 'DELETE',
                    'headers' => [
                        'X-AGENCY-TOKEN' => $api_token,
                        'Accept'         => 'application/json',
                    ],
                    'timeout' => 15,
                ] );

                if ( is_wp_error( $response ) ) {
                    $messages[] = [ 'type' => 'error', 'text' => 'Error deleting property: ' . $response->get_error_message() ];
                } else {
                    $code = wp_remote_retrieve_response_code( $response );
                    if ( 200 === $code ) {
                        $messages[] = [ 'type' => 'success', 'text' => 'Property deleted.' ];
                    } else {
                        $messages[] = [ 'type' => 'error', 'text' => 'API error (' . $code . ') deleting property.' ];
                    }
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

                $args = [
                    'headers' => [
                        'X-AGENCY-TOKEN' => $api_token,
                        'Accept'         => 'application/json',
                        'Content-Type'   => 'application/json',
                    ],
                    'body'    => wp_json_encode( $payload ),
                    'timeout' => 15,
                ];

                if ( $is_edit ) {
                    $endpoint      = $api_url . '/properties/' . $property_id;
                    $args['method'] = 'PUT';
                    $response      = wp_remote_request( $endpoint, $args );
                } else {
                    $endpoint      = $api_url . '/properties';
                    $args['method'] = 'POST';
                    $response      = wp_remote_post( $endpoint, $args );
                }

                if ( is_wp_error( $response ) ) {
                    $messages[] = [ 'type' => 'error', 'text' => 'Error saving property: ' . $response->get_error_message() ];
                } else {
                    $code = wp_remote_retrieve_response_code( $response );
                    if ( in_array( $code, [ 200, 201 ], true ) ) {
                        $messages[] = [ 'type' => 'success', 'text' => 'Property saved successfully.' ];
                    } else {
                        $messages[] = [ 'type' => 'error', 'text' => 'API error (' . $code . ') saving property.' ];
                    }
                }
            }
        }

        // 5. Fetch properties for list & stats
        $endpoint  = $api_url . '/properties';
        $response  = wp_remote_get( $endpoint, [
            'headers' => [
                'X-AGENCY-TOKEN' => $api_token,
                'Accept'         => 'application/json',
            ],
            'timeout' => 15,
        ] );

        $properties = [];
        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $data       = json_decode( wp_remote_retrieve_body( $response ), true );
            $properties = $data['data'] ?? $data;
            if ( ! is_array( $properties ) ) {
                $properties = [];
            }
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

        ob_start();
        ?>
        <div class="nexa-agency-shell">
            <div class="nexa-agency-layout">
                <aside class="nexa-sidebar">
                    <div class="nexa-sidebar-header">
                        <div class="nexa-sidebar-logo-circle">N</div>
                        <div>
                            <div class="nexa-sidebar-title">Nexa Property Suite</div>
                            <div class="nexa-sidebar-subtitle">Agency Admin</div>
                        </div>
                    </div>

                    <nav class="nexa-sidebar-nav">
                        <a class="nexa-nav-link nexa-nav-link-active">
                            <span class="nexa-nav-icon">▢</span>
                            <span>Dashboard</span>
                        </a>
                        <a class="nexa-nav-link">
                            <span class="nexa-nav-icon">▥</span>
                            <span>Properties</span>
                        </a>
                    </nav>

                    <div class="nexa-sidebar-section-label">SYSTEM</div>
                    <nav class="nexa-sidebar-nav">
                        <a class="nexa-nav-link">
                            <span class="nexa-nav-icon">⚙</span>
                            <span>Settings</span>
                        </a>
                    </nav>

                    <div class="nexa-sidebar-footer">
                        © <?php echo esc_html( date( 'Y' ) ); ?> Nexa
                    </div>
                </aside>

                <main class="nexa-main">
                    <header class="nexa-topbar">
                        <div class="nexa-topbar-title">Dashboard</div>
                        <div class="nexa-topbar-right">
                            <div class="nexa-topbar-search">
                                <span class="nexa-topbar-search-icon">🔍</span>
                                <input type="text" placeholder="Search (coming soon)" disabled>
                            </div>
                            <div class="nexa-topbar-user">
                                <div class="nexa-user-avatar">
                                    <?php echo esc_html( strtoupper( mb_substr( $current_user->display_name, 0, 1 ) ) ); ?>
                                </div>
                                <div class="nexa-user-meta">
                                    <div class="nexa-user-name"><?php echo esc_html( $current_user->display_name ); ?></div>
                                    <div class="nexa-user-role">Admin</div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <?php if ( ! empty( $messages ) ) : ?>
                        <div class="nexa-messages">
                            <?php foreach ( $messages as $msg ) : ?>
                                <div class="nexa-banner nexa-banner-<?php echo esc_attr( $msg['type'] ); ?>">
                                    <?php echo esc_html( $msg['text'] ); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <section class="nexa-stats-row">
                        <div class="nexa-stat-card">
                            <div class="nexa-stat-label">TOTAL PROPERTIES</div>
                            <div class="nexa-stat-value"><?php echo esc_html( $total_properties ); ?></div>
                            <div class="nexa-stat-sub">Across your agency</div>
                        </div>

                        <div class="nexa-stat-card">
                            <div class="nexa-stat-label">THIS WEEK (PROPERTIES)</div>
                            <div class="nexa-stat-value"><?php echo esc_html( $properties_this_week ); ?></div>
                            <div class="nexa-stat-sub">New properties added</div>
                        </div>

                        <div class="nexa-stat-card nexa-stat-card-highlight">
                            <div class="nexa-stat-label">SYSTEM HEALTH</div>
                            <div class="nexa-stat-value">99.9%</div>
                            <div class="nexa-stat-sub">API uptime (placeholder)</div>
                        </div>
                    </section>

                    <section class="nexa-properties-section">
                        <div class="nexa-properties-header">
                            <div>
                                <h2 class="nexa-section-title">Your Properties</h2>
                                <p class="nexa-section-subtitle">Manage the properties published on your website.</p>
                            </div>
                            <button type="button" class="nexa-btn" id="nexa-add-property-btn" disabled>
                                + Add New Property (coming soon)
                            </button>
                        </div>

                        <div class="nexa-card">
                            <div class="nexa-table-wrapper">
                                <table class="nexa-table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>City</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Created</th>
                                            <th style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ( empty( $properties ) ) : ?>
                                            <tr>
                                                <td colspan="6" class="nexa-table-empty">
                                                    No properties found yet. You’ll be able to create them directly from here soon.
                                                </td>
                                            </tr>
                                        <?php else : ?>
                                            <?php foreach ( $properties as $p ) : ?>
                                                <?php
                                                $created = '—';
                                                if ( ! empty( $p['created_at'] ) ) {
                                                    $timestamp = strtotime( $p['created_at'] );
                                                    if ( $timestamp ) {
                                                        $created = date_i18n( get_option( 'date_format' ), $timestamp );
                                                    }
                                                }

                                                $delete_url = wp_nonce_url(
                                                    add_query_arg(
                                                        [
                                                            'nexa_action' => 'delete_property',
                                                            'property_id' => intval( $p['id'] ),
                                                        ],
                                                        get_permalink()
                                                    ),
                                                    'nexa_delete_property_' . intval( $p['id'] )
                                                );
                                                ?>
                                                <tr>
                                                    <td class="nexa-table-title">
                                                        <?php echo esc_html( $p['title'] ?? '' ); ?>
                                                    </td>
                                                    <td><?php echo esc_html( $p['city'] ?? '' ); ?></td>
                                                    <td><?php echo esc_html( ucfirst( $p['category'] ?? '' ) ); ?></td>
                                                    <td>
                                                        <?php
                                                        if ( isset( $p['price'] ) ) {
                                                            echo esc_html( number_format_i18n( $p['price'] ) );
                                                        } else {
                                                            echo '—';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo esc_html( $created ); ?></td>
                                                    <td>
                                                        <a class="nexa-link-muted" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('Delete this property?');">
                                                            Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>

        <style>
            .nexa-agency-shell {
                --nexa-primary: #4f46e5;
                --nexa-primary-soft: #eef2ff;
                --nexa-bg: #f5f3ff;
                --nexa-sidebar-bg: #020617;
                --nexa-sidebar-text: #e5e7eb;
                --nexa-card-bg: #ffffff;
                --nexa-border-subtle: #e5e7eb;
                --nexa-text-main: #0f172a;
                --nexa-text-muted: #6b7280;
                --nexa-danger: #ef4444;
                --nexa-success: #22c55e;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", sans-serif;
            }

            .nexa-agency-layout {
                display: grid;
                grid-template-columns: 240px minmax(0, 1fr);
                min-height: 70vh;
                background: var(--nexa-bg);
            }

            /* Sidebar */
            .nexa-sidebar {
                background: var(--nexa-sidebar-bg);
                color: var(--nexa-sidebar-text);
                padding: 20px 18px;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .nexa-sidebar-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
            }

            .nexa-sidebar-logo-circle {
                width: 32px;
                height: 32px;
                border-radius: 999px;
                background: var(--nexa-primary);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 15px;
            }

            .nexa-sidebar-title {
                font-size: 13px;
                font-weight: 600;
            }

            .nexa-sidebar-subtitle {
                font-size: 11px;
                opacity: 0.6;
            }

            .nexa-sidebar-nav {
                display: flex;
                flex-direction: column;
                gap: 6px;
                margin-top: 6px;
            }

            .nexa-nav-link {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                padding: 7px 9px;
                border-radius: 10px;
                text-decoration: none;
                color: inherit;
                opacity: 0.7;
            }

            .nexa-nav-link-active,
            .nexa-nav-link:hover {
                opacity: 1;
                background: rgba(148, 163, 184, 0.12);
            }

            .nexa-nav-icon {
                font-size: 12px;
            }

            .nexa-sidebar-section-label {
                font-size: 11px;
                text-transform: uppercase;
                opacity: 0.6;
                margin-top: 12px;
            }

            .nexa-sidebar-footer {
                margin-top: auto;
                font-size: 11px;
                opacity: 0.5;
            }

            /* Main */
            .nexa-main {
                padding: 20px 28px 40px;
                display: flex;
                flex-direction: column;
                gap: 18px;
            }

            .nexa-topbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .nexa-topbar-title {
                font-size: 20px;
                font-weight: 600;
                color: var(--nexa-text-main);
            }

            .nexa-topbar-right {
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .nexa-topbar-search {
                position: relative;
                max-width: 260px;
                width: 100%;
            }

            .nexa-topbar-search input {
                width: 100%;
                border-radius: 999px;
                border: 1px solid var(--nexa-border-subtle);
                padding: 7px 32px;
                font-size: 12px;
                background: #f9fafb;
            }

            .nexa-topbar-search-icon {
                position: absolute;
                left: 10px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 12px;
                opacity: 0.6;
            }

            .nexa-topbar-user {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .nexa-user-avatar {
                width: 32px;
                height: 32px;
                border-radius: 999px;
                background: var(--nexa-primary);
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 14px;
            }

            .nexa-user-meta {
                font-size: 11px;
                color: var(--nexa-text-main);
            }

            .nexa-user-name {
                font-weight: 500;
            }

            .nexa-user-role {
                opacity: 0.6;
            }

            .nexa-messages {
                margin-top: 8px;
            }

            .nexa-banner {
                padding: 8px 10px;
                border-radius: 10px;
                font-size: 12px;
                margin-bottom: 6px;
            }

            .nexa-banner-success {
                background: #ecfdf3;
                color: #15803d;
            }

            .nexa-banner-error {
                background: #fef2f2;
                color: #b91c1c;
            }

            .nexa-stats-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 14px;
                margin-top: 8px;
            }

            .nexa-stat-card {
                background: var(--nexa-card-bg);
                border-radius: 18px;
                padding: 14px 16px;
                box-shadow: 0 10px 30px rgba(148,163,184,0.18);
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .nexa-stat-card-highlight {
                background: var(--nexa-primary);
                color: #fff;
            }

            .nexa-stat-label {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: #6b7280;
            }

            .nexa-stat-card-highlight .nexa-stat-label {
                color: rgba(241,245,249,0.9);
            }

            .nexa-stat-value {
                font-size: 20px;
                font-weight: 700;
                color: var(--nexa-text-main);
            }

            .nexa-stat-card-highlight .nexa-stat-value {
                color: #fff;
            }

            .nexa-stat-sub {
                font-size: 11px;
                color: var(--nexa-text-muted);
            }

            .nexa-stat-card-highlight .nexa-stat-sub {
                color: rgba(226,232,240,0.95);
            }

            .nexa-properties-section {
                margin-top: 10px;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .nexa-properties-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .nexa-section-title {
                font-size: 16px;
                font-weight: 600;
                color: var(--nexa-text-main);
            }

            .nexa-section-subtitle {
                font-size: 12px;
                color: var(--nexa-text-muted);
                margin-top: 2px;
            }

            .nexa-btn {
                border: none;
                border-radius: 999px;
                padding: 8px 14px;
                font-size: 12px;
                font-weight: 500;
                background: var(--nexa-primary);
                color: #fff;
                cursor: not-allowed;
                opacity: 0.65;
            }

            .nexa-card {
                background: var(--nexa-card-bg);
                border-radius: 18px;
                box-shadow: 0 10px 30px rgba(148,163,184,0.18);
                padding: 12px 14px 14px;
            }

            .nexa-table-wrapper {
                overflow-x: auto;
            }

            .nexa-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
            }

            .nexa-table thead {
                background: #f9fafb;
            }

            .nexa-table th,
            .nexa-table td {
                padding: 8px 10px;
                text-align: left;
                border-bottom: 1px solid #f1f5f9;
            }

            .nexa-table th {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: #9ca3af;
            }

            .nexa-table-title {
                font-weight: 500;
                color: var(--nexa-text-main);
            }

            .nexa-table tbody tr:hover {
                background: #f9fafb;
            }

            .nexa-table-empty {
                text-align: center;
                font-size: 12px;
                color: var(--nexa-text-muted);
            }

            .nexa-link-muted {
                font-size: 12px;
                color: #9ca3af;
            }

            .nexa-link-muted:hover {
                color: #ef4444;
            }

            @media (max-width: 900px) {
                .nexa-agency-layout {
                    grid-template-columns: 1fr;
                }
                .nexa-sidebar {
                    position: sticky;
                    top: 0;
                    z-index: 5;
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
                .nexa-sidebar-nav,
                .nexa-sidebar-section-label,
                .nexa-sidebar-footer {
                    display: none;
                }
            }
        </style>
        <?php

        return ob_get_clean();
    }


}
