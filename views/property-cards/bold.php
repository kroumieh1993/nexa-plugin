<?php
/**
 * Property Card Template: Bold
 * 
 * Eye-catching design with large typography and vibrant colors.
 *
 * @var array  $property      Property data array.
 * @var string $detail_url    URL to property detail page.
 * @var bool   $show_price    Whether to show price.
 * @var bool   $show_bedrooms Whether to show bedrooms.
 * @var bool   $show_bathrooms Whether to show bathrooms.
 * @var bool   $show_city     Whether to show city.
 * @var bool   $show_area     Whether to show area.
 * @var bool   $show_location Whether to show address.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$first_image = '';
if ( ! empty( $property['images'] ) && is_array( $property['images'] ) ) {
    $first_image = $property['images'][0]['url'] ?? '';
}

$title      = $property['title'] ?? '';
$city       = $property['city'] ?? '';
$category   = $property['category'] ?? '';
$price      = isset( $property['price'] ) ? $property['price'] : null;
$bedrooms   = $property['bedrooms'] ?? null;
$bathrooms  = $property['bathrooms'] ?? null;
$area       = $property['area'] ?? null;
$address    = $property['address'] ?? '';
$prop_id    = $property['id'] ?? 0;
?>
<a class="nexa-property-card nexa-card-bold" href="<?php echo esc_url( $detail_url ); ?>" data-property-id="<?php echo esc_attr( $prop_id ); ?>">
    <div class="nexa-bold-image-container">
        <?php if ( $first_image ) : ?>
            <img src="<?php echo esc_url( $first_image ); ?>" alt="<?php echo esc_attr( $title ); ?>">
        <?php else : ?>
            <div class="nexa-property-image-placeholder">No image</div>
        <?php endif; ?>
        <div class="nexa-bold-gradient"></div>
        <div class="nexa-bold-overlay">
            <?php if ( $category ) : ?>
                <span class="nexa-bold-category"><?php echo esc_html( ucfirst( $category ) ); ?></span>
            <?php endif; ?>
            <h3 class="nexa-bold-title"><?php echo esc_html( $title ); ?></h3>
            <?php if ( $show_city && $city ) : ?>
                <p class="nexa-bold-location">📍 <?php echo esc_html( $city ); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="nexa-bold-footer">
        <div class="nexa-bold-features">
            <?php if ( $show_bedrooms && $bedrooms ) : ?>
                <div class="nexa-bold-feature">
                    <span class="nexa-bold-feature-value"><?php echo intval( $bedrooms ); ?></span>
                    <span class="nexa-bold-feature-label">Beds</span>
                </div>
            <?php endif; ?>
            <?php if ( $show_bathrooms && $bathrooms ) : ?>
                <div class="nexa-bold-feature">
                    <span class="nexa-bold-feature-value"><?php echo intval( $bathrooms ); ?></span>
                    <span class="nexa-bold-feature-label">Baths</span>
                </div>
            <?php endif; ?>
            <?php if ( $show_area && $area ) : ?>
                <div class="nexa-bold-feature">
                    <span class="nexa-bold-feature-value"><?php echo esc_html( $area ); ?></span>
                    <span class="nexa-bold-feature-label">sqm</span>
                </div>
            <?php endif; ?>
        </div>
        <?php if ( $show_price && $price ) : ?>
            <div class="nexa-bold-price"><?php echo esc_html( number_format_i18n( $price ) ); ?></div>
        <?php endif; ?>
    </div>
</a>
