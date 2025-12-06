<?php
/**
 * Single Property Template: Default
 * 
 * Standard layout with hero section, gallery, and details grid.
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

<section class="nexa-single-hero">
    <div class="nexa-single-eyebrow">
        <?php if ( $category ) : ?>
            <span><?php echo esc_html( $category ); ?></span>
        <?php endif; ?>
        <?php if ( $type ) : ?>
            <span>• <?php echo esc_html( $type ); ?></span>
        <?php endif; ?>
    </div>

    <div class="nexa-single-title-row">
        <h1 class="nexa-single-title"><?php echo esc_html( $title ); ?></h1>
        <?php if ( $price ) : ?>
            <div class="nexa-single-price"><?php echo esc_html( $price ); ?></div>
        <?php endif; ?>
    </div>

    <div class="nexa-single-subline">
        <?php if ( $city ) : ?>
            <span class="nexa-single-pill">📍 <?php echo esc_html( $city ); ?></span>
        <?php endif; ?>
        <?php if ( $bedrooms ) : ?>
            <span class="nexa-single-pill">🛌 <?php echo intval( $bedrooms ); ?> Bedrooms</span>
        <?php endif; ?>
        <?php if ( $bathrooms ) : ?>
            <span class="nexa-single-pill">🛁 <?php echo intval( $bathrooms ); ?> Bathrooms</span>
        <?php endif; ?>
        <?php if ( $area ) : ?>
            <span class="nexa-single-pill">📐 <?php echo esc_html( $area ); ?> sqm</span>
        <?php endif; ?>
    </div>
</section>

<section class="nexa-single-gallery">
    <div class="nexa-gallery-main" data-nexa-gallery>
        <?php if ( ! empty( $images ) ) : ?>
            <?php foreach ( $images as $index => $img ) : ?>
                <img class="nexa-gallery-slide <?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-slide="<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?> image <?php echo esc_attr( $index + 1 ); ?>">
            <?php endforeach; ?>
            <?php if ( count( $images ) > 1 ) : ?>
                <div class="nexa-gallery-nav">
                    <button type="button" class="nexa-gallery-btn" data-nexa-prev aria-label="Previous image">‹</button>
                    <button type="button" class="nexa-gallery-btn" data-nexa-next aria-label="Next image">›</button>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-weight:600;">No images available</div>
        <?php endif; ?>
    </div>
    <?php if ( count( $images ) > 1 ) : ?>
        <div class="nexa-gallery-thumbs" data-nexa-thumbs>
            <?php foreach ( $images as $index => $img ) : ?>
                <img class="<?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-thumb="<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $img ); ?>" alt="Thumbnail for <?php echo esc_attr( $title ); ?>">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="nexa-details-grid">
    <?php if ( $bedrooms ) : ?>
        <div class="nexa-detail-card">
            <p class="nexa-detail-label">Bedrooms</p>
            <p class="nexa-detail-value"><?php echo intval( $bedrooms ); ?></p>
        </div>
    <?php endif; ?>
    <?php if ( $bathrooms ) : ?>
        <div class="nexa-detail-card">
            <p class="nexa-detail-label">Bathrooms</p>
            <p class="nexa-detail-value"><?php echo intval( $bathrooms ); ?></p>
        </div>
    <?php endif; ?>
    <?php if ( $area ) : ?>
        <div class="nexa-detail-card">
            <p class="nexa-detail-label">Area</p>
            <p class="nexa-detail-value"><?php echo esc_html( $area ); ?> sqm</p>
        </div>
    <?php endif; ?>
    <?php if ( $type ) : ?>
        <div class="nexa-detail-card">
            <p class="nexa-detail-label">Type</p>
            <p class="nexa-detail-value"><?php echo esc_html( ucfirst( $type ) ); ?></p>
        </div>
    <?php endif; ?>
    <?php if ( $category ) : ?>
        <div class="nexa-detail-card">
            <p class="nexa-detail-label">Category</p>
            <p class="nexa-detail-value"><?php echo esc_html( ucfirst( $category ) ); ?></p>
        </div>
    <?php endif; ?>
    <?php if ( $city ) : ?>
        <div class="nexa-detail-card">
            <p class="nexa-detail-label">City</p>
            <p class="nexa-detail-value"><?php echo esc_html( $city ); ?></p>
        </div>
    <?php endif; ?>
</section>

<section class="nexa-description">
    <h3>Description</h3>
    <p><?php echo $description ? wp_kses_post( $description ) : 'No description provided.'; ?></p>
</section>

<?php if ( $has_location && $latitude && $longitude ) : ?>
    <section class="nexa-location-section">
        <h3>Location</h3>
        <?php if ( $address ) : ?>
            <div class="nexa-location-address">
                <span>📍</span> <?php echo esc_html( $address ); ?>
            </div>
        <?php endif; ?>
        <div id="nexa-property-map" class="nexa-map-container nexa-map-single"
             data-lat="<?php echo esc_attr( $latitude ); ?>"
             data-lng="<?php echo esc_attr( $longitude ); ?>"
             data-title="<?php echo esc_attr( $title ); ?>">
        </div>
    </section>
<?php endif; ?>

<?php
$floor_plans = [];
if ( ! empty( $property['floor_plans'] ) && is_array( $property['floor_plans'] ) ) {
    $floor_plans = $property['floor_plans'];
    usort( $floor_plans, function( $a, $b ) {
        $order_a = is_array( $a ) && isset( $a['order'] ) ? (int) $a['order'] : 0;
        $order_b = is_array( $b ) && isset( $b['order'] ) ? (int) $b['order'] : 0;
        return $order_a - $order_b;
    } );
}
?>

<?php if ( ! empty( $floor_plans ) ) : ?>
    <section class="nexa-floor-plans">
        <h3>Floor Plans</h3>
        <div class="nexa-floor-plans-list">
            <?php foreach ( $floor_plans as $index => $plan ) : ?>
                <?php
                $file_url = isset( $plan['file_url'] ) ? esc_url( $plan['file_url'] ) : '';
                $label    = isset( $plan['label'] ) && ! empty( $plan['label'] ) ? esc_html( $plan['label'] ) : 'Floor Plan ' . ( $index + 1 );
                ?>
                <?php if ( $file_url ) : ?>
                    <a href="<?php echo $file_url; ?>" class="nexa-floor-plan-item" target="_blank" rel="noopener noreferrer">
                        <span class="nexa-floor-plan-icon" aria-hidden="true">📄</span>
                        <span class="nexa-floor-plan-label"><?php echo $label; ?></span>
                        <span class="nexa-floor-plan-action">View PDF</span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
