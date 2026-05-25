<?php
$exta_heading = $args['exta_heading'] ?? '';
?>

<style>

/* ── Hero section ───────────────────────────────────────────────── */
.nif-portal-section-background-img {
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: var(--client-color-900);
    border-radius: var(--r-md);
}

/* ── Background image ───────────────────────────────────────────── */
.nif-portal-section-background-img .nif-bg-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: left center;
    z-index: 0;
    display: block;
}

/* ── Overlay: very subtle - image must show clearly ────────────── */
.nif-portal-section-background-img .nif-overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    /* Light-to-stronger fade only on right side where text sits */
    background: linear-gradient(
        to right,
        color-mix(in srgb, var(--client-color-900) 8%,  transparent) 0%,
        color-mix(in srgb, var(--client-color-900) 20%, transparent) 30%,
        color-mix(in srgb, var(--client-color-900) 55%, transparent) 52%,
        color-mix(in srgb, var(--client-color-900) 70%, transparent) 100%
    );
}

/* ── Rooftop silhouette ─────────────────────────────────────────── */
.nif-portal-section-background-img .nif-rooftops {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    z-index: 2;
    pointer-events: none;
    opacity: 0.10;
}

/* ── Inner wrapper ──────────────────────────────────────────────── */
.nif-portal-section-background-img .nif-inner {
    position: relative;
    z-index: 3;
    width: 100%;
    margin: 0 auto;
    padding: 30px;
    display: flex;
}

/* ── Headline - Playfair Display serif ─────────────────────────── */
.nif-portal-section-background-img .nif-content .hero__title {
    font-family: 'Playfair Display', Georgia, serif;
    color: #fff;
    font-size: clamp(1.8rem, 3.4vw, 2.8rem);
    font-weight: 700;
    line-height: 1.18;
    margin: 0 0 18px;
    letter-spacing: -0.01em;
}

/* ── Description ────────────────────────────────────────────────── */
.nif-portal-section-background-img .nif-content .hero__desc {
    color: rgba(255, 255, 255, 0.88);
    font-size: 15px;
    line-height: 1.5;
    margin: 0 0 16px;
    font-weight: 400;
}

.nif-cards-flex {
    display: flex;
    flex-wrap: wrap;
    gap:10px;
}


.nif-card {
    background: rgba(255, 255, 255, 0.96);
    border-radius: var(--radius-sm);
    padding: 5px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    color: var(--client-color-900);
    font-size: 15px;
    font-weight: 600;
    border: 1.5px solid transparent;
    transition: transform 0.18s ease, box-shadow 0.2s ease, border-color 0.18s ease;
    backdrop-filter: blur(4px);
    width:max-content;
}
.nif-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px color-mix(in srgb, var(--client-color-500) 28%, transparent);
    border-color: var(--client-color-500);
    background: #fff;
}

.nif-card .nif-card-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in srgb, var(--client-color-50) 60%, transparent);
    border-radius: var(--radius-sm);
    font-size: 21px;
}

/* ── Responsive ─────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .nif-portal-section-background-img .nif-overlay {
        background: linear-gradient(
            to bottom,
            color-mix(in srgb, var(--client-color-900) 5%,  transparent) 0%,
            color-mix(in srgb, var(--client-color-900) 30%, transparent) 35%,
            color-mix(in srgb, var(--client-color-900) 72%, transparent) 58%,
            color-mix(in srgb, var(--client-color-900) 82%, transparent) 100%
        );
    }
    .nif-portal-section-background-img .nif-inner {
        justify-content: center;
    }
    .nif-portal-section-background-img .nif-content {
        width: 100%;
    }
}

@media (max-width: 560px) {
    .whole-page-card .nif-portal-section-background-img .nif-content .hero__title{
        font-size: 3.0rem;
    }
    .nif-portal-section-background-img .nif-content .hero__desc{
        font-size: 1.0rem;
        line-height: 1.3rem;
    }
    .nif-portal-section-background-img .hero__title {
        font-size: 1.0rem;
    }
}
.hero__title em {
    font-style: italic;
    color: var(--client-color-50);
    display: block;
    width: 0;
    animation: nif-type 2s steps(20, end) forwards;
}

.nif-portal-section-background-img .nif-content {
    width: 100%;
    min-width: 0;
}


@keyframes nif-type {
    to { width: 100%; }
}
</style>


<!-- ── Hero section ── -->
<section class="nif-portal-section nif-portal-section-background-img">

    <img
        class="nif-bg-img"
        src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/backgrounds/family_background.png' ); ?>"
        alt="<?php echo esc_attr( NIF_HERO_BG_ALT ); ?>"
    />

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