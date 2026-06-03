<?php
defined( 'ABSPATH' ) || exit;
$h        = ch_get_home_settings();
$settings = ch_get_settings();
$logo_url = get_template_directory_uri() . '/assets/images/logo.png';
$has_logo = file_exists( get_template_directory() . '/assets/images/logo.png' );
?>

<section id="hero" class="ch-hero fade-up">
	<div class="ch-hero__bubbles" aria-hidden="true">
		<div class="ch-bubble"></div>
		<div class="ch-bubble"></div>
		<div class="ch-bubble"></div>
		<div class="ch-bubble"></div>
		<div class="ch-bubble"></div>
	</div>
	<div class="ch-hero__deco ch-hero__deco--1" aria-hidden="true">🌿</div>
	<div class="ch-hero__deco ch-hero__deco--2" aria-hidden="true">🌿</div>
<div class="ch-floating-leaf ch-fl1" aria-hidden="true" style="top: 22%; left: 34%;">🍋</div>
<div class="ch-floating-leaf ch-fl2" aria-hidden="true" style="bottom: 30%; left: 3%; animation-delay: 2s;">🍃</div>
<div class="ch-floating-leaf ch-fl3" aria-hidden="true" style="top: 5%; left: 8%; animation-delay: 1s;">🌿</div>
<div class="ch-floating-leaf ch-fl5" aria-hidden="true" style="bottom: 24%; right: 16%; animation-delay: 3s;">🍉</div>
<div class="ch-floating-leaf ch-fl15" aria-hidden="true" style="bottom: 40%; right: 8%; animation-delay: 4s;">🫚</div>
<div class="ch-floating-leaf ch-fl18" aria-hidden="true" style="bottom: 8%; right: 5%; animation-delay: 3.8s;">🥭</div>
<div class="ch-floating-leaf ch-fl19" aria-hidden="true" style="top: 45%; left: 22%; animation-delay: 1.2s;">🍇</div>
<div class="ch-floating-leaf ch-fl20" aria-hidden="true" style="top: 0%; right: 5%; animation-delay: 4.5s;">🍍</div>

	<div class="ch-hero__inner">
		<div class="ch-hero__left">
			<div class="ch-hero__tag">
				<?php echo esc_html( $h['hero_tag'] ?? '100% Natural · No Additives · Pressed Live' ); ?>
			</div>

			<h1 class="ch-hero__title">
				<?php echo wp_kses( $h['hero_headline'] ?? "Pressed Fresh.<span class=\"accent\">Served Cool.</span>", [ 'span' => [ 'class' => [] ], 'em' => [], 'br' => [] ] ); ?>
			</h1>

			<div class="ch-hero__brand">
				<?php echo esc_html( $h['hero_brand'] ?? 'The Cane House' ); ?>
			</div>

			<p class="ch-hero__desc">
				<?php echo wp_kses( $h['hero_desc'] ?? 'Fresh sugarcane juice pressed live and blended with authentic cold-pressed fruit extracts &amp; natural botanicals. Build your perfect juice - your way.', [ 'br' => [], 'em' => [], 'strong' => [], 'amp' => [] ] ); ?>
			</p>

			<div class="ch-hero__btns">
				<a href="<?php echo esc_url( ch_normalize_theme_url( $h['hero_cta_url'] ?? '#build' ) ); ?>"
					class="btn-lime">
					<?php echo esc_html( $h['hero_cta_label'] ?? '🥤 Build Your Juice' ); ?>
				</a>
				<a href="<?php echo esc_url( ch_normalize_theme_url( $h['hero_cta2_url'] ?? '#hire' ) ); ?>"
					class="btn-outline">
					<?php echo esc_html( $h['hero_cta2_label'] ?? 'Hire for Events →' ); ?>
				</a>
			</div>

			<div class="ch-hero__badges">
				<?php
				$badges = ch_get_hero_badges();
				foreach ( $badges as $i => $badge ) :
					if ( ! $badge ) continue;
				?>
					<span class="ch-badge-item fade-left" style="transition-delay:<?php echo esc_attr( $i * 0.1 ); ?>s;">
						<?php echo esc_html( $badge ); ?>
					</span>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="ch-hero__right" aria-hidden="true">
			<div class="ch-hero__glow ch-hero__cup-wrap">
				<?php if ( $has_logo ) : ?>
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="The Cane House"
						class="ch-hero__logo-spin" />
				<?php else : ?>
					<div class="ch-hero__logo-placeholder">🌿</div>
				<?php endif; ?>
			</div>
			</div>
	</div>
</section>
