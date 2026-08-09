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
?>
<section class="app-hero-vintage" id="hero" aria-label="Hero">
	<?php if ( $bg_image ) : ?>
	<div class="app-hero-vintage__bg" style="background-image: url('<?php echo esc_url($bg_image); ?>')"></div>
	<?php endif; ?>
	<div class="app-hero-vintage__overlay"></div>

	<!-- Background Animated Leaves & Sugarcane -->
	<div class="app-vintage-floating" aria-hidden="true" style="top: 15%; left: 30%;"><svg width="24" height="24" viewBox="0 0 24 24" fill="var(--trad-green)" opacity="0.4"><path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/></svg></div>
	<div class="app-vintage-floating" aria-hidden="true" style="bottom: 25%; left: 5%; animation-delay: 2s;"><svg width="32" height="32" viewBox="0 0 24 24" fill="var(--trad-green)" opacity="0.3"><path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/></svg></div>
	
	<!-- Big floating sugarcane decorators -->
	<div class="app-vintage-floating app-vintage-floating--large" aria-hidden="true" style="top: 10%; right: 40%; animation-delay: 1.5s; font-size: 5rem; opacity: 0.15; filter: sepia(1) hue-rotate(-50deg) saturate(3);">&#127883;</div>
	<div class="app-vintage-floating app-vintage-floating--large" aria-hidden="true" style="bottom: 15%; left: 45%; animation-delay: 3s; font-size: 7rem; opacity: 0.1; filter: sepia(1) hue-rotate(-50deg) saturate(3);">&#127883;</div>
	
	<!-- Floating Bubbles -->
	<div class="app-vintage-bubbles" aria-hidden="true">
		<div class="app-v-bubble" style="left: 10%; animation-delay: 0s;"></div>
		<div class="app-v-bubble" style="left: 20%; animation-delay: 2s;"></div>
		<div class="app-v-bubble" style="left: 50%; animation-delay: 4s;"></div>
		<div class="app-v-bubble" style="left: 75%; animation-delay: 1s;"></div>
		<div class="app-v-bubble" style="left: 90%; animation-delay: 3s;"></div>
	</div>

	<!-- Animated Wavy Line -->
	<svg class="app-vintage-animated-line" viewBox="0 0 1000 100" preserveAspectRatio="none" aria-hidden="true">
		<path class="app-vintage-line-path" d="M0,50 Q250,0 500,50 T1000,50" fill="none" stroke="rgba(201,168,76,0.3)" stroke-width="2" stroke-dasharray="10 10"/>
	</svg>

	<div class="app-hero-vintage__inner container">
		<div class="app-hero-vintage__content">
			<div class="app-hero-vintage__badges" style="margin-bottom: 24px;">
				<span class="app-hero-vintage__badge app-hero-vintage__badge--pill">100% NATURAL • NO ADDITIVES • PRESSED LIVE</span>
			</div>

			<h1 class="app-hero-vintage__title">
				<?php echo wp_kses( $title, [ 'br' => [], 'em' => [], 'span' => ['class' => []], 'strong' => [] ] ); ?>
			</h1>

			<p class="app-hero-vintage__desc" style="max-width: 500px; line-height: 1.6;">
				<?php echo wp_kses( $description, [ 'br' => [], 'em' => [] ] ); ?>
			</p>

			<div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
				<a href="<?php echo esc_url( App_Helpers::link($btn_url) ); ?>" class="btn-primary">
					<span><?php echo wp_kses( $btn_text, ['br'=>[]] ); ?></span>
					<span class="app-vintage-btn-icon" aria-hidden="true">
						<?php App_Helpers::svg('icon-order'); ?>
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
