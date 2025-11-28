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
                                    <!-- later we’ll add Edit here -->
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
            <button type="button" class="nexa-tab-btn nexa-tab-disabled" data-tab="details" disabled>
                Details (soon)
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
                                    <input class="nexa-input" type="text" id="nexa-city" name="city" required>
                                </div>
                            </div>

                            <div class="nexa-form-row nexa-form-row-inline">
                                <div class="nexa-form-field">
                                    <label class="nexa-form-label" for="nexa-property-type">Property Type</label>
                                    <input class="nexa-input" type="text" id="nexa-property-type" name="property_type" placeholder="Apartment, Villa, Studio...">
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

                <!-- Tab: Details (placeholder) -->
                <div class="nexa-tab-panel" data-tab-panel="details" style="display:none;">
                    <p class="nexa-section-subtitle">
                        Additional details such as amenities, parking, and furnishing will appear here in a future update.
                    </p>
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
        var $modal      = $('#nexa-property-modal');
        var $btnOpen    = $('#nexa-add-property-btn');
        var $btnClose   = $('#nexa-property-modal-close');
        var $btnCancel  = $('#nexa-cancel-property-btn');
        var $imagesPreview = $('#nexa-images-preview-front');
        var mediaFrame  = null;

        function resetForm() {
            $('#nexa-property-id').val('');
            $('#nexa-title').val('');
            $('#nexa-description').val('');
            $('#nexa-category').val('rent');
            $('#nexa-city').val('');
            $('#nexa-property-type').val('');
            $('#nexa-area').val('');
            $('#nexa-address').val('');
            $('#nexa-price').val('');
            $('#nexa-bedrooms').val('');
            $('#nexa-bathrooms').val('');
            $imagesPreview.empty();

            // Reset to Basic tab
            $('.nexa-tab-btn').removeClass('nexa-tab-active');
            $('.nexa-tab-btn[data-tab="basic"]').addClass('nexa-tab-active');
            $('.nexa-tab-panel').hide();
            $('.nexa-tab-panel[data-tab-panel="basic"]').show();
        }

        function openModal() {
            resetForm();
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

        // Tabs (only Basic is active for now; others are placeholders)
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
    });
})(jQuery);
</script>
