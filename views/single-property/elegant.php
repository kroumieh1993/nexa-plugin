<?php
/**
 * Single Property Template: Elegant
 * 
 * Sophisticated design with refined typography and subtle animations.
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

<div class="nexa-elegant-single">
    <!-- Elegant Header -->
    <header class="nexa-elegant-header">
        <div class="nexa-elegant-header-content">
            <div class="nexa-elegant-breadcrumb">
                <?php if ( $category ) : ?>
                    <span><?php echo esc_html( ucfirst( $category ) ); ?></span>
                <?php endif; ?>
                <?php if ( $type ) : ?>
                    <span class="nexa-elegant-separator">·</span>
                    <span><?php echo esc_html( $type ); ?></span>
                <?php endif; ?>
            </div>
            <h1 class="nexa-elegant-title"><?php echo esc_html( $title ); ?></h1>
            <?php if ( $city || $address ) : ?>
                <p class="nexa-elegant-location"><?php echo esc_html( $address ?: $city ); ?></p>
            <?php endif; ?>
        </div>
        <?php if ( $price ) : ?>
            <div class="nexa-elegant-price-wrapper">
                <span class="nexa-elegant-price-label">Price</span>
                <span class="nexa-elegant-price"><?php echo esc_html( $price ); ?></span>
            </div>
        <?php endif; ?>
    </header>

    <!-- Gallery Grid -->
    <section class="nexa-elegant-gallery" data-nexa-gallery>
        <?php if ( ! empty( $images ) ) : ?>
            <div class="nexa-elegant-gallery-main">
                <?php foreach ( $images as $index => $img ) : ?>
                    <img class="nexa-gallery-slide <?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-slide="<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                <?php endforeach; ?>
                <?php if ( count( $images ) > 1 ) : ?>
                    <div class="nexa-elegant-gallery-controls">
                        <button type="button" data-nexa-prev aria-label="Previous">‹</button>
                        <button type="button" data-nexa-next aria-label="Next">›</button>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ( count( $images ) > 1 ) : ?>
                <div class="nexa-elegant-thumbnails" data-nexa-thumbs>
                    <?php foreach ( array_slice( $images, 0, 5 ) as $index => $img ) : ?>
                        <img class="<?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-thumb="<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $img ); ?>" alt="">
                    <?php endforeach; ?>
                    <?php if ( count( $images ) > 5 ) : ?>
                        <span class="nexa-elegant-more">+<?php echo count( $images ) - 5; ?> more</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="nexa-elegant-no-images">No images available</div>
        <?php endif; ?>
    </section>

    <!-- Features Strip -->
    <section class="nexa-elegant-features">
        <?php if ( $bedrooms ) : ?>
            <div class="nexa-elegant-feature">
                <span class="nexa-elegant-feature-number"><?php echo intval( $bedrooms ); ?></span>
                <span class="nexa-elegant-feature-text">Bedrooms</span>
            </div>
        <?php endif; ?>
        <?php if ( $bathrooms ) : ?>
            <div class="nexa-elegant-feature">
                <span class="nexa-elegant-feature-number"><?php echo intval( $bathrooms ); ?></span>
                <span class="nexa-elegant-feature-text">Bathrooms</span>
            </div>
        <?php endif; ?>
        <?php if ( $area ) : ?>
            <div class="nexa-elegant-feature">
                <span class="nexa-elegant-feature-number"><?php echo esc_html( $area ); ?></span>
                <span class="nexa-elegant-feature-text">Square meters</span>
            </div>
        <?php endif; ?>
    </section>

    <!-- Content Grid -->
    <section class="nexa-elegant-content">
        <div class="nexa-elegant-description-section">
            <h2>Property Description</h2>
            <div class="nexa-elegant-description">
                <?php echo $description ? wp_kses_post( $description ) : '<p>No description provided.</p>'; ?>
            </div>
        </div>

        <div class="nexa-elegant-info-section">
            <h2>Property Information</h2>
            <dl class="nexa-elegant-info-list">
                <?php if ( $type ) : ?>
                    <div class="nexa-elegant-info-item">
                        <dt>Property Type</dt>
                        <dd><?php echo esc_html( ucfirst( $type ) ); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ( $category ) : ?>
                    <div class="nexa-elegant-info-item">
                        <dt>Category</dt>
                        <dd><?php echo esc_html( ucfirst( $category ) ); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ( $city ) : ?>
                    <div class="nexa-elegant-info-item">
                        <dt>City</dt>
                        <dd><?php echo esc_html( $city ); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ( $bedrooms ) : ?>
                    <div class="nexa-elegant-info-item">
                        <dt>Bedrooms</dt>
                        <dd><?php echo intval( $bedrooms ); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ( $bathrooms ) : ?>
                    <div class="nexa-elegant-info-item">
                        <dt>Bathrooms</dt>
                        <dd><?php echo intval( $bathrooms ); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ( $area ) : ?>
                    <div class="nexa-elegant-info-item">
                        <dt>Area</dt>
                        <dd><?php echo esc_html( $area ); ?> sqm</dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>
    </section>

    <?php if ( $has_location ) : ?>
        <section class="nexa-elegant-map-section">
            <h2>Location</h2>
            <?php if ( $address ) : ?>
                <p class="nexa-elegant-address"><?php echo esc_html( $address ); ?></p>
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
    }
    ?>
    <?php if ( ! empty( $floor_plans ) ) : ?>
        <section class="nexa-elegant-floor-plans">
            <h2>Floor Plans</h2>
            <div class="nexa-elegant-floor-plans-grid">
                <?php foreach ( $floor_plans as $index => $plan ) : ?>
                    <?php
                    $file_url = isset( $plan['file_url'] ) ? esc_url( $plan['file_url'] ) : '';
                    $label    = isset( $plan['label'] ) && ! empty( $plan['label'] ) ? esc_html( $plan['label'] ) : 'Floor Plan ' . ( $index + 1 );
                    ?>
                    <?php if ( $file_url ) : ?>
                        <a href="<?php echo $file_url; ?>" class="nexa-elegant-floor-plan-link" target="_blank" rel="noopener noreferrer">
                            <span class="nexa-elegant-floor-plan-icon">📄</span>
                            <span class="nexa-elegant-floor-plan-name"><?php echo $label; ?></span>
                            <span class="nexa-elegant-floor-plan-action">View →</span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
