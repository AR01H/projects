<?php
/**
 * Template Name: Our Juices
 */
defined( 'ABSPATH' ) || exit;
get_header();

$sizes       = ch_get_menu_sizes();
$cane_types  = ch_get_cane_types();
$textures    = ch_get_textures();
$flavours    = ch_get_flavours();
$steps       = ch_get_order_steps();
$show_prices = ch_show_prices();
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<section class="ch-page-hero">
	<div class="container">
		<div class="fade-up" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
			<div class="section-tag">Full Menu</div>
			<h1 class="ch-page-hero__title">Our <em>Juices</em></h1>
			<p class="ch-page-hero__desc">Mix and match your perfect cup - every juice is pressed live, just for you.</p>
		</div>
	</div>
</section>

<!-- ── Flavour Showcase ──────────────────────────────────────────────────────── -->
<section style="background:var(--ch-green-bg);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">Signature Blends</div>
			<h2 class="section-title">Find Your <span class="accent">Flavour</span></h2>
			<p class="section-body">From clean and classic to tropical and bold - there's a Cane House juice for every mood.</p>
		</div>
		<div class="ch-juice-showcase-grid fade-up">
			<div class="ch-juice-card">
				<div class="ch-juice-card__img" style="background:linear-gradient(135deg,#c8e830,#9bb800);">
					<span style="font-size:3rem;">🌿</span>
				</div>
				<div class="ch-juice-card__body">
					<h3>Pure Cane</h3>
					<p>Clean, natural sweetness. The classic - no additions, just the freshly pressed cane exactly as nature intended.</p>
					<?php if ( $show_prices ) : ?><span class="ch-juice-card__price">Included</span><?php endif; ?>
				</div>
			</div>
			<div class="ch-juice-card">
				<div class="ch-juice-card__img" style="background:linear-gradient(135deg,#f5e642,#f0c040);">
					<span style="font-size:3rem;">🍋</span>
				</div>
				<div class="ch-juice-card__body">
					<h3>Lemon Blend</h3>
					<p>Zesty and refreshing - our citrus lemon blend adds a sharp brightness to the natural cane sweetness.</p>
					<?php if ( $show_prices ) : ?><span class="ch-juice-card__price">+£0.50</span><?php endif; ?>
				</div>
			</div>
			<div class="ch-juice-card">
				<div class="ch-juice-card__img" style="background:linear-gradient(135deg,#d4a017,#b8861a);">
					<span style="font-size:3rem;">🫚</span>
				</div>
				<div class="ch-juice-card__body">
					<h3>Ginger Zing</h3>
					<p>Warming and invigorating - the sharp heat of fresh ginger perfectly balances the cane's sweetness.</p>
					<?php if ( $show_prices ) : ?><span class="ch-juice-card__price">+£0.50</span><?php endif; ?>
				</div>
			</div>
			<div class="ch-juice-card">
				<div class="ch-juice-card__img" style="background:linear-gradient(135deg,#4a8c2a,#2d5a1b);">
					<span style="font-size:3rem;">🌱</span>
				</div>
				<div class="ch-juice-card__body">
					<h3>Mint Cooler</h3>
					<p>Ultra-refreshing - the ultimate summer drink. Cool mint cuts through the cane for a perfectly balanced sip.</p>
					<?php if ( $show_prices ) : ?><span class="ch-juice-card__price">+£0.50</span><?php endif; ?>
				</div>
			</div>
			<div class="ch-juice-card">
				<div class="ch-juice-card__img" style="background:linear-gradient(135deg,#ff9500,#ff6b00);">
					<span style="font-size:3rem;">🍍</span>
				</div>
				<div class="ch-juice-card__body">
					<h3>Tropical Pineapple</h3>
					<p>Sweet, tropical, and vibrant - pineapple elevates the cane juice experience to something truly exotic.</p>
					<?php if ( $show_prices ) : ?><span class="ch-juice-card__price">+£1.00</span><?php endif; ?>
				</div>
			</div>
			<div class="ch-juice-card">
				<div class="ch-juice-card__img" style="background:linear-gradient(135deg,#e83e8c,#c82060);">
					<span style="font-size:3rem;">🥭</span>
				</div>
				<div class="ch-juice-card__body">
					<h3>Wild Mango</h3>
					<p>Berry-sweet and gorgeous - fresh mango notes turn the classic cane juice into a crowd favourite.</p>
					<?php if ( $show_prices ) : ?><span class="ch-juice-card__price">+£1.00</span><?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ── Full Build Menu (from DB) ────────────────────────────────────────────── -->
<section style="background:var(--ch-white);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">Build Your Cup</div>
			<h2 class="section-title">Choose Your <span class="accent">Options</span></h2>
			<p class="section-body">4 simple steps to your perfect juice. Every combination is freshly pressed to order.</p>
		</div>

		<div class="ch-menu-columns fade-up">

			<!-- Sizes -->
			<div class="ch-menu-column">
				<div class="ch-menu-column__header">
					<span class="ch-menu-col-num">1</span>
					<div>
						<div class="ch-menu-col-title">Size</div>
						<div class="ch-menu-col-sub">Pick your cup</div>
					</div>
				</div>
				<div class="ch-menu-items">
					<?php foreach ( $sizes as $s ) :
						$s = (array) $s;
					?>
						<div class="ch-menu-item<?php echo ! empty( $s['featured'] ) ? ' ch-menu-item--featured' : ''; ?>">
							<span class="ch-menu-item__icon"><?php echo esc_html( $s['icon'] ?? '🥤' ); ?></span>
							<div class="ch-menu-item__info">
								<span class="ch-menu-item__name"><?php echo esc_html( $s['name'] ?? '' ); ?></span>
								<span class="ch-menu-item__desc"><?php echo esc_html( $s['desc'] ?? '' ); ?></span>
							</div>
							<div class="ch-menu-item__right">
								<?php if ( ! empty( $s['badge'] ) ) : ?>
									<span class="ch-menu-item__badge"><?php echo esc_html( $s['badge'] ); ?></span>
								<?php endif; ?>
								<?php if ( $show_prices && ! empty( $s['price'] ) ) : ?>
									<span class="ch-menu-item__price"><?php echo esc_html( $s['price'] ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Cane types -->
			<div class="ch-menu-column">
				<div class="ch-menu-column__header">
					<span class="ch-menu-col-num">2</span>
					<div>
						<div class="ch-menu-col-title">Cane Type</div>
						<div class="ch-menu-col-sub">Your cane</div>
					</div>
				</div>
				<div class="ch-menu-items">
					<?php foreach ( $cane_types as $c ) :
						$c = (array) $c;
					?>
						<div class="ch-menu-item<?php echo ! empty( $c['featured'] ) ? ' ch-menu-item--featured' : ''; ?>">
							<span class="ch-menu-item__icon"><?php echo esc_html( $c['icon'] ?? '🌾' ); ?></span>
							<div class="ch-menu-item__info">
								<span class="ch-menu-item__name"><?php echo esc_html( $c['name'] ?? '' ); ?></span>
								<span class="ch-menu-item__desc"><?php echo esc_html( $c['desc'] ?? '' ); ?></span>
							</div>
							<div class="ch-menu-item__right">
								<?php if ( ! empty( $c['badge'] ) ) : ?><span class="ch-menu-item__badge"><?php echo esc_html( $c['badge'] ); ?></span><?php endif; ?>
								<?php if ( $show_prices && ! empty( $c['price'] ) ) : ?><span class="ch-menu-item__price"><?php echo esc_html( $c['price'] ); ?></span><?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Texture as sub-section -->
				<div class="ch-menu-column__header" style="margin-top:2rem;">
					<span class="ch-menu-col-num">3</span>
					<div>
						<div class="ch-menu-col-title">Texture</div>
						<div class="ch-menu-col-sub">How it's pressed</div>
					</div>
				</div>
				<div class="ch-menu-items">
					<?php foreach ( $textures as $t ) :
						$t = (array) $t;
					?>
						<div class="ch-menu-item<?php echo ! empty( $t['featured'] ) ? ' ch-menu-item--featured' : ''; ?>">
							<span class="ch-menu-item__icon"><?php echo esc_html( $t['icon'] ?? '🥢' ); ?></span>
							<div class="ch-menu-item__info">
								<span class="ch-menu-item__name"><?php echo esc_html( $t['name'] ?? '' ); ?></span>
								<span class="ch-menu-item__desc"><?php echo esc_html( $t['desc'] ?? '' ); ?></span>
							</div>
							<div class="ch-menu-item__right">
								<?php if ( ! empty( $t['badge'] ) ) : ?><span class="ch-menu-item__badge"><?php echo esc_html( $t['badge'] ); ?></span><?php endif; ?>
								<?php if ( ! empty( $t['price'] ) ) : ?><span class="ch-menu-item__price"><?php echo esc_html( $t['price'] ); ?></span><?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Flavours -->
			<div class="ch-menu-column">
				<div class="ch-menu-column__header">
					<span class="ch-menu-col-num">4</span>
					<div>
						<div class="ch-menu-col-title">Flavour</div>
						<div class="ch-menu-col-sub">Your blend</div>
					</div>
				</div>
				<div class="ch-flavour-chips">
					<?php foreach ( $flavours as $fl ) :
						$fl = (array) $fl;
					?>
						<div class="ch-flavour-chip">
							<span class="ch-chip-emoji"><?php echo esc_html( $fl['emoji'] ?? '🌿' ); ?></span>
							<span class="ch-chip-name"><?php echo esc_html( $fl['name'] ?? '' ); ?></span>
							<span class="ch-chip-price"><?php echo esc_html( $fl['desc'] ?? '' ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
				<p style="margin-top:1rem;font-size:0.72rem;color:var(--ch-text-muted);font-style:italic;">* Slight colour and taste variation may occur as different canes are pressed in a shared machine. Contains natural sugars - enjoy responsibly.</p>
			</div>

		</div>
	</div>
</section>

<!-- ── How to Order steps ────────────────────────────────────────────────────── -->
<!-- <section style="background:var(--ch-green-deep);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up" style="color:var(--ch-white);">
			<div class="section-tag" style="color:var(--ch-lime);">Simple Process</div>
			<h2 class="section-title" style="color:var(--ch-white);">How to <span class="accent" style="color:var(--ch-lime);">Order</span></h2>
			<p class="section-body" style="color:rgba(255,255,255,0.7);">Walk up, choose your options, we press it live - done in under 2 minutes.</p>
		</div>
		<div class="ch-steps-grid fade-up">
			<?php foreach ( $steps as $step ) :
				$step = (array) $step;
				$hl   = ! empty( $step['highlight'] );
			?>
				<div class="ch-step-card ch-step-card--dark<?php echo $hl ? ' ch-step-card--highlight' : ''; ?>">
					<div class="ch-step-num<?php echo $hl ? ' ch-step-num--highlight' : ''; ?>"><?php echo esc_html( $step['num'] ?? '' ); ?></div>
					<div class="ch-step-emoji"><?php echo esc_html( $step['emoji'] ?? '' ); ?></div>
					<div class="ch-step-title"><?php echo esc_html( $step['title'] ?? '' ); ?></div>
					<div class="ch-step-desc"><?php echo esc_html( $step['desc'] ?? '' ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section> -->
<div class="ch-contact-page-form">
	<?php get_template_part( 'components/contact-section' ); ?>
</div>

</main>
<?php get_footer(); ?>

