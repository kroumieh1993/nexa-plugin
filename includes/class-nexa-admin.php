<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Admin {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
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

        add_submenu_page(
            'nexa-properties',
            'All Properties',
            'All Properties',
            'manage_options',
            'nexa-properties-list',
            [ __CLASS__, 'render_properties_list_page' ]
        );

        add_submenu_page(
            'nexa-properties',
            'Add New Property',
            'Add New',
            'manage_options',
            'nexa-properties-add',
            [ __CLASS__, 'render_property_form_page' ]
        );

        add_submenu_page(
            'nexa-properties',
            'Agency Customization',
            'Customization',
            'manage_options',
            'nexa-customization',
            [ __CLASS__, 'render_customization_page' ]
        );
    }

    public static function enqueue_assets( $hook ) {
        // Only load on our pages
        if ( strpos( $hook, 'nexa-properties' ) === false && strpos( $hook, 'nexa-customization' ) === false ) {
            return;
        }

        // Media for image uploads
        wp_enqueue_media();

        // Enqueue admin CSS for customization page
        if ( strpos( $hook, 'nexa-customization' ) !== false ) {
            wp_enqueue_style(
                'nexa-admin-customization',
                NEXA_RE_PLUGIN_URL . 'assets/css/nexa-admin-customization.css',
                [],
                NEXA_RE_VERSION
            );
        }
    }

    public static function render_dashboard_page() {
        ?>
        <div class="wrap nexa-admin-wrap">
            <h1>Nexa Agency Dashboard</h1>
            <p>We will add analytics and summaries here later.</p>
        </div>
        <?php
    }

    // Implemented below:
    public static function render_properties_list_page() {
        $api_url   = rtrim( Nexa_RE_Settings::API_BASE_URL, '/' );
        $api_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );

        if ( empty( $api_token ) ) {
            echo '<div class="notice notice-error"><p>Please set your API token in Nexa Real Estate settings.</p></div>';
            return;
        }

        // Handle delete action
        if ( isset( $_GET['action'], $_GET['property_id'] ) && $_GET['action'] === 'delete' && check_admin_referer( 'nexa_delete_property_' . $_GET['property_id'] ) ) {
            $property_id = (int) $_GET['property_id'];

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
                echo '<div class="notice notice-error"><p>Failed to delete property: ' . esc_html( $response->get_error_message() ) . '</p></div>';
            } else {
                echo '<div class="notice notice-success is-dismissible"><p>Property deleted.</p></div>';
            }
        }

        // Fetch properties
        $endpoint = $api_url . '/properties';

        $response = wp_remote_get( $endpoint, [
            'headers' => [
                'X-AGENCY-TOKEN' => $api_token,
                'Accept'         => 'application/json',
            ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            echo '<div class="notice notice-error"><p>Error connecting to Nexa API: ' . esc_html( $response->get_error_message() ) . '</p></div>';
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            echo '<div class="notice notice-error"><p>Nexa API returned error: ' . esc_html( $code ) . '</p></div>';
            return;
        }

        $data = json_decode( $body, true );
        $properties = $data['data'] ?? $data;
        if ( ! is_array( $properties ) ) {
            $properties = [];
        }

        ?>
        <div class="wrap nexa-admin-wrap">
            <h1 class="wp-heading-inline">Properties</h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=nexa-properties-add' ) ); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>City</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Created</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $properties ) ) : ?>
                        <tr>
                            <td colspan="7">No properties found.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $properties as $property ) : ?>
                            <tr>
                                <td><?php echo intval( $property['id'] ); ?></td>
                                <td><?php echo esc_html( $property['title'] ?? '' ); ?></td>
                                <td><?php echo esc_html( $property['city'] ?? '' ); ?></td>
                                <td><?php echo esc_html( ucfirst( $property['category'] ?? '' ) ); ?></td>
                                <td><?php echo isset( $property['price'] ) ? esc_html( number_format_i18n( $property['price'] ) ) : '—'; ?></td>
                                <td>
                                    <?php echo ! empty( $property['created_at'] )
                                        ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $property['created_at'] ) ) )
                                        : '—'; ?>
                                </td>
                                <td>
                                    <?php
                                    $edit_url = add_query_arg(
                                        [
                                            'page'        => 'nexa-properties-add',
                                            'property_id' => intval( $property['id'] ),
                                        ],
                                        admin_url( 'admin.php' )
                                    );

                                    $delete_url = wp_nonce_url(
                                        add_query_arg(
                                            [
                                                'page'        => 'nexa-properties-list',
                                                'action'      => 'delete',
                                                'property_id' => intval( $property['id'] ),
                                            ],
                                            admin_url( 'admin.php' )
                                        ),
                                        'nexa_delete_property_' . intval( $property['id'] )
                                    );
                                    ?>
                                    <a href="<?php echo esc_url( $edit_url ); ?>">Edit</a> |
                                    <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('Delete this property?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function render_property_form_page() {
        $api_url   = rtrim( Nexa_RE_Settings::API_BASE_URL, '/' );
        $api_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );

        if ( empty( $api_token ) ) {
            echo '<div class="notice notice-error"><p>Please set your API token in Nexa Real Estate settings.</p></div>';
            return;
        }

        $is_edit     = false;
        $property_id = isset( $_GET['property_id'] ) ? intval( $_GET['property_id'] ) : 0;
        $property    = null;

        // Editing: fetch property
        if ( $property_id ) {
            $is_edit  = true;
            $endpoint = $api_url . '/properties/' . $property_id;

            $response = wp_remote_get( $endpoint, [
                'headers' => [
                    'X-AGENCY-TOKEN' => $api_token,
                    'Accept'         => 'application/json',
                ],
                'timeout' => 15,
            ] );

            if ( is_wp_error( $response ) ) {
                echo '<div class="notice notice-error"><p>Error loading property: ' . esc_html( $response->get_error_message() ) . '</p></div>';
                return;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );

            if ( $code === 200 ) {
                $property = json_decode( $body, true );
            } else {
                echo '<div class="notice notice-error"><p>Could not load property (HTTP ' . esc_html( $code ) . ').</p></div>';
                return;
            }
        }

        // Handle form submit
        if ( isset( $_POST['nexa_property_nonce'] ) && wp_verify_nonce( $_POST['nexa_property_nonce'], 'nexa_save_property' ) ) {
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
                'area'          => (int) ( $_POST['area'] ?? 0 ),
                'address'       => sanitize_text_field( $_POST['address'] ?? '' ),
                'latitude'      => $latitude,
                'longitude'     => $longitude,
                'price'         => $_POST['price'] !== '' ? (int) $_POST['price'] : null,
                'bedrooms'      => $_POST['bedrooms'] !== '' ? (int) $_POST['bedrooms'] : null,
                'bathrooms'     => $_POST['bathrooms'] !== '' ? (int) $_POST['bathrooms'] : null,
            ];

            // images[]
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

            // floor_plans[]
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
                $endpoint = $api_url . '/properties/' . $property_id;
                $args['method'] = 'PUT';
                $response = wp_remote_request( $endpoint, $args );
            } else {
                $endpoint = $api_url . '/properties';
                $args['method'] = 'POST';
                $response = wp_remote_post( $endpoint, $args );
            }

            if ( is_wp_error( $response ) ) {
                echo '<div class="notice notice-error"><p>Error saving property: ' . esc_html( $response->get_error_message() ) . '</p></div>';
            } else {
                $code = wp_remote_retrieve_response_code( $response );
                if ( in_array( $code, [200, 201], true ) ) {
                    echo '<div class="notice notice-success is-dismissible"><p>Property saved successfully.</p></div>';
                    // Optionally redirect back to the list
                    echo '<script>setTimeout(function(){ window.location.href = "' . esc_js( admin_url( 'admin.php?page=nexa-properties-list' ) ) . '"; }, 800);</script>';
                } else {
                    $body = wp_remote_retrieve_body( $response );
                    echo '<div class="notice notice-error"><p>API error (' . esc_html( $code ) . '): ' . esc_html( $body ) . '</p></div>';
                }
            }
        }

        // Defaults for form fields
        $title       = $property['title'] ?? '';
        $description = $property['description'] ?? '';
        $category    = $property['category'] ?? 'rent';
        $city        = $property['city'] ?? '';
        $property_type = $property['property_type'] ?? '';
        $area        = $property['area'] ?? '';
        $address     = $property['address'] ?? '';
        $latitude    = $property['latitude'] ?? '';
        $longitude   = $property['longitude'] ?? '';
        $price       = $property['price'] ?? '';
        $bedrooms    = $property['bedrooms'] ?? '';
        $bathrooms   = $property['bathrooms'] ?? '';
        $images      = [];
        $floor_plans = [];

        if ( ! empty( $property['images'] ) && is_array( $property['images'] ) ) {
            foreach ( $property['images'] as $img ) {
                if ( ! empty( $img['url'] ) ) {
                    $images[] = $img['url'];
                }
            }
        }

        if ( ! empty( $property['floor_plans'] ) && is_array( $property['floor_plans'] ) ) {
            $floor_plans = $property['floor_plans'];
        }

        // Fetch agency parameters for dropdowns
        $api_client        = new Nexa_RE_Api_Client();
        $params_result     = $api_client->list_agency_parameters();
        $city_options      = [];
        $property_type_options = [];

        if ( $params_result['ok'] && ! empty( $params_result['data']['parameters'] ) ) {
            $parameters = $params_result['data']['parameters'];
            if ( ! empty( $parameters['city'] ) && is_array( $parameters['city'] ) ) {
                $city_options = array_column( $parameters['city'], 'value' );
            }
            if ( ! empty( $parameters['property_type'] ) && is_array( $parameters['property_type'] ) ) {
                $property_type_options = array_column( $parameters['property_type'], 'value' );
            }
        }

        // Enqueue map assets for admin
        nexa_re_enqueue_map_assets();

        ?>
        <div class="wrap nexa-admin-wrap">
            <h1 class="wp-heading-inline">
                <?php echo $is_edit ? 'Edit Property' : 'Add New Property'; ?>
            </h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=nexa-properties-list' ) ); ?>" class="page-title-action">Back to list</a>
            <hr class="wp-header-end">

            <form method="post">
                <?php wp_nonce_field( 'nexa_save_property', 'nexa_property_nonce' ); ?>

                <div style="display:flex; gap:24px; align-items:flex-start; max-width: 1100px;">
                    <div style="flex:2;">
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="title">Title</label></th>
                                    <td>
                                        <input name="title" type="text" id="title" value="<?php echo esc_attr( $title ); ?>" class="regular-text" required>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="description">Description</label></th>
                                    <td>
                                        <textarea name="description" id="description" rows="5" class="large-text"><?php echo esc_textarea( $description ); ?></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="category">Category</label></th>
                                    <td>
                                        <select name="category" id="category">
                                            <option value="rent" <?php selected( $category, 'rent' ); ?>>Rent</option>
                                            <option value="buy" <?php selected( $category, 'buy' ); ?>>Buy</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="city">City</label></th>
                                    <td>
                                        <?php if ( ! empty( $city_options ) ) : ?>
                                            <select name="city" id="city" required>
                                                <option value="">— Select City —</option>
                                                <?php foreach ( $city_options as $city_option ) : ?>
                                                    <option value="<?php echo esc_attr( $city_option ); ?>" <?php selected( $city, $city_option ); ?>><?php echo esc_html( $city_option ); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else : ?>
                                            <input name="city" type="text" id="city" value="<?php echo esc_attr( $city ); ?>" class="regular-text" required>
                                            <p class="description">
                                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=nexa-customization' ) ); ?>">Configure city options</a> for a dropdown list.
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="property_type">Property Type</label></th>
                                    <td>
                                        <?php if ( ! empty( $property_type_options ) ) : ?>
                                            <select name="property_type" id="property_type">
                                                <option value="">— Select Property Type —</option>
                                                <?php foreach ( $property_type_options as $type_option ) : ?>
                                                    <option value="<?php echo esc_attr( $type_option ); ?>" <?php selected( $property_type, $type_option ); ?>><?php echo esc_html( $type_option ); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else : ?>
                                            <input name="property_type" type="text" id="property_type" value="<?php echo esc_attr( $property_type ); ?>" class="regular-text">
                                            <p class="description">
                                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=nexa-customization' ) ); ?>">Configure property type options</a> for a dropdown list.
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="area">Area (sqm)</label></th>
                                    <td>
                                        <input name="area" type="number" id="area" value="<?php echo esc_attr( $area ); ?>" class="small-text">
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="address">Address</label></th>
                                    <td>
                                        <input name="address" type="text" id="address" value="<?php echo esc_attr( $address ); ?>" class="regular-text">
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label>Location</label></th>
                                    <td>
                                        <div id="nexa-admin-map-picker" class="nexa-map-container nexa-map-picker" style="margin-bottom: 12px;"></div>
                                        <p class="description" style="margin-bottom: 10px;">Click on the map to set the property location or enter coordinates manually below.</p>
                                        <div style="display: flex; gap: 12px;">
                                            <div style="flex: 1;">
                                                <label for="latitude" style="display: block; margin-bottom: 4px; font-weight: 500;">Latitude</label>
                                                <input name="latitude" type="number" step="any" id="latitude" value="<?php echo esc_attr( $latitude ); ?>" class="regular-text" placeholder="-90 to 90" min="-90" max="90">
                                            </div>
                                            <div style="flex: 1;">
                                                <label for="longitude" style="display: block; margin-bottom: 4px; font-weight: 500;">Longitude</label>
                                                <input name="longitude" type="number" step="any" id="longitude" value="<?php echo esc_attr( $longitude ); ?>" class="regular-text" placeholder="-180 to 180" min="-180" max="180">
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="price">Price</label></th>
                                    <td>
                                        <input name="price" type="number" id="price" value="<?php echo esc_attr( $price ); ?>" class="small-text">
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="bedrooms">Bedrooms</label></th>
                                    <td>
                                        <input name="bedrooms" type="number" id="bedrooms" value="<?php echo esc_attr( $bedrooms ); ?>" class="small-text">
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="bathrooms">Bathrooms</label></th>
                                    <td>
                                        <input name="bathrooms" type="number" id="bathrooms" value="<?php echo esc_attr( $bathrooms ); ?>" class="small-text">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="flex:1;">
                        <h2>Gallery</h2>
                        <p>Select one or more images from the media library.</p>
                        <button type="button" class="button" id="nexa-select-images">Select Images</button>

                        <div id="nexa-images-preview" style="margin-top:10px; display:flex; flex-wrap:wrap; gap:6px;">
                            <?php foreach ( $images as $url ) : ?>
                                <div class="nexa-image-thumb" style="position:relative;">
                                    <img src="<?php echo esc_url( $url ); ?>" style="width:70px; height:70px; object-fit:cover; border-radius:4px;">
                                    <input type="hidden" name="images[]" value="<?php echo esc_attr( $url ); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <h2 style="margin-top:24px;">Floor Plans (PDF)</h2>
                        <p>Upload PDF floor plans for this property.</p>
                        <button type="button" class="button" id="nexa-add-floor-plan">+ Add Floor Plan</button>

                        <div id="nexa-floor-plans-container" style="margin-top:10px;">
                            <?php foreach ( $floor_plans as $index => $plan ) : ?>
                                <div class="nexa-floor-plan-row" style="display:flex; gap:10px; align-items:center; margin-bottom:8px; padding:10px; background:#f9f9f9; border-radius:4px;">
                                    <input type="hidden" name="floor_plans[<?php echo $index; ?>][file_url]" class="floor-plan-url" value="<?php echo esc_attr( $plan['file_url'] ?? '' ); ?>">
                                    <input type="hidden" name="floor_plans[<?php echo $index; ?>][order]" value="<?php echo intval( $plan['order'] ?? $index ); ?>">
                                    <input type="text" name="floor_plans[<?php echo $index; ?>][label]" placeholder="Label (optional)" value="<?php echo esc_attr( $plan['label'] ?? '' ); ?>" class="regular-text" style="flex:1;">
                                    <span class="floor-plan-filename" style="flex:1; font-size:13px; color:#666;">
                                        <?php 
                                        $filename = ! empty( $plan['file_url'] ) ? basename( $plan['file_url'] ) : 'No file selected';
                                        echo '📄 ' . esc_html( $filename ); 
                                        ?>
                                    </span>
                                    <button type="button" class="button nexa-select-floor-plan">Select PDF</button>
                                    <button type="button" class="button nexa-remove-floor-plan" style="color:#a00;">×</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php submit_button( $is_edit ? 'Save Changes' : 'Create Property' ); ?>
            </form>
        </div>

        <script>
            (function($){
                $('#nexa-select-images').on('click', function(e) {
                    e.preventDefault();

                    var frame = wp.media({
                        title: 'Select Property Images',
                        button: { text: 'Use these images' },
                        multiple: true
                    });

                    frame.on('select', function() {
                        var selection = frame.state().get('selection');
                        var container = $('#nexa-images-preview');
                        container.empty();

                        selection.each(function(attachment) {
                            attachment = attachment.toJSON();
                            if (!attachment.url) return;

                            var thumb = $('<div class="nexa-image-thumb" style="position:relative;"></div>');
                            thumb.append('<img src="'+attachment.url+'" style="width:70px; height:70px; object-fit:cover; border-radius:4px;">');
                            thumb.append('<input type="hidden" name="images[]" value="'+attachment.url+'">');
                            container.append(thumb);
                        });
                    });

                    frame.open();
                });

                // Floor plans counter
                var floorPlanCounter = $('#nexa-floor-plans-container .nexa-floor-plan-row').length;

                function createFloorPlanRow(fileUrl, label, order) {
                    fileUrl = fileUrl || '';
                    label = label || '';
                    order = order !== undefined ? order : floorPlanCounter;
                    
                    var $row = $('<div class="nexa-floor-plan-row" style="display:flex; gap:10px; align-items:center; margin-bottom:8px; padding:10px; background:#f9f9f9; border-radius:4px;"></div>');
                    
                    var $fileUrlInput = $('<input type="hidden" name="floor_plans['+floorPlanCounter+'][file_url]" class="floor-plan-url">');
                    $fileUrlInput.val(fileUrl);
                    $row.append($fileUrlInput);
                    
                    var $orderInput = $('<input type="hidden" name="floor_plans['+floorPlanCounter+'][order]">');
                    $orderInput.val(order);
                    $row.append($orderInput);
                    
                    var $labelInput = $('<input type="text" name="floor_plans['+floorPlanCounter+'][label]" placeholder="Label (optional)" class="regular-text" style="flex:1;">');
                    $labelInput.val(label);
                    $row.append($labelInput);
                    
                    var filename = fileUrl ? fileUrl.split('/').pop() : 'No file selected';
                    var $filenameSpan = $('<span class="floor-plan-filename" style="flex:1; font-size:13px; color:#666;"></span>');
                    $filenameSpan.text('📄 ' + filename);
                    $row.append($filenameSpan);
                    
                    $row.append('<button type="button" class="button nexa-select-floor-plan">Select PDF</button>');
                    $row.append('<button type="button" class="button nexa-remove-floor-plan" style="color:#a00;">×</button>');
                    
                    floorPlanCounter++;
                    return $row;
                }

                // Add new floor plan row
                $('#nexa-add-floor-plan').on('click', function(e) {
                    e.preventDefault();
                    var $row = createFloorPlanRow('', '', floorPlanCounter);
                    $('#nexa-floor-plans-container').append($row);
                });

                // Remove floor plan row
                $('#nexa-floor-plans-container').on('click', '.nexa-remove-floor-plan', function(e) {
                    e.preventDefault();
                    $(this).closest('.nexa-floor-plan-row').remove();
                });

                // Select PDF for floor plan
                $('#nexa-floor-plans-container').on('click', '.nexa-select-floor-plan', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var $row = $btn.closest('.nexa-floor-plan-row');
                    
                    var frame = wp.media({
                        title: 'Select Floor Plan PDF',
                        button: { text: 'Use this PDF' },
                        multiple: false,
                        library: {
                            type: 'application/pdf'
                        }
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        if (!attachment.url) return;
                        
                        // Update the hidden input
                        $row.find('.floor-plan-url').val(attachment.url);
                        
                        // Update the display
                        var filename = attachment.url.split('/').pop();
                        $row.find('.floor-plan-filename').text('📄 ' + filename);
                    });

                    frame.open();
                });

                // Leaflet Map Picker
                if (typeof L !== 'undefined' && $('#nexa-admin-map-picker').length) {
                    var initialLat = parseFloat($('#latitude').val()) || 33.8886;
                    var initialLng = parseFloat($('#longitude').val()) || 35.4955;
                    var hasInitialCoords = $('#latitude').val() !== '' && $('#longitude').val() !== '';
                    var defaultZoom = hasInitialCoords ? 14 : 8;

                    var map = L.map('nexa-admin-map-picker').setView([initialLat, initialLng], defaultZoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(map);

                    var marker = null;

                    function updateMarker(lat, lng) {
                        if (marker) {
                            marker.setLatLng([lat, lng]);
                        } else {
                            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                            marker.on('dragend', function(e) {
                                var pos = marker.getLatLng();
                                $('#latitude').val(pos.lat.toFixed(7));
                                $('#longitude').val(pos.lng.toFixed(7));
                            });
                        }
                    }

                    if (hasInitialCoords) {
                        updateMarker(initialLat, initialLng);
                    }

                    map.on('click', function(e) {
                        var lat = e.latlng.lat;
                        var lng = e.latlng.lng;
                        $('#latitude').val(lat.toFixed(7));
                        $('#longitude').val(lng.toFixed(7));
                        updateMarker(lat, lng);
                    });

                    // Update marker when inputs change
                    $('#latitude, #longitude').on('change', function() {
                        var lat = parseFloat($('#latitude').val());
                        var lng = parseFloat($('#longitude').val());
                        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                            updateMarker(lat, lng);
                            map.setView([lat, lng], 14);
                        }
                    });

                    // Fix map rendering when in a hidden/tab context
                    setTimeout(function() {
                        map.invalidateSize();
                    }, 100);
                }
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Render the Agency Customization page for managing Cities and Property Types.
     */
    public static function render_customization_page() {
        $api_url   = rtrim( Nexa_RE_Settings::API_BASE_URL, '/' );
        $api_token = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );

        if ( empty( $api_token ) ) {
            echo '<div class="notice notice-error"><p>Please set your API token in <a href="' . esc_url( admin_url( 'options-general.php?page=nexa-real-estate' ) ) . '">Nexa Real Estate settings</a>.</p></div>';
            return;
        }

        // Handle form submissions
        $messages = [];

        // Handle bulk save for cities
        if ( isset( $_POST['nexa_save_cities'] ) && check_admin_referer( 'nexa_save_customization' ) ) {
            $cities = [];
            if ( ! empty( $_POST['nexa_cities'] ) && is_array( $_POST['nexa_cities'] ) ) {
                foreach ( $_POST['nexa_cities'] as $city ) {
                    $city = sanitize_text_field( $city );
                    if ( ! empty( $city ) ) {
                        $cities[] = $city;
                    }
                }
            }

            $api    = new Nexa_RE_Api_Client();
            $result = $api->bulk_update_agency_parameters( 'city', $cities );

            if ( $result['ok'] ) {
                $messages[] = [ 'type' => 'success', 'text' => 'Cities saved successfully.' ];
            } else {
                $messages[] = [ 'type' => 'error', 'text' => 'Error saving cities: ' . esc_html( $result['error'] ?: 'API error ' . $result['code'] ) ];
            }
        }

        // Handle bulk save for property types
        if ( isset( $_POST['nexa_save_property_types'] ) && check_admin_referer( 'nexa_save_customization' ) ) {
            $property_types = [];
            if ( ! empty( $_POST['nexa_property_types'] ) && is_array( $_POST['nexa_property_types'] ) ) {
                foreach ( $_POST['nexa_property_types'] as $type ) {
                    $type = sanitize_text_field( $type );
                    if ( ! empty( $type ) ) {
                        $property_types[] = $type;
                    }
                }
            }

            $api    = new Nexa_RE_Api_Client();
            $result = $api->bulk_update_agency_parameters( 'property_type', $property_types );

            if ( $result['ok'] ) {
                $messages[] = [ 'type' => 'success', 'text' => 'Property types saved successfully.' ];
            } else {
                $messages[] = [ 'type' => 'error', 'text' => 'Error saving property types: ' . esc_html( $result['error'] ?: 'API error ' . $result['code'] ) ];
            }
        }

        // Fetch current parameters
        $api    = new Nexa_RE_Api_Client();
        $result = $api->list_agency_parameters();

        $cities         = [];
        $property_types = [];

        if ( $result['ok'] && ! empty( $result['data']['parameters'] ) ) {
            $parameters = $result['data']['parameters'];
            if ( ! empty( $parameters['city'] ) && is_array( $parameters['city'] ) ) {
                $cities = array_column( $parameters['city'], 'value' );
            }
            if ( ! empty( $parameters['property_type'] ) && is_array( $parameters['property_type'] ) ) {
                $property_types = array_column( $parameters['property_type'], 'value' );
            }
        }

        ?>
        <div class="wrap nexa-admin-wrap nexa-customization-wrap">
            <h1>Agency Customization</h1>
            <p class="nexa-customization-subtitle">Manage custom dropdown options for Cities and Property Types. These values will appear as options when adding or editing properties.</p>

            <?php foreach ( $messages as $msg ) : ?>
                <div class="notice notice-<?php echo esc_attr( $msg['type'] ); ?> is-dismissible">
                    <p><?php echo esc_html( $msg['text'] ); ?></p>
                </div>
            <?php endforeach; ?>

            <div class="nexa-customization-sections">
                <!-- Cities Section -->
                <div class="nexa-customization-section">
                    <form method="post">
                        <?php wp_nonce_field( 'nexa_save_customization' ); ?>
                        <div class="nexa-section-header">
                            <h2>Cities</h2>
                            <p class="description">Add the cities where your agency operates. These will be available as dropdown options in the property form.</p>
                        </div>

                        <div class="nexa-values-list" id="nexa-cities-list">
                            <?php if ( empty( $cities ) ) : ?>
                                <p class="nexa-empty-message">No cities added yet. Click "Add City" to get started.</p>
                            <?php else : ?>
                                <?php foreach ( $cities as $index => $city ) : ?>
                                    <div class="nexa-value-row">
                                        <span class="nexa-drag-handle dashicons dashicons-menu"></span>
                                        <input type="text" name="nexa_cities[]" value="<?php echo esc_attr( $city ); ?>" class="regular-text" placeholder="Enter city name">
                                        <button type="button" class="button nexa-remove-value" title="Remove"><span class="dashicons dashicons-trash"></span></button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="nexa-section-actions">
                            <button type="button" class="button nexa-add-value" data-target="nexa-cities-list" data-name="nexa_cities[]" data-placeholder="Enter city name">
                                <span class="dashicons dashicons-plus-alt2"></span> Add City
                            </button>
                            <button type="submit" name="nexa_save_cities" class="button button-primary">
                                <span class="dashicons dashicons-saved"></span> Save Cities
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Property Types Section -->
                <div class="nexa-customization-section">
                    <form method="post">
                        <?php wp_nonce_field( 'nexa_save_customization' ); ?>
                        <div class="nexa-section-header">
                            <h2>Property Types</h2>
                            <p class="description">Define the types of properties your agency handles (e.g., Apartment, Villa, Studio, Office).</p>
                        </div>

                        <div class="nexa-values-list" id="nexa-property-types-list">
                            <?php if ( empty( $property_types ) ) : ?>
                                <p class="nexa-empty-message">No property types added yet. Click "Add Property Type" to get started.</p>
                            <?php else : ?>
                                <?php foreach ( $property_types as $index => $type ) : ?>
                                    <div class="nexa-value-row">
                                        <span class="nexa-drag-handle dashicons dashicons-menu"></span>
                                        <input type="text" name="nexa_property_types[]" value="<?php echo esc_attr( $type ); ?>" class="regular-text" placeholder="Enter property type">
                                        <button type="button" class="button nexa-remove-value" title="Remove"><span class="dashicons dashicons-trash"></span></button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="nexa-section-actions">
                            <button type="button" class="button nexa-add-value" data-target="nexa-property-types-list" data-name="nexa_property_types[]" data-placeholder="Enter property type">
                                <span class="dashicons dashicons-plus-alt2"></span> Add Property Type
                            </button>
                            <button type="submit" name="nexa_save_property_types" class="button button-primary">
                                <span class="dashicons dashicons-saved"></span> Save Property Types
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        (function($){
            $(function(){
                // Add new value row
                $('.nexa-add-value').on('click', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var targetId = $btn.data('target');
                    var inputName = $btn.data('name');
                    var placeholder = $btn.data('placeholder');
                    var $list = $('#' + targetId);

                    // Remove empty message if present
                    $list.find('.nexa-empty-message').remove();

                    var $row = $('<div class="nexa-value-row"></div>');
                    $row.append('<span class="nexa-drag-handle dashicons dashicons-menu"></span>');
                    $row.append('<input type="text" name="' + inputName + '" value="" class="regular-text" placeholder="' + placeholder + '">');
                    $row.append('<button type="button" class="button nexa-remove-value" title="Remove"><span class="dashicons dashicons-trash"></span></button>');

                    $list.append($row);
                    $row.find('input').focus();
                });

                // Remove value row
                $(document).on('click', '.nexa-remove-value', function(e) {
                    e.preventDefault();
                    var $row = $(this).closest('.nexa-value-row');
                    var $list = $row.parent();
                    $row.remove();

                    // Show empty message if no rows left
                    if ($list.find('.nexa-value-row').length === 0) {
                        $list.append('<p class="nexa-empty-message">No items added yet.</p>');
                    }
                });

                // Make lists sortable (using jQuery UI if available)
                if ($.fn.sortable) {
                    $('.nexa-values-list').sortable({
                        handle: '.nexa-drag-handle',
                        items: '.nexa-value-row',
                        placeholder: 'nexa-sortable-placeholder',
                        cursor: 'grabbing'
                    });
                }
            });
        })(jQuery);
        </script>
        <?php
    }

}
