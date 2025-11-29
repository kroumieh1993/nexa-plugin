<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$property = get_query_var( 'nexa_property' );
$error    = get_query_var( 'nexa_property_error' );

get_header();
?>
<main class="nexa-single-shell">
    <style>
        .nexa-single-shell {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px 60px;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #0f172a;
        }
        .nexa-single-hero {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 26px;
        }
        .nexa-single-eyebrow {
            display: inline-flex;
            gap: 10px;
            align-items: center;
            font-size: 12px;
            color: #6366f1;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .nexa-single-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }
        .nexa-single-title {
            font-size: clamp(26px, 4vw, 34px);
            margin: 0;
            line-height: 1.15;
        }
        .nexa-single-price {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 18px;
            box-shadow: 0 14px 40px rgba(79, 70, 229, 0.15);
            white-space: nowrap;
        }
        .nexa-single-subline {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
            font-size: 14px;
            color: #475569;
        }
        .nexa-single-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 999px;
            color: #0f172a;
            font-weight: 600;
            font-size: 12px;
        }
        .nexa-single-gallery {
            background: #ffffff;
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            margin-bottom: 24px;
        }
        .nexa-gallery-main {
            position: relative;
            padding-top: 56.25%;
            border-radius: 14px;
            overflow: hidden;
            background: linear-gradient(120deg, #e2e8f0, #f8fafc);
        }
        .nexa-gallery-slide {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.28s ease-in-out;
        }
        .nexa-gallery-slide.is-active {
            opacity: 1;
        }
        .nexa-gallery-nav {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            pointer-events: none;
            padding: 0 8px;
        }
        .nexa-gallery-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: rgba(15, 23, 42, 0.54);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            pointer-events: auto;
            display: grid;
            place-items: center;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .nexa-gallery-btn:hover {
            background: rgba(79, 70, 229, 0.9);
            transform: translateY(-1px);
        }
        .nexa-gallery-thumbs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .nexa-gallery-thumbs img {
            width: 100%;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
            opacity: 0.7;
            border: 2px solid transparent;
            transition: opacity 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
            cursor: pointer;
        }
        .nexa-gallery-thumbs img.is-active {
            opacity: 1;
            border-color: #4f46e5;
            transform: translateY(-1px);
        }
        .nexa-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .nexa-detail-card {
            padding: 14px 16px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
        }
        .nexa-detail-label {
            font-size: 12px;
            color: #94a3b8;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            margin: 0 0 6px;
        }
        .nexa-detail-value {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }
        .nexa-description {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }
        .nexa-description h3 {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 18px;
        }
        .nexa-description p {
            margin: 0;
            color: #475569;
            line-height: 1.6;
        }
        .nexa-single-error {
            padding: 14px 16px;
            background: #fef2f2;
            color: #991b1b;
            border-radius: 12px;
            border: 1px solid #fecaca;
        }
        @media (max-width: 720px) {
            .nexa-single-shell {
                margin-top: 20px;
            }
            .nexa-single-title-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .nexa-single-price {
                width: 100%;
            }
        }
    </style>

    <?php if ( $error ) : ?>
        <div class="nexa-single-error"><?php echo esc_html( $error ); ?></div>
    <?php else : ?>
        <?php
        $title       = $property['title'] ?? '';
        $price       = isset( $property['price'] ) ? number_format_i18n( $property['price'] ) : '';
        $city        = $property['city'] ?? '';
        $category    = $property['category'] ?? '';
        $type        = $property['property_type'] ?? '';
        $bedrooms    = $property['bedrooms'] ?? '';
        $bathrooms   = $property['bathrooms'] ?? '';
        $area        = $property['area'] ?? '';
        $description = $property['description'] ?? '';
        $images      = [];

        if ( ! empty( $property['images'] ) && is_array( $property['images'] ) ) {
            foreach ( $property['images'] as $img ) {
                if ( ! empty( $img['url'] ) ) {
                    $images[] = esc_url( $img['url'] );
                }
            }
        }
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
                    <span class="nexa-single-pill">📐 <?php echo esc_html( $area ); ?> sq ft</span>
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
                    <p class="nexa-detail-value"><?php echo esc_html( $area ); ?> sq ft</p>
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
    <?php endif; ?>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const gallery = document.querySelector('[data-nexa-gallery]');
    if (!gallery) {
        return;
    }

    const slides = Array.from(gallery.querySelectorAll('[data-nexa-slide]'));
    if (!slides.length) {
        return;
    }

    const thumbs = Array.from(document.querySelectorAll('[data-nexa-thumb]'));
    const nextBtn = gallery.querySelector('[data-nexa-next]');
    const prevBtn = gallery.querySelector('[data-nexa-prev]');
    let currentIndex = 0;
    let startX = null;

    const updateActive = () => {
        slides.forEach((slide, index) => {
            slide.classList.toggle('is-active', index === currentIndex);
        });

        thumbs.forEach((thumb, index) => {
            thumb.classList.toggle('is-active', index === currentIndex);
        });
    };

    const goTo = (index) => {
        const total = slides.length;
        currentIndex = (index + total) % total;
        updateActive();
    };

    const goNext = () => goTo(currentIndex + 1);
    const goPrev = () => goTo(currentIndex - 1);

    nextBtn?.addEventListener('click', goNext);
    prevBtn?.addEventListener('click', goPrev);

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const index = parseInt(thumb.dataset.nexaThumb, 10);
            if (!Number.isNaN(index)) {
                goTo(index);
            }
        });
    });

    const handleTouchStart = (event) => {
        const touch = event.touches[0];
        if (touch) {
            startX = touch.clientX;
        }
    };

    const handleTouchEnd = (event) => {
        if (startX === null) {
            return;
        }

        const touch = event.changedTouches[0];
        if (!touch) {
            return;
        }

        const deltaX = touch.clientX - startX;
        if (Math.abs(deltaX) > 40) {
            if (deltaX > 0) {
                goPrev();
            } else {
                goNext();
            }
        }

        startX = null;
    };

    gallery.addEventListener('touchstart', handleTouchStart, { passive: true });
    gallery.addEventListener('touchend', handleTouchEnd);
});
</script>
<?php
get_footer();
