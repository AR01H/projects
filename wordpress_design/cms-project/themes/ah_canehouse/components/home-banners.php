<?php
/**
 * The Cane House – Hero Banner Carousel
 *
 * Banners are managed in the plugin: CMS ADMIN → Home Banners (stored in the DB).
 * This template just renders whatever ch_get_home_banners() returns.
 *
 * Each banner row supports:
 *   image, subtitle, title (limited HTML), description,
 *   btn_text, btn_url, btn_target, text_align, text_pos, overlay
 */
defined( 'ABSPATH' ) || exit;

$banners     = ch_get_home_banners();
$autoplay_ms = ch_get_banner_autoplay();

if ( empty( $banners ) ) return;

/* ── Unique ID so multiple carousels on same page work ──── */
$uid = 'ch-hero-' . wp_unique_id();
?>

<!-- ════════════════════════════════════════════════════════
     THE CANE HOUSE - HERO BANNER CAROUSEL
     ════════════════════════════════════════════════════════ -->
<section class="ch-carousel-hero-banner" id="<?php echo esc_attr( $uid ); ?>"
         aria-label="Hero Banner"
         data-autoplay="<?php echo esc_attr( $autoplay_ms ); ?>">

    <!-- TRACK -->
    <div class="ch-hero-track" role="list">
        <?php foreach ( $banners as $i => $b ) :
            $align     = in_array( $b['text_align'] ?? 'center', ['left','right','center'] ) ? $b['text_align'] : 'center';
            $pos       = in_array( $b['text_pos']   ?? 'middle', ['top','middle','bottom'] ) ? $b['text_pos']   : 'middle';
            $overlay   = esc_attr( $b['overlay'] ?? 'rgba(26,58,15,0.45)' );
            $img_desk  = esc_url( $b['image'] ?? '' );
            // Mobile image is optional - fall back to the desktop image when blank.
            $img_mob   = ! empty( $b['image_mobile'] ) ? esc_url( $b['image_mobile'] ) : $img_desk;
            $bg_style  = "--bg:url('{$img_desk}');--bg-mobile:url('{$img_mob}');";
        ?>
        <div class="ch-hero-slide ch-hero-slide--align-<?php echo $align; ?> ch-hero-slide--pos-<?php echo $pos; ?>"
             role="listitem"
             aria-label="Slide <?php echo $i + 1; ?>"
             aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>">

            <!-- Background image (separate desktop / mobile source via CSS vars) -->
            <div class="ch-hero-slide__bg"
                 style="<?php echo esc_attr( $bg_style ); ?>"
                 role="img"
                 aria-label="<?php echo esc_attr( strip_tags( $b['title'] ?? '' ) ); ?>">
            </div>

            <!-- Scrim / overlay -->
            <div class="ch-hero-slide__overlay" style="background:<?php echo $overlay; ?>;"></div>

            <!-- Content -->
            <div class="ch-hero-slide__content">
                <?php if ( ! empty( $b['subtitle'] ) ) : ?>
                    <p class="ch-hero-slide__subtitle"><?php echo esc_html( $b['subtitle'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $b['title'] ) ) : ?>
                    <h2 class="ch-hero-slide__title"><?php echo wp_kses_post( $b['title'] ); ?></h2>
                <?php endif; ?>

                <?php if ( ! empty( $b['description'] ) ) : ?>
                    <p class="ch-hero-slide__desc"><?php echo esc_html( $b['description'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $b['btn_text'] ) && ! empty( $b['btn_url'] ) ) : ?>
                    <a class="ch-hero-slide__btn"
                       href="<?php echo esc_url( $b['btn_url'] ); ?>"
                       target="<?php echo esc_attr( $b['btn_target'] ?? '_self' ); ?>">
                        <?php echo esc_html( $b['btn_text'] ); ?>
                        <span class="ch-hero-slide__btn-arrow" aria-hidden="true">→</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div><!-- /.ch-hero-track -->

    <!-- DOTS - positioned inside the banner -->
    <?php if ( count( $banners ) > 1 ) : ?>
    <div class="ch-hero-dots" role="tablist" aria-label="Slide navigation">
        <?php foreach ( $banners as $i => $b ) : ?>
        <button class="ch-hero-dot <?php echo $i === 0 ? 'is-active' : ''; ?>"
                role="tab"
                aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                aria-label="Go to slide <?php echo $i + 1; ?>"
                data-index="<?php echo $i; ?>">
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Progress bar -->
    <div class="ch-hero-progress" aria-hidden="true">
        <div class="ch-hero-progress__bar"></div>
    </div>

</section><!-- /.ch-carousel-hero-banner -->


<!-- ════════════════════════════════════════════════════════
     STYLES
     ════════════════════════════════════════════════════════ -->
<style>
/* ── Scoped reset ────────────────────────────────────────── */
.ch-carousel-hero-banner *,
.ch-carousel-hero-banner *::before,
.ch-carousel-hero-banner *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* ── Wrapper ─────────────────────────────────────────────── */
.ch-carousel-hero-banner {
    position: relative;
    width: 100%;
    overflow: hidden;
    height: 90vh;
    min-height: 480px;
    max-height: 900px;
    background: var(--ch-bg-dark, #1a3a0f);
    padding: 0 !important;
}

/* ── Track ───────────────────────────────────────────────── */
.ch-hero-track {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.75s cubic-bezier(0.77, 0, 0.175, 1);
    will-change: transform;
}

/* ── Slide ───────────────────────────────────────────────── */
.ch-hero-slide {
    flex: 0 0 100%;
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    overflow: hidden;
}

/* Background image */
.ch-hero-slide__bg {
    position: absolute;
    inset: 0;
    background-image: var(--bg);
    background-size: 100% 100%;   /* fill: stretch to cover the whole banner */
    background-position: center;
    background-repeat: no-repeat;
    transform: scale(1.08);
    transition: transform 8s ease;
}
/* Use the mobile image (falls back to desktop when none supplied) */
@media (max-width: 768px) {
    .ch-hero-slide__bg { background-image: var(--bg-mobile, var(--bg)); }
}
.ch-hero-slide.is-active .ch-hero-slide__bg {
    transform: scale(1);
}

/* Overlay scrim */
.ch-hero-slide__overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
}

/* ── Content ─────────────────────────────────────────────── */
.ch-hero-slide__content {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: var(--ch-container, 1200px);
    margin-inline: auto;
    padding: 2rem clamp(1.25rem, 5vw, 4rem);
    display: flex;
    flex-direction: column;
    gap: 1rem;
    pointer-events: none;
}

/* Vertical alignment */
.ch-hero-slide--pos-top    .ch-hero-slide__content { justify-content: flex-start; padding-top: clamp(4rem, 10vh, 8rem); }
.ch-hero-slide--pos-middle .ch-hero-slide__content { justify-content: center; }
.ch-hero-slide--pos-bottom .ch-hero-slide__content { justify-content: flex-end; padding-bottom: clamp(4rem, 10vh, 8rem); }

/* Horizontal alignment */
.ch-hero-slide--align-left   .ch-hero-slide__content { align-items: flex-start; text-align: left; }
.ch-hero-slide--align-center .ch-hero-slide__content { align-items: center;     text-align: center; }
.ch-hero-slide--align-right  .ch-hero-slide__content { align-items: flex-end;   text-align: right; }

/* ── Typography ──────────────────────────────────────────── */
.ch-hero-slide__subtitle {
    font-size: clamp(0.75rem, 1.5vw, 0.95rem);
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ch-lime, #c8e830);
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease 0.2s, transform 0.6s ease 0.2s;
}

.ch-hero-slide__title {
    font-size: clamp(2rem, 6vw, 4.5rem);
    font-weight: 800;
    line-height: 1.1;
    color: var(--ch-white, #fdfff8);
    text-shadow: 0 2px 24px rgba(0,0,0,0.4);
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease 0.35s, transform 0.7s ease 0.35s;
}

.ch-hero-slide__desc {
    font-size: clamp(0.875rem, 1.8vw, 1.1rem);
    font-weight: 400;
    line-height: 1.65;
    color: var(--ch-text-light, rgba(232,245,224,0.82));
    max-width: 560px;
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.7s ease 0.5s, transform 0.7s ease 0.5s;
}

.ch-hero-slide--align-center .ch-hero-slide__desc { text-align: center; }

/* ── Button ──────────────────────────────────────────────── */
.ch-hero-slide__btn {
    pointer-events: all;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding: 0.85rem 2rem;
    background: var(--ch-lime, #c8e830);
    color: var(--ch-green-deep, #2d5a1b);
    font-size: clamp(0.8rem, 1.4vw, 0.95rem);
    font-weight: 700;
    letter-spacing: 0.06em;
    text-decoration: none;
    border-radius: var(--ch-radius-pill, 50px);
    border: 2px solid transparent;
    box-shadow: 0 4px 24px var(--ch-lime-glow, rgba(200,232,48,0.35));
    opacity: 0;
    transform: translateY(20px);
    transition:
        opacity 0.6s ease 0.65s,
        transform 0.6s ease 0.65s,
        background 0.25s ease,
        color 0.25s ease,
        border-color 0.25s ease,
        box-shadow 0.25s ease;
}
.ch-hero-slide__btn:hover,
.ch-hero-slide__btn:focus-visible {
    background: transparent;
    color: var(--ch-lime, #c8e830);
    border-color: var(--ch-lime, #c8e830);
    box-shadow: 0 4px 32px var(--ch-lime-glow, rgba(200,232,48,0.45));
}
.ch-hero-slide__btn-arrow {
    display: inline-block;
    transition: transform 0.2s ease;
}
.ch-hero-slide__btn:hover .ch-hero-slide__btn-arrow { transform: translateX(4px); }

/* ── Active slide - animate in ───────────────────────────── */
.ch-hero-slide.is-active .ch-hero-slide__subtitle,
.ch-hero-slide.is-active .ch-hero-slide__title,
.ch-hero-slide.is-active .ch-hero-slide__desc,
.ch-hero-slide.is-active .ch-hero-slide__btn {
    opacity: 1;
    transform: translateY(0);
}

/* ── Dots ────────────────────────────────────────────────── */
.ch-hero-dots {
    position: absolute;
    right: clamp(1rem, 2.5vw, 2rem);
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 10;
}

.ch-hero-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.6);
    background: transparent;
    cursor: pointer;
    padding: 0;
    transition: var(--ch-transition, all 0.3s ease);
    position: relative;
}
.ch-hero-dot::after {
    content: '';
    position: absolute;
    inset: -6px;
}
.ch-hero-dot.is-active {
    background: var(--ch-lime, #c8e830);
    border-color: var(--ch-lime, #c8e830);
    transform: scale(1.3);
    box-shadow: 0 0 8px var(--ch-lime-glow, rgba(200,232,48,0.5));
}
.ch-hero-dot:hover:not(.is-active) {
    background: rgba(255,255,255,0.45);
    border-color: rgba(255,255,255,0.9);
}

/* ── Arrows ──────────────────────────────────────────────── */
.ch-hero-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.35);
    background: rgba(26,58,15,0.45);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(6px);
    transition: var(--ch-transition, all 0.3s ease);
    padding: 0;
}
.ch-hero-arrow svg { width: 22px; height: 22px; }
.ch-hero-arrow--prev { left: clamp(0.75rem, 2vw, 1.5rem); }
.ch-hero-arrow--next { right: clamp(3.5rem, 6vw, 5rem); }
.ch-hero-arrow:hover,
.ch-hero-arrow:focus-visible {
    background: var(--ch-lime, #c8e830);
    border-color: var(--ch-lime, #c8e830);
    color: var(--ch-green-deep, #2d5a1b);
}

/* ── Progress bar ────────────────────────────────────────── */
.ch-hero-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: rgba(255,255,255,0.15);
    z-index: 10;
}
.ch-hero-progress__bar {
    height: 100%;
    width: 0%;
    background: var(--ch-lime, #c8e830);
    transition: width 0.1s linear;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 768px) {
    .ch-carousel-hero-banner {
        height: 80svh;
        min-height: 380px;
        max-height: 640px;
    }
    .ch-hero-arrow {
        width: 38px;
        height: 38px;
    }
    .ch-hero-arrow svg { width: 18px; height: 18px; }
    .ch-hero-arrow--next { right: clamp(3rem, 8vw, 4rem); }
    .ch-hero-dots { gap: 8px; }
    .ch-hero-dot { width: 8px; height: 8px; }
    .ch-hero-slide__desc { display: none; }
}

@media (max-width: 480px) {
    .ch-carousel-hero-banner {
        height: 70svh;
        min-height: 320px;
    }
    .ch-hero-arrow { display: none; }
    .ch-hero-dots { right: 0.75rem; }
}

/* ── Reduced-motion ──────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .ch-hero-track { transition: none; }
    .ch-hero-slide__bg { transition: none; transform: scale(1); }
    .ch-hero-slide__subtitle,
    .ch-hero-slide__title,
    .ch-hero-slide__desc,
    .ch-hero-slide__btn { transition: opacity 0.3s ease; transform: none; }
}
</style>


<!-- ════════════════════════════════════════════════════════
     JAVASCRIPT
     ════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';

    document.querySelectorAll('.ch-carousel-hero-banner').forEach(function (carousel) {
        var track       = carousel.querySelector('.ch-hero-track');
        var slides      = Array.from(carousel.querySelectorAll('.ch-hero-slide'));
        var dots        = Array.from(carousel.querySelectorAll('.ch-hero-dot'));
        var prevBtn     = carousel.querySelector('.ch-hero-arrow--prev');
        var nextBtn     = carousel.querySelector('.ch-hero-arrow--next');
        var progressBar = carousel.querySelector('.ch-hero-progress__bar');

        if (!track || slides.length === 0) return;

        var total      = slides.length;
        var autoplayMs = parseInt(carousel.dataset.autoplay, 10) || 5000;
        var current    = 0;
        var timer      = null;
        var startX     = 0;
        var isDragging = false;

        /* ── Go to slide ──────────────────────────────────── */
        function goTo(index) {
            index = ((index % total) + total) % total;

            slides[current].classList.remove('is-active');
            slides[current].setAttribute('aria-hidden', 'true');
            if (dots[current]) {
                dots[current].classList.remove('is-active');
                dots[current].setAttribute('aria-selected', 'false');
            }

            current = index;

            track.style.transform = 'translateX(-' + (current * 100) + '%)';
            slides[current].classList.add('is-active');
            slides[current].setAttribute('aria-hidden', 'false');
            if (dots[current]) {
                dots[current].classList.add('is-active');
                dots[current].setAttribute('aria-selected', 'true');
            }

            resetProgress();
        }

        /* ── Progress bar ─────────────────────────────────── */
        function resetProgress() {
            if (!progressBar) return;
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
            void progressBar.offsetWidth;
            progressBar.style.transition = 'width ' + autoplayMs + 'ms linear';
            progressBar.style.width = '100%';
        }

        /* ── Autoplay ─────────────────────────────────────── */
        function startAutoplay() {
            stopAutoplay();
            timer = setInterval(function () { goTo(current + 1); }, autoplayMs);
        }
        function stopAutoplay() {
            clearInterval(timer);
            if (progressBar) {
                progressBar.style.transition = 'none';
                progressBar.style.width = '0%';
            }
        }

        /* ── Dot clicks ───────────────────────────────────── */
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(parseInt(dot.dataset.index, 10));
                startAutoplay();
            });
        });

        /* ── Arrow clicks ─────────────────────────────────── */
        if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); startAutoplay(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); startAutoplay(); });

        /* ── Keyboard ─────────────────────────────────────── */
        carousel.setAttribute('tabindex', '0');
        carousel.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft')  { goTo(current - 1); startAutoplay(); }
            if (e.key === 'ArrowRight') { goTo(current + 1); startAutoplay(); }
        });

        /* ── Touch / Swipe ────────────────────────────────── */
        carousel.addEventListener('touchstart', function (e) {
            startX     = e.touches[0].clientX;
            isDragging = true;
        }, { passive: true });

        carousel.addEventListener('touchend', function (e) {
            if (!isDragging) return;
            isDragging = false;
            var diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                goTo(diff > 0 ? current + 1 : current - 1);
                startAutoplay();
            }
        }, { passive: true });

        /* ── Pause on hover ───────────────────────────────── */
        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);

        /* ── Init ─────────────────────────────────────────── */
        goTo(0);
        startAutoplay();
    });
})();
</script>