<?php
// expects: $properties
?>
<section class="nexa-properties-section">
    <div class="nexa-properties-header">
        <div>
            <h2 class="nexa-section-title">Your Properties</h2>
            <p class="nexa-section-subtitle">Manage the properties published on your website.</p>
        </div>
        <button type="button" class="nexa-btn" id="nexa-add-property-btn">
            + Add New Property
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
                                No properties found yet. Create your first property to get started.
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

                            $property_id = intval( $p['id'] );

                            $delete_url = wp_nonce_url(
                                add_query_arg(
                                    [
                                        'nexa_action' => 'delete_property',
                                        'property_id' => $property_id,
                                    ],
                                    get_permalink()
                                ),
                                'nexa_delete_property_' . $property_id
                            );

                            $view_url = $property_id ? home_url( '/properties/' . $property_id . '/' ) : home_url();
                            ?>
                            <tr data-property='{"id":<?php echo intval( $p['id'] ); ?>}'>
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
                                    <a class="nexa-link-muted" href="<?php echo esc_url( $view_url ); ?>" target="_blank" rel="noopener noreferrer">
                                        View
                                    </a>
                                    <span aria-hidden="true">|</span>
                                    <a class="nexa-link-muted nexa-edit-property" href="#" data-property='<?php echo esc_attr( wp_json_encode( $p ) ); ?>'>
                                        Edit
                                    </a>
                                    <span aria-hidden="true">|</span>
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

<?php
// Modal: create / edit property
?>
<div class="nexa-modal-backdrop" id="nexa-property-modal" style="display:none;">
    <div class="nexa-modal">
        <div class="nexa-modal-header">
            <div>
                <h2 class="nexa-modal-title">Create Property</h2>
                <p class="nexa-modal-subtitle">Fill in the details to create a new property for your agency.</p>
            </div>
            <button type="button" class="nexa-modal-close" id="nexa-property-modal-close">&times;</button>
        </div>

        <div class="nexa-modal-tabs">
            <button type="button" class="nexa-tab-btn nexa-tab-active" data-tab="basic">
                Basic Info
            </button>
            <button type="button" class="nexa-tab-btn" data-tab="location">
                Location
            </button>
            <button type="button" class="nexa-tab-btn" data-tab="details">
                Details
            </button>
            <button type="button" class="nexa-tab-btn nexa-tab-disabled" data-tab="media" disabled>
                Media & Docs (soon)
            </button>
        </div>

        <div class="nexa-modal-body">
            <form method="post" id="nexa-property-form">
                <?php wp_nonce_field( 'nexa_save_property_front', 'nexa_property_nonce' ); ?>
                <input type="hidden" name="nexa_action" value="save_property">
                <input type="hidden" name="property_id" id="nexa-property-id" value="">

                <!-- Tab: Basic Info -->
                <div class="nexa-tab-panel" data-tab-panel="basic">
                    <div class="nexa-form-grid">
                        <div class="nexa-form-col">
                            <div class="nexa-form-row">
                                <label class="nexa-form-label" for="nexa-title">Title</label>
                                <input class="nexa-input" type="text" id="nexa-title" name="title" required>
                            </div>

                            <div class="nexa-form-row">
                                <label class="nexa-form-label" for="nexa-description">Description</label>
                                <textarea class="nexa-textarea" id="nexa-description" name="description" rows="4"></textarea>
                            </div>

                            <div class="nexa-form-row nexa-form-row-inline">
                                <div class="nexa-form-field">
                                    <label class="nexa-form-label" for="nexa-category">Category</label>
                                    <select class="nexa-input" id="nexa-category" name="category">
                                        <option value="rent">Rent</option>
                                        <option value="buy">Buy</option>
                                    </select>
                                </div>
                                <div class="nexa-form-field">
                                    <label class="nexa-form-label" for="nexa-city">City</label>
                                    <?php if ( ! empty( $city_options ) ) : ?>
                                        <select class="nexa-input" id="nexa-city" name="city" required>
                                            <option value="">— Select City —</option>
                                            <?php foreach ( $city_options as $city_opt ) : ?>
                                                <option value="<?php echo esc_attr( $city_opt ); ?>"><?php echo esc_html( $city_opt ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else : ?>
                                        <input class="nexa-input" type="text" id="nexa-city" name="city" required>
                                        <p class="nexa-form-hint" style="font-size:11px; color:#6b7280; margin-top:4px;">
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=nexa-customization' ) ); ?>">Configure city options</a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="nexa-form-row nexa-form-row-inline">
                                <div class="nexa-form-field">
                                    <label class="nexa-form-label" for="nexa-property-type">Property Type</label>
                                    <?php if ( ! empty( $property_type_options ) ) : ?>
                                        <select class="nexa-input" id="nexa-property-type" name="property_type">
                                            <option value="">— Select Property Type —</option>
                                            <?php foreach ( $property_type_options as $type_opt ) : ?>
                                                <option value="<?php echo esc_attr( $type_opt ); ?>"><?php echo esc_html( $type_opt ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else : ?>
                                        <input class="nexa-input" type="text" id="nexa-property-type" name="property_type" placeholder="Apartment, Villa, Studio...">
                                        <p class="nexa-form-hint" style="font-size:11px; color:#6b7280; margin-top:4px;">
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=nexa-customization' ) ); ?>">Configure property types</a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="nexa-form-field">
                                    <label class="nexa-form-label" for="nexa-area">Area (sqm)</label>
                                    <input class="nexa-input" type="number" id="nexa-area" name="area" min="0">
                                </div>
                            </div>

                            <div class="nexa-form-row">
                                <label class="nexa-form-label" for="nexa-address">Address</label>
                                <input class="nexa-input" type="text" id="nexa-address" name="address">
                            </div>

                            <div class="nexa-form-row nexa-form-row-inline">
                                <div class="nexa-form-field">
                                    <label class="nexa-form-label" for="nexa-price">Price</label>
                                    <input class="nexa-input" type="number" id="nexa-price" name="price" min="0">
                                </div>
                                <div class="nexa-form-field">
                                    <label class="nexa-form-label" for="nexa-bedrooms">Bedrooms</label>
                                    <input class="nexa-input" type="number" id="nexa-bedrooms" name="bedrooms" min="0">
                                </div>
                                <div class="nexa-form-field">
                                    <label class="nexa-form-label" for="nexa-bathrooms">Bathrooms</label>
                                    <input class="nexa-input" type="number" id="nexa-bathrooms" name="bathrooms" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="nexa-form-col nexa-form-col-side">
                            <div class="nexa-form-row">
                                <label class="nexa-form-label">Gallery</label>
                                <p class="nexa-section-subtitle" style="margin-bottom:8px;">
                                    Select one or more images from the media library.
                                </p>
                                <button type="button" class="nexa-btn nexa-btn-secondary" id="nexa-select-images-front" style="margin-bottom:10px;">
                                    Select Images
                                </button>

                                <div id="nexa-images-preview-front" class="nexa-images-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Location -->
                <div class="nexa-tab-panel" data-tab-panel="location" style="display:none;">
                    <div class="nexa-location-tab-content">
                        <div class="nexa-form-row" style="margin-bottom: 12px;">
                            <label class="nexa-form-label">Pin Location on Map</label>
                            <p class="nexa-section-subtitle" style="margin-bottom: 8px;">
                                Click on the map to set the property's location, or drag the marker to adjust. You can also enter coordinates manually.
                            </p>
                        </div>
                        <div id="nexa-front-map-picker" class="nexa-map-container nexa-map-picker" style="margin-bottom: 16px;"></div>
                        <div class="nexa-coords-wrapper">
                            <div class="nexa-coord-field">
                                <label for="nexa-latitude">Latitude</label>
                                <input class="nexa-input" type="number" step="any" id="nexa-latitude" name="latitude" placeholder="-90 to 90" min="-90" max="90">
                                <div class="nexa-coord-error" id="nexa-lat-error" style="display:none;">Latitude must be between -90 and 90</div>
                            </div>
                            <div class="nexa-coord-field">
                                <label for="nexa-longitude">Longitude</label>
                                <input class="nexa-input" type="number" step="any" id="nexa-longitude" name="longitude" placeholder="-180 to 180" min="-180" max="180">
                                <div class="nexa-coord-error" id="nexa-lng-error" style="display:none;">Longitude must be between -180 and 180</div>
                            </div>
                        </div>
                        <div class="nexa-map-hint">
                            <strong>Tip:</strong> Zoom in to accurately position the property marker. The coordinates will update automatically as you move the marker.
                        </div>
                    </div>
                </div>

                <!-- Tab: Details -->
                <div class="nexa-tab-panel" data-tab-panel="details" style="display:none;">
                    <div class="nexa-form-grid">
                        <div class="nexa-form-col">
                            <div class="nexa-form-row">
                                <label class="nexa-form-label">Floor Plans (PDF)</label>
                                <p class="nexa-section-subtitle" style="margin-bottom:8px;">
                                    Upload PDF floor plans for this property. You can add multiple floor plans.
                                </p>
                                <button type="button" class="nexa-btn nexa-btn-secondary" id="nexa-add-floor-plan-btn" style="margin-bottom:10px;">
                                    + Add Floor Plan
                                </button>
                                
                                <div id="nexa-floor-plans-container"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Media (placeholder) -->
                <div class="nexa-tab-panel" data-tab-panel="media" style="display:none;">
                    <p class="nexa-section-subtitle">
                        Advanced media management (floor plans, documents…) will be added here later.
                    </p>
                </div>

                <div class="nexa-modal-footer">
                    <button type="submit" class="nexa-btn">
                        Save Property
                    </button>
                    <button type="button" class="nexa-btn nexa-btn-secondary" id="nexa-cancel-property-btn">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function($){
    $(function(){
        var $modal         = $('#nexa-property-modal');
        var $btnOpen       = $('#nexa-add-property-btn');
        var $btnClose      = $('#nexa-property-modal-close');
        var $btnCancel     = $('#nexa-cancel-property-btn');
        var $imagesPreview = $('#nexa-images-preview-front');
        var $floorPlansContainer = $('#nexa-floor-plans-container');
        var $modalTitle    = $modal.find('.nexa-modal-title');
        var $modalSubtitle = $modal.find('.nexa-modal-subtitle');
        var $submitBtn     = $('#nexa-property-form').find('button[type="submit"]');
        var mediaFrame     = null;
        var floorPlanCounter = 0;
        var frontMap = null;
        var frontMapMarker = null;
        var frontMapInitialized = false;

        // Initialize front map when Location tab is shown
        function initFrontMap() {
            if (frontMapInitialized || typeof L === 'undefined') return;
            
            var mapEl = document.getElementById('nexa-front-map-picker');
            if (!mapEl) return;

            var initialLat = parseFloat($('#nexa-latitude').val()) || 33.8886;
            var initialLng = parseFloat($('#nexa-longitude').val()) || 35.4955;
            var hasInitialCoords = $('#nexa-latitude').val() !== '' && $('#nexa-longitude').val() !== '';
            var defaultZoom = hasInitialCoords ? 14 : 8;

            frontMap = L.map('nexa-front-map-picker').setView([initialLat, initialLng], defaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(frontMap);

            if (hasInitialCoords) {
                updateFrontMapMarker(initialLat, initialLng);
            }

            frontMap.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;
                $('#nexa-latitude').val(lat.toFixed(7));
                $('#nexa-longitude').val(lng.toFixed(7));
                updateFrontMapMarker(lat, lng);
                validateCoordinates();
            });

            frontMapInitialized = true;

            // Force map resize after a short delay
            setTimeout(function() {
                if (frontMap) frontMap.invalidateSize();
            }, 100);
        }

        function updateFrontMapMarker(lat, lng) {
            if (!frontMap) return;
            
            if (frontMapMarker) {
                frontMapMarker.setLatLng([lat, lng]);
            } else {
                frontMapMarker = L.marker([lat, lng], { draggable: true }).addTo(frontMap);
                frontMapMarker.on('dragend', function(e) {
                    var pos = frontMapMarker.getLatLng();
                    $('#nexa-latitude').val(pos.lat.toFixed(7));
                    $('#nexa-longitude').val(pos.lng.toFixed(7));
                    validateCoordinates();
                });
            }
        }

        function validateCoordinates() {
            var lat = parseFloat($('#nexa-latitude').val());
            var lng = parseFloat($('#nexa-longitude').val());
            var latValid = isNaN(lat) || (lat >= -90 && lat <= 90);
            var lngValid = isNaN(lng) || (lng >= -180 && lng <= 180);

            if (!latValid) {
                $('#nexa-lat-error').show();
                $('#nexa-latitude').addClass('nexa-input-error');
            } else {
                $('#nexa-lat-error').hide();
                $('#nexa-latitude').removeClass('nexa-input-error');
            }

            if (!lngValid) {
                $('#nexa-lng-error').show();
                $('#nexa-longitude').addClass('nexa-input-error');
            } else {
                $('#nexa-lng-error').hide();
                $('#nexa-longitude').removeClass('nexa-input-error');
            }

            return latValid && lngValid;
        }

        // Update marker when inputs change
        $('#nexa-latitude, #nexa-longitude').on('change', function() {
            if (!validateCoordinates()) return;
            
            var lat = parseFloat($('#nexa-latitude').val());
            var lng = parseFloat($('#nexa-longitude').val());
            if (!isNaN(lat) && !isNaN(lng) && frontMap) {
                updateFrontMapMarker(lat, lng);
                frontMap.setView([lat, lng], 14);
            }
        });

        function createFloorPlanRow(fileUrl, label, order) {
            fileUrl = fileUrl || '';
            label = label || '';
            order = order !== undefined ? order : floorPlanCounter;
            
            var $row = $('<div class="nexa-floor-plan-row" style="display:flex; gap:10px; align-items:flex-start; margin-bottom:12px; padding:12px; background:#f9fafb; border-radius:8px;"></div>');
            
            var $fileUrlInput = $('<input type="hidden" name="floor_plans['+floorPlanCounter+'][file_url]" class="floor-plan-url">');
            $fileUrlInput.val(fileUrl);
            $row.append($fileUrlInput);
            
            var $orderInput = $('<input type="hidden" name="floor_plans['+floorPlanCounter+'][order]">');
            $orderInput.val(order);
            $row.append($orderInput);
            
            var $labelWrapper = $('<div style="flex:1;"><label class="nexa-form-label" style="font-size:12px;">Label (optional)</label></div>');
            var $labelInputField = $('<input type="text" name="floor_plans['+floorPlanCounter+'][label]" class="nexa-input" placeholder="e.g. Ground Floor" style="font-size:13px;">');
            $labelInputField.val(label);
            $labelWrapper.append($labelInputField);
            $row.append($labelWrapper);
            
            var $fileDisplay = $('<div style="flex:2;"><label class="nexa-form-label" style="font-size:12px;">PDF File</label><div class="floor-plan-file-display" style="display:flex; gap:8px; align-items:center;"></div></div>');
            
            var $displayInner = $fileDisplay.find('.floor-plan-file-display');
            var $filenameSpan = $('<span class="floor-plan-filename" style="font-size:13px;"></span>');
            if (fileUrl) {
                var filename = fileUrl.split('/').pop();
                $filenameSpan.css('color', '#374151').text('📄 ' + filename);
            } else {
                $filenameSpan.css('color', '#9ca3af').text('No file selected');
            }
            $displayInner.append($filenameSpan);
            $displayInner.append('<button type="button" class="nexa-btn nexa-btn-secondary nexa-select-floor-plan-btn" style="font-size:12px; padding:4px 10px;">Select PDF</button>');
            
            $row.append($fileDisplay);
            
            var $removeBtn = $('<button type="button" class="nexa-remove-floor-plan-btn" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; padding:4px; margin-top:20px;" title="Remove">×</button>');
            $row.append($removeBtn);
            
            floorPlanCounter++;
            return $row;
        }

        function resetForm() {
            $('#nexa-property-id').val('');
            $('#nexa-title').val('');
            $('#nexa-description').val('');
            $('#nexa-category').val('rent');
            $('#nexa-city').val('');
            $('#nexa-property-type').val('');
            $('#nexa-area').val('');
            $('#nexa-address').val('');
            $('#nexa-latitude').val('');
            $('#nexa-longitude').val('');
            $('#nexa-price').val('');
            $('#nexa-bedrooms').val('');
            $('#nexa-bathrooms').val('');
            $imagesPreview.empty();
            $floorPlansContainer.empty();
            floorPlanCounter = 0;

            // Reset map marker
            if (frontMapMarker) {
                frontMap.removeLayer(frontMapMarker);
                frontMapMarker = null;
            }
            if (frontMap) {
                frontMap.setView([33.8886, 35.4955], 8);
            }

            // Reset to Basic tab
            $('.nexa-tab-btn').removeClass('nexa-tab-active');
            $('.nexa-tab-btn[data-tab="basic"]').addClass('nexa-tab-active');
            $('.nexa-tab-panel').hide();
            $('.nexa-tab-panel[data-tab-panel="basic"]').show();

            $modalTitle.text('Create Property');
            $modalSubtitle.text('Fill in the details to create a new property for your agency.');
            $submitBtn.text('Save Property');
        }

        function fillFormFromProperty(property) {
            if (!property || typeof property !== 'object') {
                return;
            }

            $('#nexa-property-id').val(property.id || '');
            $('#nexa-title').val(property.title || '');
            $('#nexa-description').val(property.description || '');
            $('#nexa-category').val(property.category || 'rent');
            $('#nexa-city').val(property.city || '');
            $('#nexa-property-type').val(property.property_type || '');
            $('#nexa-area').val(property.area || '');
            $('#nexa-address').val(property.address || '');
            $('#nexa-latitude').val(property.latitude != null ? property.latitude : '');
            $('#nexa-longitude').val(property.longitude != null ? property.longitude : '');
            $('#nexa-price').val(property.price != null ? property.price : '');
            $('#nexa-bedrooms').val(property.bedrooms != null ? property.bedrooms : '');
            $('#nexa-bathrooms').val(property.bathrooms != null ? property.bathrooms : '');

            // Update map marker if location exists
            if (property.latitude && property.longitude && frontMap) {
                var lat = parseFloat(property.latitude);
                var lng = parseFloat(property.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    updateFrontMapMarker(lat, lng);
                    frontMap.setView([lat, lng], 14);
                }
            }

            $imagesPreview.empty();
            if (Array.isArray(property.images)) {
                property.images.forEach(function(img){
                    var url = '';
                    if (typeof img === 'string') {
                        url = img;
                    } else if (img && typeof img === 'object' && img.url) {
                        url = img.url;
                    }

                    if (!url) return;

                    var $thumb = $('<div class="nexa-image-thumb"></div>');
                    $thumb.append('<img src="'+url+'">');
                    $thumb.append('<input type="hidden" name="images[]" value="'+url+'">');
                    $imagesPreview.append($thumb);
                });
            }

            // Populate floor plans
            $floorPlansContainer.empty();
            floorPlanCounter = 0;
            if (Array.isArray(property.floor_plans)) {
                property.floor_plans.forEach(function(plan){
                    var fileUrl = plan.file_url || '';
                    var label = plan.label || '';
                    var order = plan.order !== undefined ? plan.order : floorPlanCounter;
                    var $row = createFloorPlanRow(fileUrl, label, order);
                    $floorPlansContainer.append($row);
                });
            }
        }

        function openModal(property) {
            resetForm();

            if (property) {
                fillFormFromProperty(property);
                $modalTitle.text('Edit Property');
                $modalSubtitle.text('Update the property details and save your changes.');
                $submitBtn.text('Save Changes');
            }

            $modal.fadeIn(160);
        }

        function closeModal() {
            $modal.fadeOut(160);
        }

        $btnOpen.on('click', function(e){
            e.preventDefault();
            openModal();
        });

        $btnClose.on('click', function(e){
            e.preventDefault();
            closeModal();
        });

        $btnCancel.on('click', function(e){
            e.preventDefault();
            closeModal();
        });

        // Close when clicking backdrop
        $modal.on('click', function(e){
            if ( $(e.target).is('#nexa-property-modal') ) {
                closeModal();
            }
        });

        // Tabs
        $('.nexa-tab-btn').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var tab  = $btn.data('tab');

            if ( $btn.hasClass('nexa-tab-disabled') ) {
                return;
            }

            $('.nexa-tab-btn').removeClass('nexa-tab-active');
            $btn.addClass('nexa-tab-active');

            $('.nexa-tab-panel').hide();
            $('.nexa-tab-panel[data-tab-panel="'+tab+'"]').show();

            // Initialize map when Location tab is shown
            if (tab === 'location') {
                setTimeout(function() {
                    initFrontMap();
                    if (frontMap) frontMap.invalidateSize();
                }, 100);
            }
        });

        $('.nexa-edit-property').on('click', function(e){
            e.preventDefault();
            var raw   = $(this).data('property');
            var prop  = raw;

            // If dataset came through as a string, parse it
            if (typeof raw === 'string') {
                try {
                    prop = JSON.parse(raw);
                } catch (err) {
                    prop = null;
                }
            }

            if (!prop) {
                return;
            }

            openModal(prop);
        });

        // Media library
        $('#nexa-select-images-front').on('click', function(e){
            e.preventDefault();

            if ( mediaFrame ) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: 'Select Property Images',
                button: { text: 'Use these images' },
                multiple: true
            });

            mediaFrame.on('select', function(){
                var selection = mediaFrame.state().get('selection');
                $imagesPreview.empty();

                selection.each(function(attachment){
                    attachment = attachment.toJSON();
                    if (!attachment.url) return;

                    var $thumb = $('<div class="nexa-image-thumb"></div>');
                    $thumb.append('<img src="'+attachment.url+'">');
                    $thumb.append('<input type="hidden" name="images[]" value="'+attachment.url+'">');
                    $imagesPreview.append($thumb);
                });
            });

            mediaFrame.open();
        });

        // Add new floor plan row
        $('#nexa-add-floor-plan-btn').on('click', function(e){
            e.preventDefault();
            var $row = createFloorPlanRow('', '', floorPlanCounter);
            $floorPlansContainer.append($row);
        });

        // Remove floor plan row
        $floorPlansContainer.on('click', '.nexa-remove-floor-plan-btn', function(e){
            e.preventDefault();
            $(this).closest('.nexa-floor-plan-row').remove();
        });

        // Select PDF for floor plan
        $floorPlansContainer.on('click', '.nexa-select-floor-plan-btn', function(e){
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

            frame.on('select', function(){
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
    });
})(jQuery);
</script>
