<?php
/**
 * Single Property Template: Modern
 * 
 * Contemporary design with full-width gallery and floating details.
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

<div class="nexa-modern-single">
    <!-- Full-width Hero Gallery -->
    <section class="nexa-modern-hero">
        <div class="nexa-modern-hero-gallery" data-nexa-gallery>
            <?php if ( ! empty( $images ) ) : ?>
                <?php foreach ( $images as $index => $img ) : ?>
                    <img class="nexa-modern-hero-slide <?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-slide="<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                <?php endforeach; ?>
                <?php if ( count( $images ) > 1 ) : ?>
                    <div class="nexa-modern-gallery-nav">
                        <button type="button" class="nexa-modern-nav-btn" data-nexa-prev aria-label="Previous">←</button>
                        <span class="nexa-modern-counter"><span data-current>1</span> / <?php echo count( $images ); ?></span>
                        <button type="button" class="nexa-modern-nav-btn" data-nexa-next aria-label="Next">→</button>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="nexa-modern-no-image">No images available</div>
            <?php endif; ?>
        </div>
        
        <!-- Floating Info Card -->
        <div class="nexa-modern-info-card">
            <?php if ( $category ) : ?>
                <span class="nexa-modern-badge"><?php echo esc_html( ucfirst( $category ) ); ?></span>
            <?php endif; ?>
            <h1 class="nexa-modern-title"><?php echo esc_html( $title ); ?></h1>
            <?php if ( $city || $address ) : ?>
                <p class="nexa-modern-location">
                    📍 <?php echo esc_html( $address ?: $city ); ?>
                </p>
            <?php endif; ?>
            <?php if ( $price ) : ?>
                <div class="nexa-modern-price-tag"><?php echo esc_html( $price ); ?></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Quick Stats Bar -->
    <section class="nexa-modern-stats-bar">
        <?php if ( $bedrooms ) : ?>
            <div class="nexa-modern-stat">
                <span class="nexa-modern-stat-icon">🛏️</span>
                <div class="nexa-modern-stat-content">
                    <span class="nexa-modern-stat-value"><?php echo intval( $bedrooms ); ?></span>
                    <span class="nexa-modern-stat-label">Bedrooms</span>
                </div>
            </div>
        <?php endif; ?>
        <?php if ( $bathrooms ) : ?>
            <div class="nexa-modern-stat">
                <span class="nexa-modern-stat-icon">🚿</span>
                <div class="nexa-modern-stat-content">
                    <span class="nexa-modern-stat-value"><?php echo intval( $bathrooms ); ?></span>
                    <span class="nexa-modern-stat-label">Bathrooms</span>
                </div>
            </div>
        <?php endif; ?>
        <?php if ( $area ) : ?>
            <div class="nexa-modern-stat">
                <span class="nexa-modern-stat-icon">📐</span>
                <div class="nexa-modern-stat-content">
                    <span class="nexa-modern-stat-value"><?php echo esc_html( $area ); ?></span>
                    <span class="nexa-modern-stat-label">sqm</span>
                </div>
            </div>
        <?php endif; ?>
        <?php if ( $type ) : ?>
            <div class="nexa-modern-stat">
                <span class="nexa-modern-stat-icon">🏠</span>
                <div class="nexa-modern-stat-content">
                    <span class="nexa-modern-stat-value"><?php echo esc_html( ucfirst( $type ) ); ?></span>
                    <span class="nexa-modern-stat-label">Type</span>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Two Column Layout -->
    <section class="nexa-modern-content">
        <div class="nexa-modern-main">
            <div class="nexa-modern-section">
                <h2>About this property</h2>
                <div class="nexa-modern-description">
                    <?php echo $description ? wp_kses_post( $description ) : '<p>No description provided.</p>'; ?>
                </div>
            </div>

            <?php if ( $has_location ) : ?>
                <div class="nexa-modern-section">
                    <h2>Location</h2>
                    <div id="nexa-property-map" class="nexa-map-container nexa-map-single"
                         data-lat="<?php echo esc_attr( $latitude ); ?>"
                         data-lng="<?php echo esc_attr( $longitude ); ?>"
                         data-title="<?php echo esc_attr( $title ); ?>">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <aside class="nexa-modern-sidebar">
            <div class="nexa-modern-details-card">
                <h3>Property Details</h3>
                <ul class="nexa-modern-details-list">
                    <?php if ( $type ) : ?>
                        <li><span>Type</span><span><?php echo esc_html( ucfirst( $type ) ); ?></span></li>
                    <?php endif; ?>
                    <?php if ( $category ) : ?>
                        <li><span>Category</span><span><?php echo esc_html( ucfirst( $category ) ); ?></span></li>
                    <?php endif; ?>
                    <?php if ( $bedrooms ) : ?>
                        <li><span>Bedrooms</span><span><?php echo intval( $bedrooms ); ?></span></li>
                    <?php endif; ?>
                    <?php if ( $bathrooms ) : ?>
                        <li><span>Bathrooms</span><span><?php echo intval( $bathrooms ); ?></span></li>
                    <?php endif; ?>
                    <?php if ( $area ) : ?>
                        <li><span>Area</span><span><?php echo esc_html( $area ); ?> sqm</span></li>
                    <?php endif; ?>
                    <?php if ( $city ) : ?>
                        <li><span>City</span><span><?php echo esc_html( $city ); ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>

            <?php
            $floor_plans = [];
            if ( ! empty( $property['floor_plans'] ) && is_array( $property['floor_plans'] ) ) {
                $floor_plans = $property['floor_plans'];
            }
            ?>
            <?php if ( ! empty( $floor_plans ) ) : ?>
                <div class="nexa-modern-details-card">
                    <h3>Floor Plans</h3>
                    <div class="nexa-modern-floor-plans">
                        <?php foreach ( $floor_plans as $index => $plan ) : ?>
                            <?php
                            $file_url = isset( $plan['file_url'] ) ? esc_url( $plan['file_url'] ) : '';
                            $label    = isset( $plan['label'] ) && ! empty( $plan['label'] ) ? esc_html( $plan['label'] ) : 'Floor Plan ' . ( $index + 1 );
                            ?>
                            <?php if ( $file_url ) : ?>
                                <a href="<?php echo $file_url; ?>" class="nexa-modern-floor-plan" target="_blank" rel="noopener noreferrer">
                                    📄 <?php echo $label; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </section>
</div>
