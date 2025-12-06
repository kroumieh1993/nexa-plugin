<?php
/**
 * Single Property Template: Bold
 * 
 * Eye-catching design with large visuals and prominent typography.
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
$first_image = ! empty( $images ) ? $images[0] : '';
?>

<div class="nexa-bold-single">
    <!-- Bold Hero Section -->
    <section class="nexa-bold-hero" <?php if ( $first_image ) : ?>style="background-image: url('<?php echo esc_url( $first_image ); ?>');"<?php endif; ?>>
        <div class="nexa-bold-hero-overlay"></div>
        <div class="nexa-bold-hero-content">
            <?php if ( $category ) : ?>
                <span class="nexa-bold-category-tag"><?php echo esc_html( strtoupper( $category ) ); ?></span>
            <?php endif; ?>
            <h1 class="nexa-bold-hero-title"><?php echo esc_html( $title ); ?></h1>
            <?php if ( $city || $address ) : ?>
                <p class="nexa-bold-hero-location">📍 <?php echo esc_html( $address ?: $city ); ?></p>
            <?php endif; ?>
            <?php if ( $price ) : ?>
                <div class="nexa-bold-hero-price"><?php echo esc_html( $price ); ?></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Banner -->
    <section class="nexa-bold-features-banner">
        <?php if ( $bedrooms ) : ?>
            <div class="nexa-bold-feature-item">
                <span class="nexa-bold-feature-icon">🛏️</span>
                <span class="nexa-bold-feature-value"><?php echo intval( $bedrooms ); ?></span>
                <span class="nexa-bold-feature-label">Bedrooms</span>
            </div>
        <?php endif; ?>
        <?php if ( $bathrooms ) : ?>
            <div class="nexa-bold-feature-item">
                <span class="nexa-bold-feature-icon">🚿</span>
                <span class="nexa-bold-feature-value"><?php echo intval( $bathrooms ); ?></span>
                <span class="nexa-bold-feature-label">Bathrooms</span>
            </div>
        <?php endif; ?>
        <?php if ( $area ) : ?>
            <div class="nexa-bold-feature-item">
                <span class="nexa-bold-feature-icon">📐</span>
                <span class="nexa-bold-feature-value"><?php echo esc_html( $area ); ?></span>
                <span class="nexa-bold-feature-label">Square Meters</span>
            </div>
        <?php endif; ?>
        <?php if ( $type ) : ?>
            <div class="nexa-bold-feature-item">
                <span class="nexa-bold-feature-icon">🏠</span>
                <span class="nexa-bold-feature-value"><?php echo esc_html( ucfirst( $type ) ); ?></span>
                <span class="nexa-bold-feature-label">Property Type</span>
            </div>
        <?php endif; ?>
    </section>

    <!-- Gallery Grid -->
    <?php if ( count( $images ) > 1 ) : ?>
        <section class="nexa-bold-gallery-section">
            <h2>Gallery</h2>
            <div class="nexa-bold-gallery-grid" data-nexa-gallery>
                <?php foreach ( $images as $index => $img ) : ?>
                    <img class="nexa-bold-gallery-item <?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-slide="<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Description Section -->
    <section class="nexa-bold-description-section">
        <div class="nexa-bold-section-header">
            <h2>About This Property</h2>
        </div>
        <div class="nexa-bold-description">
            <?php echo $description ? wp_kses_post( $description ) : '<p>No description provided.</p>'; ?>
        </div>
    </section>

    <!-- Details Cards -->
    <section class="nexa-bold-details-section">
        <h2>Property Details</h2>
        <div class="nexa-bold-details-grid">
            <?php if ( $type ) : ?>
                <div class="nexa-bold-detail-card">
                    <span class="nexa-bold-detail-label">Type</span>
                    <span class="nexa-bold-detail-value"><?php echo esc_html( ucfirst( $type ) ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $category ) : ?>
                <div class="nexa-bold-detail-card">
                    <span class="nexa-bold-detail-label">Category</span>
                    <span class="nexa-bold-detail-value"><?php echo esc_html( ucfirst( $category ) ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $city ) : ?>
                <div class="nexa-bold-detail-card">
                    <span class="nexa-bold-detail-label">City</span>
                    <span class="nexa-bold-detail-value"><?php echo esc_html( $city ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $bedrooms ) : ?>
                <div class="nexa-bold-detail-card">
                    <span class="nexa-bold-detail-label">Bedrooms</span>
                    <span class="nexa-bold-detail-value"><?php echo intval( $bedrooms ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $bathrooms ) : ?>
                <div class="nexa-bold-detail-card">
                    <span class="nexa-bold-detail-label">Bathrooms</span>
                    <span class="nexa-bold-detail-value"><?php echo intval( $bathrooms ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $area ) : ?>
                <div class="nexa-bold-detail-card">
                    <span class="nexa-bold-detail-label">Area</span>
                    <span class="nexa-bold-detail-value"><?php echo esc_html( $area ); ?> sqm</span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ( $has_location ) : ?>
        <section class="nexa-bold-map-section">
            <h2>Location</h2>
            <?php if ( $address ) : ?>
                <p class="nexa-bold-address">📍 <?php echo esc_html( $address ); ?></p>
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
        <section class="nexa-bold-floor-plans">
            <h2>Floor Plans</h2>
            <div class="nexa-bold-floor-plans-list">
                <?php foreach ( $floor_plans as $index => $plan ) : ?>
                    <?php
                    $file_url = isset( $plan['file_url'] ) ? esc_url( $plan['file_url'] ) : '';
                    $label    = isset( $plan['label'] ) && ! empty( $plan['label'] ) ? esc_html( $plan['label'] ) : 'Floor Plan ' . ( $index + 1 );
                    ?>
                    <?php if ( $file_url ) : ?>
                        <a href="<?php echo $file_url; ?>" class="nexa-bold-floor-plan-btn" target="_blank" rel="noopener noreferrer">
                            📄 <?php echo $label; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
