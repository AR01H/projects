<?php
/**
 * NIF banner — supports multiple variants so different pages get distinct designs.
 *
 * Args:
 *   variant       'photo' (default) | 'gradient' | 'tiles'
 *   exta_heading  extra text appended to the title (e.g. "on Finance")
 *   title         override headline (falls back to NIF_HERO constants)
 *   desc          override description
 *   tiles         (tiles variant) array of [ 'icon','label','href' ]; auto-built from
 *                 parent terms when omitted
 */
defined( 'ABSPATH' ) || exit;

$variant      = $args['variant']      ?? 'photo';
$exta_heading = $args['exta_heading'] ?? '';
$b_desc       = $args['desc']         ?? ( defined( 'NIF_HERO_DESC' ) ? NIF_HERO_DESC : '' );
?>

<?php if ( $variant === 'gradient' ) :
	// ── Latest headlines for the ticker ──────────────────────────────────────
	$ticker_titles = [];
	foreach ( get_posts( [ 'posts_per_page' => 6, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] ) as $_tp ) {
		$ticker_titles[] = get_the_title( $_tp->ID );
	}
	$g_title = $args['title'] ?? 'News & Market Updates';
?>
<style>
.nif-gbanner {
	position: relative;
	overflow: hidden;
	background:
		radial-gradient(120% 140% at 85% 0%, color-mix(in srgb, var(--accent) 22%, transparent) 0%, transparent 55%),
		linear-gradient(135deg, var(--client-color-900) 0%, var(--client-color-700) 55%, var(--client-color-800) 100%);
	color: #fff;
}
.nif-gbanner__inner {
	position: relative; z-index: 2;
	max-width: 1180px; margin-inline: auto;
	padding: clamp(20px,4vw,44px) clamp(16px,4vw,40px) 0;
}
.nif-gbanner__eyebrow {
	display: inline-block;
	font-size: .72rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase;
	color: var(--client-color-300);
	background: rgba(255,255,255,.08);
	border: 1px solid rgba(255,255,255,.16);
	padding: 6px 14px; border-radius: 999px; margin-bottom: 18px;
}
.nif-gbanner__title {
	font-family: var(--font-display, Georgia, serif);
	font-size: clamp(2rem,5vw,3.4rem); font-weight: 700; line-height: 1.1;
	margin: 0 0 14px; letter-spacing: -.015em;
}
.nif-gbanner__title em { font-style: italic; color: var(--client-color-300); }
.nif-gbanner__desc {
	max-width: 560px; color: rgba(255,255,255,.82);
	font-size: clamp(.9rem,1.5vw,1.05rem); line-height: 1.65; margin: 0 0 26px;
}
/* rooftop pattern strip */
.nif-gbanner__rooftops {
	position: absolute; left: 0; right: 0; bottom: 0;
	width: 100%; height: 90px; z-index: 1; opacity: .10; fill: #fff;
	pointer-events: none;
}
/* headline ticker */
.nif-gbanner__ticker {
	position: relative; z-index: 2;
	margin-top: clamp(20px,3vw,34px);
	border-top: 1px solid rgba(255,255,255,.14);
	background: rgba(0,0,0,.18);
	display: flex; align-items: center; gap: 14px;
	padding: 12px clamp(16px,4vw,40px);
	overflow: hidden;
}
.nif-gbanner__ticker-label {
	flex-shrink: 0; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;
	color: var(--client-color-900); background: var(--accent);
	padding: 5px 12px; border-radius: 6px;
}
.nif-gbanner__ticker-track {
	display: flex; gap: 48px; white-space: nowrap;
	animation: nif-ticker 28s linear infinite;
}
.nif-gbanner__ticker:hover .nif-gbanner__ticker-track { animation-play-state: paused; }
.nif-gbanner__ticker-item {
	color: rgba(255,255,255,.85); font-size: .9rem; font-weight: 500;
	display: inline-flex; align-items: center; gap: 10px;
}
.nif-gbanner__ticker-item::before { content: "•"; color: var(--accent); }
@keyframes nif-ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }
@media (max-width: 600px) {
	.nif-gbanner__ticker-label { display: none; }
}
</style>

<!-- <section class="nif-portal-section nif-gbanner" aria-label="<?php echo esc_attr( $g_title ); ?>">
	<div class="nif-gbanner__inner">
		<span class="nif-gbanner__eyebrow"><?php echo esc_html( defined( 'TXT_STAY_INFORMED' ) ? TXT_STAY_INFORMED : 'Stay Informed' ); ?></span>
		<h1 class="nif-gbanner__title"><?php echo esc_html( $g_title ); ?> <em><?php echo esc_html( $exta_heading ); ?></em></h1>
		<p class="nif-gbanner__desc"><?php echo wp_kses_post( $b_desc ); ?></p>
	</div>

	<svg class="nif-gbanner__rooftops" viewBox="0 0 1200 90" preserveAspectRatio="none" aria-hidden="true">
		<path d="M0,90 L0,60 L70,60 L70,44 L104,44 L104,32 L138,32 L138,60 L210,60 L255,28 L300,60 L390,60 L390,48 L450,48 L450,36 L486,36 L486,60 L560,60 L606,22 L652,60 L740,60 L740,46 L800,46 L800,60 L880,60 L926,30 L972,60 L1060,60 L1060,50 L1116,50 L1116,40 L1150,40 L1150,60 L1200,60 L1200,90 Z"/>
	</svg>

	<?php if ( ! empty( $ticker_titles ) ) : ?>
	<div class="nif-gbanner__ticker">
		<span class="nif-gbanner__ticker-label"><?php echo esc_html( defined( 'TXT_LATEST_NEWS' ) ? TXT_LATEST_NEWS : 'Latest' ); ?></span>
		<div class="nif-gbanner__ticker-track">
			<?php // duplicate the list so the loop scrolls seamlessly
			foreach ( array_merge( $ticker_titles, $ticker_titles ) as $tt ) : ?>
			<span class="nif-gbanner__ticker-item"><?php echo esc_html( $tt ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</section> -->

<?php elseif ( $variant === 'tiles' ) :
	// ── Tiles: build from parent terms, fall back to a fixed set ──────────────
	$tiles = $args['tiles'] ?? [];
	if ( empty( $tiles ) && class_exists( 'AH_DB_Helper' ) ) {
		global $wpdb;
		$_pt_tbl = AH_DB_Helper::table( 'taxonomy_parent_terms' );
		$_pts    = $wpdb->get_results( "SELECT name, slug, color, icon_emoji FROM `{$_pt_tbl}` WHERE status = 1 ORDER BY name ASC LIMIT 6" ) ?: [];
		foreach ( $_pts as $_pt ) {
			$tiles[] = [
				'icon'  => function_exists( 'ah_topic_icon' )
					? ah_topic_icon( $_pt->name ?? '', $_pt->slug ?? '', $_pt->icon_emoji ?? '' )
					: ( $_pt->icon_emoji ?: '📁' ),
				'label' => $_pt->name,
				'href'  => home_url( '/multiinfo/' . $_pt->slug . '/' ),
				'color' => $_pt->color ?: '',
			];
		}
	}
	if ( empty( $tiles ) ) {
		$tiles = [
			[ 'icon' => '🏠', 'label' => 'Buying',    'href' => home_url( '/guides/' ) ],
			[ 'icon' => '💷', 'label' => 'Finance',   'href' => home_url( '/mortgages/' ) ],
			[ 'icon' => '⚖️', 'label' => 'Legal',     'href' => home_url( '/guides/' ) ],
			[ 'icon' => '📈', 'label' => 'Investing', 'href' => home_url( '/guides/' ) ],
		];
	}
	$t_title = $args['title'] ?? 'The Information Hub';
?>
<style>
.nif-tbanner {
	position: relative; overflow: hidden;
	padding: calc(var(--nav-h) + clamp(28px,4vw,52px)) 0 clamp(28px,4vw,48px);
	background:
		radial-gradient(100% 120% at 0% 0%, color-mix(in srgb, var(--accent) 14%, transparent), transparent 60%),
		linear-gradient(120deg, var(--client-color-900), var(--client-color-800));
	color: #fff;
}
.nif-tbanner__inner { max-width: 1180px; margin-inline: auto; padding: 0 clamp(16px,4vw,40px); }
.nif-tbanner__eyebrow {
	font-size: .72rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase;
	color: var(--client-color-300); margin-bottom: 10px; display: block;
}
.nif-tbanner__title {
	font-family: var(--font-display, Georgia, serif);
	font-size: clamp(1.8rem,4.5vw,3rem); font-weight: 700; line-height: 1.12;
	margin: 0 0 8px; letter-spacing: -.015em;
}
.nif-tbanner__title em { font-style: italic; color: var(--client-color-300); }
.nif-tbanner__desc { max-width: 560px; color: rgba(255,255,255,.8); font-size: clamp(.88rem,1.4vw,1rem); line-height: 1.6; margin: 0 0 28px; }
.nif-tbanner__tiles {
	display: grid; gap: 14px;
	grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
}
.nif-tbanner__tile {
	--tc: var(--accent);
	display: flex; flex-direction: column; gap: 10px;
	padding: 20px 18px; border-radius: 16px;
	background: rgba(255,255,255,.06);
	border: 1px solid rgba(255,255,255,.12);
	text-decoration: none; color: #fff;
	transition: transform .18s, background .18s, border-color .18s, box-shadow .2s;
}
.nif-tbanner__tile:hover {
	transform: translateY(-4px);
	background: rgba(255,255,255,.1);
	border-color: var(--tc);
	box-shadow: 0 14px 34px rgba(0,0,0,.28);
}
.nif-tbanner__tile-icon {
	width: 52px; height: 52px; border-radius: 13px;
	display: flex; align-items: center; justify-content: center;
	font-size: 1.7rem;
	background: color-mix(in srgb, var(--tc) 24%, var(--client-color-900));
	border: 1px solid color-mix(in srgb, var(--tc) 40%, transparent);
}
.nif-tbanner__tile-label { font-weight: 700; font-size: 1.02rem; }
.nif-tbanner__tile-go { font-size: .78rem; font-weight: 600; color: var(--client-color-300); margin-top: auto; }
@media (max-width: 600px) {
	.nif-tbanner__tiles { grid-template-columns: repeat(2, 1fr); gap: 10px; }
	.nif-tbanner__tile { padding: 16px 14px; }
	.nif-tbanner__tile-icon { width: 44px; height: 44px; font-size: 1.4rem; }
}
</style>

<!-- <section class="nif-portal-section nif-tbanner" aria-label="<?php echo esc_attr( $t_title ); ?>">
	<div class="nif-tbanner__inner">
		<span class="nif-tbanner__eyebrow"><?php echo esc_html( defined( 'TXT_BROWSE_BY_TOPIC' ) ? TXT_BROWSE_BY_TOPIC : 'Browse by Topic' ); ?></span>
		<h1 class="nif-tbanner__title"><?php echo esc_html( $t_title ); ?> <em><?php echo esc_html( $exta_heading ); ?></em></h1>
		<?php if ( $b_desc ) : ?>
		<p class="nif-tbanner__desc"><?php echo wp_kses_post( $b_desc ); ?></p>
		<?php endif; ?>
		<div class="nif-tbanner__tiles">
			<?php foreach ( $tiles as $tile ) : ?>
			<a class="nif-tbanner__tile" href="<?php echo esc_url( $tile['href'] ?? '#' ); ?>"
			   <?php if ( ! empty( $tile['color'] ) ) echo 'style="--tc:' . esc_attr( $tile['color'] ) . '"'; ?>>
				<span class="nif-tbanner__tile-icon"><?php echo esc_html( $tile['icon'] ?? '📁' ); ?></span>
				<span class="nif-tbanner__tile-label"><?php echo esc_html( $tile['label'] ?? '' ); ?></span>
				<span class="nif-tbanner__tile-go">Explore →</span>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</section> -->

<?php else : // ── 'photo' (default) — original family-photo hero ──────────────── ?>

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
    .nif-portal-section-background-img .nif-bg-img:not(.nif-bg-img-light) {
        display: none !important;
    }
    .nif-portal-section-background-img .nif-overlay {
        display: none !important;
    }
    .nif-portal-section-background-img .nif-rooftops {
        display: none !important;
    }
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

<?php endif; ?>
