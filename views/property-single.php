<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$property = get_query_var( 'nexa_property' );
$error    = get_query_var( 'nexa_property_error' );
$layout   = get_query_var( 'nexa_single_property_layout' ) ?: 'default';

// Valid layouts
$valid_layouts = [ 'default', 'modern', 'elegant', 'compact', 'minimal', 'bold' ];
if ( ! in_array( $layout, $valid_layouts, true ) ) {
    $layout = 'default';
}

// Enqueue map assets if property has location data
$has_location = ! empty( $property['latitude'] ) && ! empty( $property['longitude'] );
if ( $has_location ) {
    nexa_re_enqueue_map_assets();
}

get_header();
?>
<main class="nexa-single-shell nexa-single-layout-<?php echo esc_attr( $layout ); ?>">
    <?php if ( $error ) : ?>
        <div class="nexa-single-error"><?php echo esc_html( $error ); ?></div>
    <?php else : ?>
        <?php
        // Determine template path
        $template_path = NEXA_RE_PLUGIN_DIR . 'views/single-property/' . $layout . '.php';
        
        // Fallback to default if template doesn't exist
        if ( ! file_exists( $template_path ) ) {
            $template_path = NEXA_RE_PLUGIN_DIR . 'views/single-property/default.php';
        }
        
        // Include the template
        include $template_path;
        ?>
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
    const counter = gallery.querySelector('[data-current]');
    let currentIndex = 0;
    let startX = null;

    const updateActive = () => {
        slides.forEach((slide, index) => {
            slide.classList.toggle('is-active', index === currentIndex);
        });

        thumbs.forEach((thumb, index) => {
            thumb.classList.toggle('is-active', index === currentIndex);
        });

        if (counter) {
            counter.textContent = currentIndex + 1;
        }
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

// Map initialization script for Leaflet
document.addEventListener('DOMContentLoaded', function() {
    if (typeof L === 'undefined') return;
    
    var mapEl = document.getElementById('nexa-property-map');
    if (!mapEl) return;
    
    var lat = parseFloat(mapEl.dataset.lat);
    var lng = parseFloat(mapEl.dataset.lng);
    var title = mapEl.dataset.title || 'Property Location';
    
    if (isNaN(lat) || isNaN(lng)) return;
    
    var map = L.map('nexa-property-map').setView([lat, lng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    L.marker([lat, lng])
        .addTo(map)
        .bindPopup('<strong>' + title + '</strong>')
        .openPopup();
});
</script>
<?php
get_footer();
