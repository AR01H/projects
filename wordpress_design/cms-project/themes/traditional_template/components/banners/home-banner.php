<?php
/**
 * Home Banner - Vintage Hero Section
 * Matches reference: deep sepia hero with large title, description, and CTA.
 */
defined( 'ABSPATH' ) || exit;

$banners = NT_Data_Provider::get('home_banner');
if ( empty($banners) ) return;
$b = (object) $banners[0];

$title       = $b->title       ?? 'Pure by Nature,<br>Perfected by Time.';
$description = $b->description ?? 'Crafted from the finest sugarcane to bring you nature\'s purest refreshment.';
$btn_text    = $b->btn_text    ?? 'Explore Our Story';
$btn_url     = $b->btn_url     ?? '#our-story';
$bg_image    = $b->image       ?? '';
$video_url   = $b->video_url   ?? '';

if ( ! empty( $video_url ) && strpos( $video_url, 'http' ) !== 0 ) {
	$video_url = get_template_directory_uri() . '/' . ltrim( $video_url, '/' );
}
?>
<section class="app-hero-vintage" id="hero" aria-label="Hero" style="background-color: var(--trad-bg);">
	<?php if ( $bg_image ) : ?>
	<div class="app-hero-vintage__bg" style="background-image: url('<?php echo esc_url($bg_image); ?>'); opacity: 0.1;"></div>
	<?php endif; ?>
	
	<?php if ( !empty($video_url) ) : ?>
	<div class="app-hero-vintage__video" style="position: absolute; inset: 0; z-index: 0; pointer-events: none; opacity: 0.15; -webkit-mask-image: var(--banner); mask-image: var(--banner); -webkit-mask-size: cover; mask-size: cover; -webkit-mask-position: center; mask-position: center;">
		<video autoplay muted loop playsinline style="width: 100%; height: 100%; object-fit: cover;">
			<source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
		</video>
	</div>
	<?php endif; ?>

	<div class="app-hero-vintage__overlay" style="z-index: 1;"></div>

	<!-- Background Animated Leaves & Fruits -->
	<div class="app-vintage-floating" aria-hidden="true" style="top: 15%; left: 30%; font-size: 3rem; filter: saturate(0.8) opacity(0.4);">🌿</div>
	<div class="app-vintage-floating" aria-hidden="true" style="bottom: 25%; left: 5%; animation-delay: 2s; font-size: 2.5rem; filter: saturate(0.8) opacity(0.4);">🍋</div>
	
	<!-- Big floating decorators -->
	<div class="app-vintage-floating app-vintage-floating--large" aria-hidden="true" style="top: 10%; right: 40%; animation-delay: 1.5s; font-size: 5rem; opacity: 0.15; filter: saturate(0.8);">🍎</div>
	<div class="app-vintage-floating app-vintage-floating--large" aria-hidden="true" style="bottom: 15%; left: 45%; animation-delay: 3s; font-size: 7rem; opacity: 0.1; filter: saturate(0.8);">🌿</div>
	
	<!-- Floating Bubbles -->
	<div class="app-vintage-bubbles" aria-hidden="true" style="z-index: 1;">
		<div class="app-v-bubble" style="left: 10%; animation-delay: 0s;"></div>
		<div class="app-v-bubble" style="left: 20%; animation-delay: 2s;"></div>
		<div class="app-v-bubble" style="left: 50%; animation-delay: 4s;"></div>
		<div class="app-v-bubble" style="left: 75%; animation-delay: 1s;"></div>
		<div class="app-v-bubble" style="left: 90%; animation-delay: 3s;"></div>
	</div>

	<!-- Animated Wavy Line -->
	<svg class="app-vintage-animated-line" viewBox="0 0 1000 100" preserveAspectRatio="none" aria-hidden="true" style="z-index: 1;">
		<path class="app-vintage-line-path" d="M0,50 Q250,0 500,50 T1000,50" fill="none" stroke="rgba(201,168,76,0.3)" stroke-width="2" stroke-dasharray="10 10"/>
	</svg>

	<div class="app-hero-vintage__inner container" style="position: relative; z-index: 2;">
		<div class="app-hero-vintage__content">
			<div class="app-hero-vintage__badges" style="margin-bottom: 24px;">
				<span class="app-hero-vintage__badge app-hero-vintage__badge--pill">100% NATURAL • NO ADDITIVES • PRESSED LIVE</span>
			</div>

			<h1 class="app-hero-vintage__title app-hero-vintage__title--3d">
				<?php echo wp_kses( $title, [ 'br' => [], 'em' => [], 'span' => ['class' => []], 'strong' => [] ] ); ?>
			</h1>

			<p class="app-hero-vintage__desc" style="max-width: 500px; line-height: 1.6;">
				<?php echo wp_kses( $description, [ 'br' => [], 'em' => [] ] ); ?>
			</p>

			<div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
				<a href="<?php echo esc_url( App_Helpers::link($btn_url) ); ?>" class="btn-primary">
					<span><?php echo wp_kses( $btn_text, ['br'=>[]] ); ?></span>
					<span class="app-vintage-btn-icon" aria-hidden="true">
						<?php App_Helpers::svg('buttons/icon-order'); ?>
					</span>
				</a>
				<a href="#events-catering" class="btn-secondary">
					Hire for Events &rarr;
				</a>
			</div>
			
			<div class="app-hero-vintage__checks" style="margin-top: 32px; display: flex; gap: 16px; flex-wrap: wrap; font-size: 0.85rem; font-weight: 600; color: var(--trad-green);">
				<?php foreach ( App_Helpers::data('hero_checks', []) as $check ) : ?>
					<span>&#10003; <?php echo esc_html( $check ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="app-hero-vintage__illustration" aria-hidden="true" style="position: relative;">
			<div class="app-vintage-stamp" aria-label="Family Business, Proven Model, Full Support"></div>
			
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>"
				 alt="The Cane House Mascot"
				 class="app-hero-vintage__mascot-img app-no-rough">
		</div>
	</div>

	<!-- Decorative large background text -->
	<div class="app-hero-vintage__bgtext" aria-hidden="true">Fresh Harvest</div>
</section>
