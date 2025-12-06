<?php
/**
 * Property Card Template: Default
 * 
 * Standard property card with image on top and details below.
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
<a class="nexa-property-card nexa-card-default" href="<?php echo esc_url( $detail_url ); ?>" data-property-id="<?php echo esc_attr( $prop_id ); ?>">
    <div class="nexa-property-image">
        <?php if ( $first_image ) : ?>
            <img src="<?php echo esc_url( $first_image ); ?>" alt="<?php echo esc_attr( $title ); ?>">
        <?php else : ?>
            <div class="nexa-property-image-placeholder">No image</div>
        <?php endif; ?>
        <?php if ( $category ) : ?>
            <span class="nexa-property-chip"><?php echo esc_html( ucfirst( $category ) ); ?></span>
        <?php endif; ?>
    </div>
    <div class="nexa-property-body">
        <div class="nexa-property-top">
            <h3 class="nexa-property-title"><?php echo esc_html( $title ); ?></h3>
            <?php if ( $show_price && $price ) : ?>
                <div class="nexa-property-price">
                    <?php echo esc_html( number_format_i18n( $price ) ); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ( $show_city && $city ) : ?>
            <p class="nexa-property-location"><?php echo esc_html( $city ); ?></p>
        <?php endif; ?>
        <?php if ( $show_location && $address ) : ?>
            <p class="nexa-property-address"><?php echo esc_html( $address ); ?></p>
        <?php endif; ?>

        <div class="nexa-property-meta">
            <?php if ( $show_bedrooms && $bedrooms ) : ?>
                <span><?php echo intval( $bedrooms ); ?> Bedrooms</span>
            <?php endif; ?>
            <?php if ( $show_bathrooms && $bathrooms ) : ?>
                <span><?php echo intval( $bathrooms ); ?> Bathrooms</span>
            <?php endif; ?>
            <?php if ( $show_area && $area ) : ?>
                <span><?php echo esc_html( $area ); ?> sqm</span>
            <?php endif; ?>
        </div>

        <span class="nexa-property-view-btn">View details</span>
    </div>
</a>
