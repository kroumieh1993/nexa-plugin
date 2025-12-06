<?php
/**
 * Single Property Template: Compact
 * 
 * Space-efficient layout optimized for quick viewing.
 *
 * @var array $property Property data array.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$title       = $property['title'] ?? '';
$price       = isset( $property['price'] ) ? number_format_i18n( $property['price'] ) : '';
$city        = $property['city'] ?? '';
$category    = $property['category'] ?? '';
$type        = $property['property_type'] ?? '';
$bedrooms    = $property['bedrooms'] ?? '';
$bathrooms   = $property['bathrooms'] ?? '';
$area        = $property['area'] ?? '';
$address     = $property['address'] ?? '';
$latitude    = $property['latitude'] ?? null;
$longitude   = $property['longitude'] ?? null;
$description = $property['description'] ?? '';
$images      = [];

if ( ! empty( $property['images'] ) && is_array( $property['images'] ) ) {
    foreach ( $property['images'] as $img ) {
        if ( ! empty( $img['url'] ) ) {
            $images[] = esc_url( $img['url'] );
        }
    }
}

$has_location = ! empty( $latitude ) && ! empty( $longitude );
?>

<div class="nexa-compact-single">
    <!-- Compact Header with Image -->
    <div class="nexa-compact-top">
        <div class="nexa-compact-gallery" data-nexa-gallery>
            <?php if ( ! empty( $images ) ) : ?>
                <?php foreach ( $images as $index => $img ) : ?>
                    <img class="nexa-gallery-slide <?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-slide="<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                <?php endforeach; ?>
                <?php if ( count( $images ) > 1 ) : ?>
                    <button type="button" class="nexa-compact-nav nexa-compact-nav-prev" data-nexa-prev>‹</button>
                    <button type="button" class="nexa-compact-nav nexa-compact-nav-next" data-nexa-next>›</button>
                    <div class="nexa-compact-dots">
                        <?php foreach ( $images as $index => $img ) : ?>
                            <span class="nexa-compact-dot <?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-thumb="<?php echo esc_attr( $index ); ?>"></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="nexa-compact-no-image">No images</div>
            <?php endif; ?>
        </div>

        <div class="nexa-compact-header">
            <div class="nexa-compact-tags">
                <?php if ( $category ) : ?>
                    <span class="nexa-compact-tag"><?php echo esc_html( ucfirst( $category ) ); ?></span>
                <?php endif; ?>
                <?php if ( $type ) : ?>
                    <span class="nexa-compact-tag"><?php echo esc_html( $type ); ?></span>
                <?php endif; ?>
            </div>
            <h1 class="nexa-compact-title"><?php echo esc_html( $title ); ?></h1>
            <?php if ( $city || $address ) : ?>
                <p class="nexa-compact-location">📍 <?php echo esc_html( $address ?: $city ); ?></p>
            <?php endif; ?>
            <?php if ( $price ) : ?>
                <div class="nexa-compact-price"><?php echo esc_html( $price ); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="nexa-compact-stats">
        <?php if ( $bedrooms ) : ?>
            <div class="nexa-compact-stat">
                <span class="nexa-compact-stat-value"><?php echo intval( $bedrooms ); ?></span>
                <span class="nexa-compact-stat-label">Beds</span>
            </div>
        <?php endif; ?>
        <?php if ( $bathrooms ) : ?>
            <div class="nexa-compact-stat">
                <span class="nexa-compact-stat-value"><?php echo intval( $bathrooms ); ?></span>
                <span class="nexa-compact-stat-label">Baths</span>
            </div>
        <?php endif; ?>
        <?php if ( $area ) : ?>
            <div class="nexa-compact-stat">
                <span class="nexa-compact-stat-value"><?php echo esc_html( $area ); ?></span>
                <span class="nexa-compact-stat-label">sqm</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tabs -->
    <div class="nexa-compact-tabs">
        <button type="button" class="nexa-compact-tab is-active" data-tab="description">Description</button>
        <button type="button" class="nexa-compact-tab" data-tab="details">Details</button>
        <?php if ( $has_location ) : ?>
            <button type="button" class="nexa-compact-tab" data-tab="location">Location</button>
        <?php endif; ?>
    </div>

    <div class="nexa-compact-tab-content" id="tab-description">
        <div class="nexa-compact-description">
            <?php echo $description ? wp_kses_post( $description ) : '<p>No description provided.</p>'; ?>
        </div>
    </div>

    <div class="nexa-compact-tab-content" id="tab-details" style="display:none;">
        <table class="nexa-compact-details-table">
            <?php if ( $type ) : ?>
                <tr><td>Type</td><td><?php echo esc_html( ucfirst( $type ) ); ?></td></tr>
            <?php endif; ?>
            <?php if ( $category ) : ?>
                <tr><td>Category</td><td><?php echo esc_html( ucfirst( $category ) ); ?></td></tr>
            <?php endif; ?>
            <?php if ( $city ) : ?>
                <tr><td>City</td><td><?php echo esc_html( $city ); ?></td></tr>
            <?php endif; ?>
            <?php if ( $bedrooms ) : ?>
                <tr><td>Bedrooms</td><td><?php echo intval( $bedrooms ); ?></td></tr>
            <?php endif; ?>
            <?php if ( $bathrooms ) : ?>
                <tr><td>Bathrooms</td><td><?php echo intval( $bathrooms ); ?></td></tr>
            <?php endif; ?>
            <?php if ( $area ) : ?>
                <tr><td>Area</td><td><?php echo esc_html( $area ); ?> sqm</td></tr>
            <?php endif; ?>
        </table>

        <?php
        $floor_plans = [];
        if ( ! empty( $property['floor_plans'] ) && is_array( $property['floor_plans'] ) ) {
            $floor_plans = $property['floor_plans'];
        }
        ?>
        <?php if ( ! empty( $floor_plans ) ) : ?>
            <div class="nexa-compact-floor-plans">
                <h4>Floor Plans</h4>
                <?php foreach ( $floor_plans as $index => $plan ) : ?>
                    <?php
                    $file_url = isset( $plan['file_url'] ) ? esc_url( $plan['file_url'] ) : '';
                    $label    = isset( $plan['label'] ) && ! empty( $plan['label'] ) ? esc_html( $plan['label'] ) : 'Floor Plan ' . ( $index + 1 );
                    ?>
                    <?php if ( $file_url ) : ?>
                        <a href="<?php echo $file_url; ?>" target="_blank" rel="noopener noreferrer">📄 <?php echo $label; ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( $has_location ) : ?>
        <div class="nexa-compact-tab-content" id="tab-location" style="display:none;">
            <?php if ( $address ) : ?>
                <p class="nexa-compact-address"><?php echo esc_html( $address ); ?></p>
            <?php endif; ?>
            <div id="nexa-property-map" class="nexa-map-container nexa-map-single"
                 data-lat="<?php echo esc_attr( $latitude ); ?>"
                 data-lng="<?php echo esc_attr( $longitude ); ?>"
                 data-title="<?php echo esc_attr( $title ); ?>">
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('.nexa-compact-tab');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = this.dataset.tab;
            tabs.forEach(function(t) { t.classList.remove('is-active'); });
            this.classList.add('is-active');
            document.querySelectorAll('.nexa-compact-tab-content').forEach(function(c) {
                c.style.display = c.id === 'tab-' + target ? 'block' : 'none';
            });
        });
    });
});
</script>
