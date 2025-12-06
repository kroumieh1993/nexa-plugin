<?php
/**
 * Single Property Template: Minimal
 * 
 * Clean, minimalist design focusing on essential information.
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

<div class="nexa-minimal-single">
    <!-- Minimal Gallery -->
    <div class="nexa-minimal-gallery" data-nexa-gallery>
        <?php if ( ! empty( $images ) ) : ?>
            <?php foreach ( $images as $index => $img ) : ?>
                <img class="nexa-gallery-slide <?php echo 0 === $index ? 'is-active' : ''; ?>" data-nexa-slide="<?php echo esc_attr( $index ); ?>" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>">
            <?php endforeach; ?>
            <?php if ( count( $images ) > 1 ) : ?>
                <div class="nexa-minimal-gallery-nav">
                    <button type="button" data-nexa-prev aria-label="Previous">←</button>
                    <span><span data-current>1</span>/<?php echo count( $images ); ?></span>
                    <button type="button" data-nexa-next aria-label="Next">→</button>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="nexa-minimal-no-image">No images</div>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <div class="nexa-minimal-content">
        <header class="nexa-minimal-header">
            <h1><?php echo esc_html( $title ); ?></h1>
            <?php if ( $city ) : ?>
                <p class="nexa-minimal-location"><?php echo esc_html( $city ); ?></p>
            <?php endif; ?>
        </header>

        <div class="nexa-minimal-price-row">
            <?php if ( $price ) : ?>
                <span class="nexa-minimal-price"><?php echo esc_html( $price ); ?></span>
            <?php endif; ?>
            <div class="nexa-minimal-badges">
                <?php if ( $category ) : ?>
                    <span><?php echo esc_html( ucfirst( $category ) ); ?></span>
                <?php endif; ?>
                <?php if ( $type ) : ?>
                    <span><?php echo esc_html( $type ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="nexa-minimal-specs">
            <?php if ( $bedrooms ) : ?>
                <div>
                    <strong><?php echo intval( $bedrooms ); ?></strong>
                    <span>bedrooms</span>
                </div>
            <?php endif; ?>
            <?php if ( $bathrooms ) : ?>
                <div>
                    <strong><?php echo intval( $bathrooms ); ?></strong>
                    <span>bathrooms</span>
                </div>
            <?php endif; ?>
            <?php if ( $area ) : ?>
                <div>
                    <strong><?php echo esc_html( $area ); ?></strong>
                    <span>sqm</span>
                </div>
            <?php endif; ?>
        </div>

        <hr class="nexa-minimal-divider">

        <div class="nexa-minimal-description">
            <?php echo $description ? wp_kses_post( $description ) : '<p>No description provided.</p>'; ?>
        </div>

        <?php if ( $has_location ) : ?>
            <hr class="nexa-minimal-divider">
            <div class="nexa-minimal-map-section">
                <h2>Location</h2>
                <?php if ( $address ) : ?>
                    <p class="nexa-minimal-address"><?php echo esc_html( $address ); ?></p>
                <?php endif; ?>
                <div id="nexa-property-map" class="nexa-map-container nexa-map-single"
                     data-lat="<?php echo esc_attr( $latitude ); ?>"
                     data-lng="<?php echo esc_attr( $longitude ); ?>"
                     data-title="<?php echo esc_attr( $title ); ?>">
                </div>
            </div>
        <?php endif; ?>

        <?php
        $floor_plans = [];
        if ( ! empty( $property['floor_plans'] ) && is_array( $property['floor_plans'] ) ) {
            $floor_plans = $property['floor_plans'];
        }
        ?>
        <?php if ( ! empty( $floor_plans ) ) : ?>
            <hr class="nexa-minimal-divider">
            <div class="nexa-minimal-floor-plans">
                <h2>Floor Plans</h2>
                <?php foreach ( $floor_plans as $index => $plan ) : ?>
                    <?php
                    $file_url = isset( $plan['file_url'] ) ? esc_url( $plan['file_url'] ) : '';
                    $label    = isset( $plan['label'] ) && ! empty( $plan['label'] ) ? esc_html( $plan['label'] ) : 'Floor Plan ' . ( $index + 1 );
                    ?>
                    <?php if ( $file_url ) : ?>
                        <a href="<?php echo $file_url; ?>" target="_blank" rel="noopener noreferrer"><?php echo $label; ?> →</a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
