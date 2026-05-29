<?php
$exta_heading = $args['exta_heading'] ?? '';
?>

<style>
/* ── Hero ─────────────────────────────────────────────────────────── */
.nif-portal-section-background-img {
    position: relative;
    display: flex;
    align-items: flex-end;
    overflow: hidden;
    background: var(--client-color-900);
    min-height: clamp(420px, 60vh, 600px);
    padding-top: var(--nav-h);
}

/* Background photo */
.nif-portal-section-background-img .nif-bg-img {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: center top;
    z-index: 0;
    display: block;
}
.nif-bg-img-light { opacity: .52; pointer-events: none; }

/* Dark gradient overlay — strong at bottom so text pops */
.nif-portal-section-background-img .nif-overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    background: linear-gradient(
        to bottom,
        rgba(10,6,2,.10) 0%,
        rgba(10,6,2,.28) 40%,
        rgba(10,6,2,.72) 70%,
        rgba(10,6,2,.90) 100%
    );
}

/* Rooftop SVG */
.nif-portal-section-background-img .nif-rooftops {
    position: absolute;
    bottom: 0; left: 0;
    width: 100%; z-index: 2;
    pointer-events: none;
    opacity: .08;
}

/* Content wrapper */
.nif-portal-section-background-img .nif-inner {
    position: relative;
    z-index: 3;
    width: 100%;
    max-width: 860px;
    padding: clamp(24px, 4vw, 52px) clamp(16px, 4vw, 40px);
}

/* Title */
.nif-portal-section-background-img .nif-content .hero__title {
    font-family: var(--font-display, 'Playfair Display', Georgia, serif);
    color: #fff;
    font-size: clamp(1.6rem, 3.8vw, 3rem);
    font-weight: 700;
    line-height: 1.18;
    margin: 0 0 12px;
    letter-spacing: -.01em;
}
.hero__title em {
    font-style: italic;
    color: var(--client-color-300);
}

/* Description */
.nif-portal-section-background-img .nif-content .hero__desc {
    color: rgba(255,255,255,.82);
    font-size: clamp(.82rem, 1.4vw, .96rem);
    line-height: 1.6;
    margin: 0 0 18px;
    max-width: 520px;
}

/* Action cards row */
.nif-cards-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.nif-card {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,.95);
    border: 1.5px solid rgba(255,255,255,.4);
    border-radius: 10px;
    padding: 10px 18px 10px 12px;
    text-decoration: none;
    color: var(--client-color-900);
    font-size: .88rem;
    font-weight: 600;
    backdrop-filter: blur(6px);
    transition: transform .18s, box-shadow .2s, background .18s;
    white-space: nowrap;
}
.nif-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(234,179,8,.25);
    background: #fff;
}
.nif-card .nif-card-icon {
    flex-shrink: 0;
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    background: var(--client-color-100);
    border-radius: 8px;
    font-size: 1.1rem;
}

/* ── MOBILE: split layout — image strip top, dark panel bottom ── */
@media (max-width: 600px) {
    .nif-portal-section-background-img {
        min-height: 0 !important;
        align-items: stretch !important;
        flex-direction: column;
        padding-top: 0;
    }

    /* Full-size image as top strip */
    .nif-portal-section-background-img .nif-bg-img {
        position: relative !important;
        inset: auto !important;
        width: 100% !important;
        height: 180px !important;
        object-fit: cover !important;
        object-position: center top !important;
        flex-shrink: 0;
        z-index: 0 !important;
    }

    /* Hide the SVG animated bg on mobile */
    .nif-portal-section-background-img .nif-bg-img:not(.nif-bg-img-light) {
        display: none !important;
    }

    /* Kill the full-screen overlay on mobile */
    .nif-portal-section-background-img .nif-overlay {
        display: none !important;
    }

    /* Rooftop SVG also hidden */
    .nif-portal-section-background-img .nif-rooftops {
        display: none !important;
    }

    /* Dark content panel below the image */
    .nif-portal-section-background-img .nif-inner {
        position: relative !important;
        z-index: 1 !important;
        background: var(--client-color-900, #1a0e04);
        padding: 20px 18px 24px !important;
        margin-top: 0 !important;
    }

    .nif-portal-section-background-img .nif-content .hero__title {
        font-size: 1.5rem !important;
        line-height: 1.22 !important;
        margin-bottom: 10px !important;
    }

    .nif-portal-section-background-img .nif-content .hero__desc {
        font-size: .84rem !important;
        line-height: 1.55 !important;
        margin-bottom: 16px !important;
        color: rgba(255,255,255,.75) !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .nif-cards-flex { gap: 8px; }

    .nif-card {
        font-size: .8rem !important;
        padding: 8px 14px 8px 10px !important;
        background: rgba(255,255,255,.92) !important;
    }
    .nif-card .nif-card-icon {
        width: 28px !important; height: 28px !important;
        font-size: .95rem !important;
    }
}

@keyframes nif-type { to { width: 100%; } }
</style>


<!-- ── Hero section ── -->
<section class="nif-portal-section nif-portal-section-background-img">

    <img
        class="nif-bg-img nif-bg-img-light"
        src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/backgrounds/family_background.png' ); ?>"
        alt="<?php echo esc_attr( NIF_HERO_BG_ALT ); ?>"
    />
<img src="<?php echo get_template_directory_uri(); ?>/assets/images/svgs/ph-bg-anim.svg" alt="" aria-hidden="true" class="nif-bg-img">
    <div class="nif-overlay" aria-hidden="true"></div>


    <div class="nif-inner">
        <div class="nif-content">

            <h1 class="hero__title">
                <span><?php echo esc_html( NIF_HERO_TITLE_SPAN ); ?></span><em><?php echo esc_html( NIF_HERO_TITLE_EM ); ?> <?php echo $exta_heading; ?></em>
            </h1>

            <p class="hero__desc">
                <?php echo wp_kses_post( NIF_HERO_DESC ); ?>
            </p>

            <div class="nif-cards-flex">

                <a href="/guides" class="nif-card">
                    <span class="nif-card-icon">🏠</span>
                    <?php echo esc_html( NIF_HERO_CARD1_TEXT ); ?>
                </a>

                <a href="#" class="nif-card">
                    <span class="nif-card-icon">🛡️</span>
                    <?php echo esc_html( NIF_HERO_CARD2_TEXT ); ?>
                </a>

            </div>

        </div>
    </div>

</section>