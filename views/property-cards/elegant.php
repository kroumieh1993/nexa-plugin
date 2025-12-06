<?php
/**
 * Property Card Template: Elegant
 * 
 * Sophisticated design with subtle shadows and refined typography.
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
<a class="nexa-property-card nexa-card-elegant" href="<?php echo esc_url( $detail_url ); ?>" data-property-id="<?php echo esc_attr( $prop_id ); ?>">
    <div class="nexa-elegant-image">
        <?php if ( $first_image ) : ?>
            <img src="<?php echo esc_url( $first_image ); ?>" alt="<?php echo esc_attr( $title ); ?>">
        <?php else : ?>
            <div class="nexa-property-image-placeholder">No image</div>
        <?php endif; ?>
    </div>
    <div class="nexa-elegant-body">
        <div class="nexa-elegant-header">
            <?php if ( $category ) : ?>
                <span class="nexa-elegant-badge"><?php echo esc_html( ucfirst( $category ) ); ?></span>
            <?php endif; ?>
            <?php if ( $show_price && $price ) : ?>
                <span class="nexa-elegant-price"><?php echo esc_html( number_format_i18n( $price ) ); ?></span>
            <?php endif; ?>
        </div>
        <h3 class="nexa-elegant-title"><?php echo esc_html( $title ); ?></h3>
        <?php if ( $show_city && $city ) : ?>
            <p class="nexa-elegant-location"><?php echo esc_html( $city ); ?></p>
        <?php endif; ?>
        <?php if ( $show_location && $address ) : ?>
            <p class="nexa-elegant-address"><?php echo esc_html( $address ); ?></p>
        <?php endif; ?>
        <div class="nexa-elegant-divider"></div>
        <div class="nexa-elegant-stats">
            <?php if ( $show_bedrooms && $bedrooms ) : ?>
                <div class="nexa-elegant-stat">
                    <span class="nexa-elegant-stat-value"><?php echo intval( $bedrooms ); ?></span>
                    <span class="nexa-elegant-stat-label">Beds</span>
                </div>
            <?php endif; ?>
            <?php if ( $show_bathrooms && $bathrooms ) : ?>
                <div class="nexa-elegant-stat">
                    <span class="nexa-elegant-stat-value"><?php echo intval( $bathrooms ); ?></span>
                    <span class="nexa-elegant-stat-label">Baths</span>
                </div>
            <?php endif; ?>
            <?php if ( $show_area && $area ) : ?>
                <div class="nexa-elegant-stat">
                    <span class="nexa-elegant-stat-value"><?php echo esc_html( $area ); ?></span>
                    <span class="nexa-elegant-stat-label">sqm</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</a>
