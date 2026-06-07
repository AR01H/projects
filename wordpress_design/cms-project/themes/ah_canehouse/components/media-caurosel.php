<?php
/**
 * Template Part: CH Media Carousel (Card Style)
 * File:          components/showcase-carousel.php
 *
 * USAGE:
 *   get_template_part( 'components/showcase-carousel', null, [
 *       'tag'   => 'See It Live',
 *       'title' => 'What Our Clients Say',
 *       'body'  => 'Real stories from real people.',
 *       'bg'    => 'var(--client-color-12)',
 *       'id'    => 'sc-testimonials',
 *       'items' => [
 *           [
 *               'type'        => 'youtube',   // 'youtube' | 'video' | 'image'
 *               'src'         => 'https://youtu.be/VIDEO_ID',
 *               'title'       => 'How TAP Academy Helped Me',
 *               'description' => 'A student shares their placement story.',
 *               'aspect'      => 'portrait',  // 'portrait' | 'landscape' | 'square'
 *           ],
 *           [
 *               'type'        => 'image',
 *               'src'         => 'https://example.com/photo.jpg',
 *               'title'       => 'Our Farm',
 *               'description' => 'From field to bottle.',
 *               'aspect'      => 'portrait',
 *           ],
 *       ],
 *   ]);
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tag   = $args['tag']   ?? '';
$title = $args['title'] ?? '';
$body  = $args['body']  ?? '';
$bg    = $args['bg']    ?? 'transparent';
$id    = $args['id']    ?? 'sc-default';
$items = $args['items'] ?? [];

if ( empty( $items ) ) return;

$carousel_id = 'ch_mc_' . preg_replace( '/[^a-z0-9_-]/', '', strtolower( $id ) );

$allowed_types   = [ 'image', 'video', 'youtube' ];
$allowed_aspects = [ 'portrait', 'square', 'landscape' ];

$items_safe = array_map( static function ( $item ) use ( $allowed_types, $allowed_aspects ) {
    return [
        'type'        => in_array( $item['type']   ?? '', $allowed_types,   true ) ? $item['type']   : 'image',
        'src'         => esc_url_raw( $item['src']         ?? '' ),
        'title'       => esc_html(   $item['title']       ?? '' ),
        'description' => esc_html(   $item['description'] ?? '' ),
        'aspect'      => in_array( $item['aspect'] ?? '', $allowed_aspects, true ) ? $item['aspect'] : 'portrait',
    ];
}, $items );

$items_json  = wp_json_encode( $items_safe );
$id_json     = wp_json_encode( $carousel_id );

$aria_label      = esc_attr( strip_tags( $title ) ?: 'Media carousel' );
$carousel_id_esc = esc_attr( $carousel_id );
?>

<section
    class="ch_mc2_section"
    aria-label="<?php echo $aria_label; ?>"
    style="background:<?php echo esc_attr( $bg ); ?>;"
>

    <?php get_template_part( 'components/section-header', null, [
        'tag'   => $tag,
        'title' => $title,
        'body'  => $body,
    ] ); ?>

    <div
        id="<?php echo $carousel_id_esc; ?>"
        class="ch_mc2_root"
        data-carousel-id="<?php echo $carousel_id_esc; ?>"
        aria-roledescription="carousel"
        aria-label="<?php echo $aria_label; ?>"
    >
        <!-- Track -->
        <div class="ch_mc2_viewport">
            <div class="ch_mc2_track" role="list">
                <!-- Cards injected by JS -->
            </div>
        </div>

        <!-- Arrow buttons -->
        <button class="ch_mc2_arrow ch_mc2_arrow_prev" aria-label="Previous slide">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>
        <button class="ch_mc2_arrow ch_mc2_arrow_next" aria-label="Next slide">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 6 15 12 9 18"/>
            </svg>
        </button>

        <!-- Dots -->
        <div class="ch_mc2_dots" role="tablist" aria-label="Slide navigation"></div>

    </div>

    <!-- Modal overlay for video playback -->
    <div class="ch_mc2_modal" id="<?php echo $carousel_id_esc; ?>_modal" role="dialog" aria-modal="true" aria-label="Video player">
        <div class="ch_mc2_modal_backdrop"></div>
        <div class="ch_mc2_modal_box">
            <button class="ch_mc2_modal_close" aria-label="Close video">&times;</button>
            <div class="ch_mc2_modal_media"></div>
        </div>
    </div>

    <script>
        window.CH_MC2_DATA = window.CH_MC2_DATA || {};
        window.CH_MC2_DATA[<?php echo $id_json; ?>] = <?php echo $items_json; ?>;
    </script>
</section>

<script>
(function () {
    'use strict';

    /* ── Helpers ─────────────────────────────────────────────────── */
    function ytId(url) {
        const m = url.match(/(?:v=|youtu\.be\/|embed\/)([A-Za-z0-9_-]{11})/);
        return m ? m[1] : null;
    }
    function esc(s) { return String(s).replace(/"/g, '&quot;'); }

    /* ── Aspect ratio class ──────────────────────────────────────── */
    function aspectClass(aspect) {
        if (aspect === 'square')    return 'ch_mc2_thumb_square';
        if (aspect === 'landscape') return 'ch_mc2_thumb_landscape';
        return ''; /* portrait is default */
    }

    /* ── Build thumbnail URL ──────────────────────────────────────── */
    function thumbSrc(item) {
        if (item.type === 'youtube') {
            const vid = ytId(item.src);
            return vid ? `https://img.youtube.com/vi/${vid}/hqdefault.jpg` : '';
        }
        if (item.type === 'image') return item.src;
        return ''; /* video: no thumb, use poster gradient */
    }

    /* ── Build one card HTML ─────────────────────────────────────── */
    function buildCard(item, index) {
        const thumb = thumbSrc(item);
        const ac    = aspectClass(item.aspect);
        const hasPlay = item.type === 'youtube' || item.type === 'video';

        const thumbEl = thumb
            ? `<img src="${esc(thumb)}" alt="${esc(item.title || '')}" loading="lazy" class="ch_mc2_thumb_img" onload="this.closest('.ch_mc2_thumb').classList.add('loaded')">`
            : `<div class="ch_mc2_thumb_video_placeholder"></div>`;

        const playBtn = hasPlay
            ? `<div class="ch_mc2_play_btn" aria-hidden="true">
                   <svg viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3" fill="currentColor"/></svg>
               </div>`
            : '';

        const typeBadge = item.type === 'youtube'
            ? `<span class="ch_mc2_badge ch_mc2_badge_yt">
                   <svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 0 0 1.46 6.42 29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.96-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon fill="#fff" points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
               </span>`
            : item.type === 'video'
            ? `<span class="ch_mc2_badge ch_mc2_badge_vid">
                   <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
               </span>`
            : '';

        const titleEl = item.title
            ? `<h3 class="ch_mc2_card_title">${esc(item.title)}</h3>` : '';
        const descEl = item.description
            ? `<p class="ch_mc2_card_desc">${esc(item.description)}</p>` : '';

        return `
        <article
            class="ch_mc2_card"
            role="listitem"
            aria-label="Slide ${index + 1}"
            data-type="${esc(item.type)}"
            data-src="${esc(item.src)}"
            data-index="${index}"
            tabindex="0"
        >
            <div class="ch_mc2_thumb ${ac}">
                ${thumbEl}
                ${playBtn}
                ${typeBadge}
                <div class="ch_mc2_thumb_shimmer"></div>
            </div>
            ${(titleEl || descEl) ? `<div class="ch_mc2_card_body">${titleEl}${descEl}</div>` : ''}
        </article>`;
    }

    /* ════════════════════════════════════════════════════════════
       CAROUSEL CLASS
       ════════════════════════════════════════════════════════════ */
    class ChMC2 {
        constructor(root, items) {
            this.root   = root;
            this.id     = root.dataset.carouselId;
            this.items  = items;
            this.cur    = 0;
            this.modal  = document.getElementById(this.id + '_modal');

            this.track  = root.querySelector('.ch_mc2_track');
            this.dotsWrap = root.querySelector('.ch_mc2_dots');
            this.prevBtn  = root.querySelector('.ch_mc2_arrow_prev');
            this.nextBtn  = root.querySelector('.ch_mc2_arrow_next');

            this._tx = 0;
            this._build();
            this._bindModal();
        }

        /* Build cards + dots */
        _build() {
            this.track.innerHTML = this.items.map((item, i) => buildCard(item, i)).join('');
            this.cards = [...this.track.querySelectorAll('.ch_mc2_card')];

            /* Dots */
            const totalDots = this._dotCount();
            for (let i = 0; i < totalDots; i++) {
                const d = document.createElement('button');
                d.className = 'ch_mc2_dot';
                d.setAttribute('role', 'tab');
                d.setAttribute('aria-label', `Go to slide ${i + 1}`);
                d.addEventListener('click', () => this.goTo(i * this._visible()));
                this.dotsWrap.appendChild(d);
            }
            this.dots = [...this.dotsWrap.querySelectorAll('.ch_mc2_dot')];

            /* Arrows */
            this.prevBtn.addEventListener('click', () => this.prev());
            this.nextBtn.addEventListener('click', () => this.next());

            /* Card click → open modal or lightbox */
             this.track.addEventListener('click', e => {
                const card = e.target.closest('.ch_mc2_card');
                if (!card || card.classList.contains('is-playing')) return;
                const type  = card.dataset.type;
                const src   = card.dataset.src;
                const thumb = card.querySelector('.ch_mc2_thumb');
                if (type === 'youtube') {
                    const vid = ytId(src);
                    if (!vid) return;
                    thumb.innerHTML = '<iframe src="https://www.youtube.com/embed/' + vid + '?autoplay=1&rel=0&playsinline=1" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute;inset:0;width:100%;height:100%;border:none;"></iframe>';
                    card.classList.add('is-playing');
                } else if (type === 'video') {
                    const v = document.createElement('video');
                    v.src = src; v.controls = true; v.autoplay = true; v.playsInline = true;
                    v.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;background:#000;';
                    thumb.innerHTML = '';
                    thumb.appendChild(v);
                    card.classList.add('is-playing');
                    v.play().catch(() => {});
                }
            })

            /* Keyboard on cards */
            this.track.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    const card = e.target.closest('.ch_mc2_card');
                    if (card) card.click();
                }
            });

            /* Touch swipe */
            const vp = this.root.querySelector('.ch_mc2_viewport');
            let sx = 0, dx = 0;
            vp.addEventListener('touchstart', e => { sx = e.touches[0].clientX; }, { passive: true });
            vp.addEventListener('touchmove',  e => { dx = e.touches[0].clientX - sx; }, { passive: true });
            vp.addEventListener('touchend',   () => {
                if (Math.abs(dx) > 40) { dx < 0 ? this.next() : this.prev(); }
                dx = 0;
            });

            /* Arrow key on root */
            this.root.addEventListener('keydown', e => {
                if (e.key === 'ArrowLeft')  this.prev();
                if (e.key === 'ArrowRight') this.next();
            });

            /* Resize → recalculate */
            window.addEventListener('resize', () => this._updateTrack(false));

            this._updateTrack(false);
            this._updateDots();
            this._updateArrows();
        }

        /* How many cards fit in viewport */
        _visible() {
            const vp = this.root.querySelector('.ch_mc2_viewport');
            const cs = getComputedStyle(this.root);
            return parseInt(cs.getPropertyValue('--ch-mc2-visible')) || 3;
        }

        _dotCount() {
            return Math.ceil(this.items.length / this._visible());
        }

        goTo(index, animate = true) {
            const max = Math.max(0, this.items.length - this._visible());
            this.cur  = Math.min(Math.max(0, index), max);
            this._updateTrack(animate);
            this._updateDots();
            this._updateArrows();
        }

        prev() { this.goTo(this.cur - this._visible()); }
        next() { this.goTo(this.cur + this._visible()); }

        _updateTrack(animate) {
            const vp   = this.root.querySelector('.ch_mc2_viewport');
            const vis  = this._visible();
            const cs   = getComputedStyle(this.root);
            const gap  = parseFloat(cs.getPropertyValue('--ch-mc2-gap')) || 20;
            const cardW = (vp.offsetWidth - gap * (vis - 1)) / vis;
            const offset = this.cur * (cardW + gap);

            if (!animate) this.track.style.transition = 'none';
            this.track.style.transform = `translateX(-${offset}px)`;
            if (!animate) {
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    this.track.style.transition = '';
                }));
            }
        }

        _updateDots() {
            const dotIndex = Math.floor(this.cur / this._visible());
            this.dots.forEach((d, i) => {
                d.classList.toggle('is-active', i === dotIndex);
                d.setAttribute('aria-selected', i === dotIndex ? 'true' : 'false');
            });
        }

        _updateArrows() {
            const max = Math.max(0, this.items.length - this._visible());
            this.prevBtn.disabled = this.cur <= 0;
            this.nextBtn.disabled = this.cur >= max;
        }

        /* ── Modal ──────────────────────────────────────────────── */
        _bindModal() {
            if (!this.modal) return;
            const backdrop = this.modal.querySelector('.ch_mc2_modal_backdrop');
            const closeBtn = this.modal.querySelector('.ch_mc2_modal_close');
            backdrop.addEventListener('click', () => this._closeModal());
            closeBtn.addEventListener('click', () => this._closeModal());
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && this.modal.classList.contains('is-open')) {
                    this._closeModal();
                }
            });
        }

        _openModal(type, src) {
            if (!this.modal) return;
            const mediaEl = this.modal.querySelector('.ch_mc2_modal_media');
            mediaEl.innerHTML = '';

            if (type === 'youtube') {
                const vid = ytId(src);
                if (!vid) return;
                const iframe = document.createElement('iframe');
                iframe.src = `https://www.youtube.com/embed/${vid}?autoplay=1&rel=0&playsinline=1`;
                iframe.allow = 'autoplay; fullscreen; picture-in-picture';
                iframe.setAttribute('allowfullscreen', '');
                mediaEl.appendChild(iframe);
            } else if (type === 'video') {
                const video = document.createElement('video');
                video.src = src;
                video.controls = true;
                video.autoplay = true;
                video.playsInline = true;
                mediaEl.appendChild(video);
            } else if (type === 'image') {
                const img = document.createElement('img');
                img.src = src;
                img.alt = '';
                mediaEl.appendChild(img);
            }

            this.modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        _closeModal() {
            if (!this.modal) return;
            const mediaEl = this.modal.querySelector('.ch_mc2_modal_media');
            mediaEl.innerHTML = ''; /* stops video/audio */
            this.modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }

    /* ── Init ────────────────────────────────────────────────────── */
    function init() {
        document.querySelectorAll('.ch_mc2_root').forEach(root => {
            const id    = root.dataset.carouselId;
            const items = (window.CH_MC2_DATA && window.CH_MC2_DATA[id]) || [];
            if (!items.length) return;
            new ChMC2(root, items);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

<style>

/* ── Root ───────────────────────────────────────────────────── */
.ch_mc2_root {
    position: relative;
    --ch-mc2-visible: 3;
    --ch-mc2-gap: 14px;
}

/* ── Viewport ───────────────────────────────────────────────── */
.ch_mc2_viewport {
    overflow: hidden;
}

/* ── Track ──────────────────────────────────────────────────── */
.ch_mc2_track {
    display: flex;
    gap: var(--ch-mc2-gap);
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
    align-items: flex-start; /* cards don't stretch to tallest */
}

/* ── Card ───────────────────────────────────────────────────── */
.ch_mc2_card {
    flex: 0 0 calc(
        (100% - (var(--ch-mc2-visible) - 1) * var(--ch-mc2-gap))
        / var(--ch-mc2-visible)
    );
    min-width: 0;
    border-radius: var(--ch-radius);
    overflow: hidden;
    background: var(--client-color-11);
    border: 1px solid rgba(168,217,110,0.25);
    box-shadow: 0 2px 12px var(--client-color-18);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    outline: none;
}

.ch_mc2_card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 32px var(--client-color-19);
}

.ch_mc2_card:focus-visible {
    box-shadow: 0 0 0 3px var(--client-color-7), 0 10px 32px var(--client-color-19);
}

/* ── Thumbnail area ─────────────────────────────────────────── */
.ch_mc2_thumb {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    background: var(--client-color-13);
    overflow: hidden;
}

.ch_mc2_thumb_square    { aspect-ratio: 1 / 1; }
.ch_mc2_thumb_landscape { aspect-ratio: 16 / 9; }

.ch_mc2_thumb_img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    position: absolute; inset: 0;
    transition: transform 0.5s ease;
}

.ch_mc2_card:hover .ch_mc2_thumb_img {
    transform: scale(1.05);
}

/* Video placeholder (no thumb) */
.ch_mc2_thumb_video_placeholder {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, var(--client-color-13) 0%, #2d5a1b 100%);
}

/* ── Shimmer ────────────────────────────────────────────────── */
@keyframes ch_mc2_shimmer {
    0%   { background-position: -400px 0; }
    100% { background-position: 400px 0; }
}

.ch_mc2_thumb_shimmer {
    position: absolute; inset: 0;
    background: linear-gradient(90deg, #d8f0b0 25%, #e4f5cc 50%, #d8f0b0 75%);
    background-size: 800px 100%;
    animation: ch_mc2_shimmer 1.4s infinite linear;
    pointer-events: none;
    transition: opacity 0.4s ease;
    z-index: 1;
}

.ch_mc2_thumb.loaded .ch_mc2_thumb_shimmer {
    opacity: 0;
}

/* ── Play button ────────────────────────────────────────────── */
.ch_mc2_play_btn {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    pointer-events: none;
}

.ch_mc2_play_btn svg {
    width: 40px; height: 40px;
    background: rgba(200,232,48,0.88);
    border-radius: 50%;
    padding: 10px;
    color: var(--client-color-1);
    box-shadow: 0 4px 24px rgba(0,0,0,0.3);
    transition: transform 0.25s ease, background 0.25s ease;
}

.ch_mc2_card:hover .ch_mc2_play_btn svg {
    transform: scale(1.1);
    background: var(--client-color-7);
}

/* ── Type badge ─────────────────────────────────────────────── */
.ch_mc2_badge {
    position: absolute;
    top: 10px; right: 10px;
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(8px);
    z-index: 4;
}

.ch_mc2_badge_yt  { background: rgba(255,0,0,0.85); }
.ch_mc2_badge_vid { background: rgba(26,58,15,0.75); border: 1px solid rgba(255,255,255,0.2); }

.ch_mc2_badge svg { display: block; }

/* ── Card body (title + desc) ───────────────────────────────── */
.ch_mc2_card_body {
    padding: 10px 12px 12px;
}

.ch_mc2_card_title {
    font-family: var(--ch-font-display);
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--client-color-1);
    margin: 0 0 4px;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ch_mc2_card_desc {
    font-size: 0.72rem;
    color: var(--client-color-16);
    margin: 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ── Arrows ─────────────────────────────────────────────────── */
.ch_mc2_arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-60%); /* offset to center on thumb, not full card */
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 1.5px solid var(--client-color-20);
    background: rgba(253,255,248,0.92);
    color: var(--client-color-1);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 12px var(--client-color-18);
    transition: var(--ch-transition);
    z-index: 10;
}

.ch_mc2_arrow svg { width: 14px; height: 14px; }

.ch_mc2_arrow:hover {
    background: var(--client-color-7);
    border-color: var(--client-color-7);
    transform: translateY(-60%) scale(1.08);
    box-shadow: 0 4px 18px rgba(200,232,48,0.45);
}

.ch_mc2_arrow:disabled {
    opacity: 0.3;
    cursor: not-allowed;
    transform: translateY(-60%);
    pointer-events: none;
}

.ch_mc2_arrow_prev { left: -22px; }
.ch_mc2_arrow_next { right: -22px; }

/* ── Dots ───────────────────────────────────────────────────── */
.ch_mc2_dots {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-top: 16px;
}

.ch_mc2_dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    border: 1.5px solid var(--client-color-4);
    background: var(--client-color-20);
    cursor: pointer;
    padding: 0;
    transition: all 0.3s ease;
}

.ch_mc2_dot.is-active {
    width: 26px;
    border-radius: var(--ch-radius-pill);
    background: var(--client-color-7);
    border-color: var(--client-color-7);
}

/* ── Modal ──────────────────────────────────────────────────── */
.ch_mc2_modal {
    display: none;
    position: fixed; inset: 0;
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.ch_mc2_modal.is-open {
    display: flex;
}

.ch_mc2_modal_backdrop {
    position: absolute; inset: 0;
    background: rgba(10,25,6,0.82);
    backdrop-filter: blur(4px);
}

.ch_mc2_modal_box {
    position: relative;
    z-index: 1;
    width: min(90vw, 860px);
    max-height: 90vh;
    border-radius: var(--ch-radius);
    overflow: hidden;
    background: #000;
    box-shadow: 0 24px 80px rgba(0,0,0,0.6);
}

.ch_mc2_modal_close {
    position: absolute;
    top: 12px; right: 14px;
    z-index: 10;
    background: rgba(255,255,255,0.15);
    border: none;
    color: #fff;
    font-size: 1.4rem;
    line-height: 1;
    width: 36px; height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
}

.ch_mc2_modal_close:hover { background: rgba(255,255,255,0.3); }

.ch_mc2_modal_media {
    width: 100%;
    aspect-ratio: 16 / 9;
}

.ch_mc2_modal_media iframe,
.ch_mc2_modal_media video {
    width: 100%; height: 100%;
    border: none; display: block;
}

.ch_mc2_modal_media img {
    width: 100%; height: 100%;
    object-fit: contain;
    display: block;
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 900px) {
    .ch_mc2_root { --ch-mc2-visible: 2; }
    .ch_mc2_arrow_prev { left: -16px; }
    .ch_mc2_arrow_next { right: -16px; }
}

@media (max-width: 560px) {
    .ch_mc2_root { --ch-mc2-visible: 1; --ch-mc2-gap: 0px; }
    .ch_mc2_section { padding: 28px 12px; }
    .ch_mc2_arrow_prev { left: 4px; }
    .ch_mc2_arrow_next { right: 4px; }
}
</style>