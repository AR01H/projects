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
<section class="nt-hero-vintage" id="hero" aria-label="Hero">
	<?php if ( $bg_image ) : ?>
	<div class="nt-hero-vintage__bg" style="background-image: url('<?php echo esc_url($bg_image); ?>')"></div>
	<?php endif; ?>
	<div class="nt-hero-vintage__overlay"></div>

	<!-- Background Animated Leaves & Sugarcane -->
	<div class="nt-vintage-floating" aria-hidden="true" style="top: 15%; left: 30%;"><svg width="24" height="24" viewBox="0 0 24 24" fill="var(--trad-green)" opacity="0.4"><path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/></svg></div>
	<div class="nt-vintage-floating" aria-hidden="true" style="bottom: 25%; left: 5%; animation-delay: 2s;"><svg width="32" height="32" viewBox="0 0 24 24" fill="var(--trad-green)" opacity="0.3"><path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/></svg></div>
	
	<!-- Big floating sugarcane decorators -->
	<div class="nt-vintage-floating nt-vintage-floating--large" aria-hidden="true" style="top: 10%; right: 40%; animation-delay: 1.5s; font-size: 5rem; opacity: 0.15; filter: sepia(1) hue-rotate(-50deg) saturate(3);">&#127883;</div>
	<div class="nt-vintage-floating nt-vintage-floating--large" aria-hidden="true" style="bottom: 15%; left: 45%; animation-delay: 3s; font-size: 7rem; opacity: 0.1; filter: sepia(1) hue-rotate(-50deg) saturate(3);">&#127883;</div>
	
	<!-- Floating Bubbles -->
	<div class="nt-vintage-bubbles" aria-hidden="true">
		<div class="nt-v-bubble" style="left: 10%; animation-delay: 0s;"></div>
		<div class="nt-v-bubble" style="left: 20%; animation-delay: 2s;"></div>
		<div class="nt-v-bubble" style="left: 50%; animation-delay: 4s;"></div>
		<div class="nt-v-bubble" style="left: 75%; animation-delay: 1s;"></div>
		<div class="nt-v-bubble" style="left: 90%; animation-delay: 3s;"></div>
	</div>

	<!-- Animated Wavy Line -->
	<svg class="nt-vintage-animated-line" viewBox="0 0 1000 100" preserveAspectRatio="none" aria-hidden="true">
		<path class="nt-vintage-line-path" d="M0,50 Q250,0 500,50 T1000,50" fill="none" stroke="rgba(201,168,76,0.3)" stroke-width="2" stroke-dasharray="10 10"/>
	</svg>

	<div class="nt-hero-vintage__inner container">
		<div class="nt-hero-vintage__content">
			<div class="nt-hero-vintage__badges" style="margin-bottom: 24px;">
				<span class="nt-hero-vintage__badge nt-hero-vintage__badge--pill">100% NATURAL • NO ADDITIVES • PRESSED LIVE</span>
			</div>

			<h1 class="nt-hero-vintage__title">
				<?php echo wp_kses( $title, [ 'br' => [], 'em' => [], 'span' => ['class' => []], 'strong' => [] ] ); ?>
			</h1>

			<p class="nt-hero-vintage__desc" style="max-width: 500px; line-height: 1.6;">
				<?php echo wp_kses( $description, [ 'br' => [], 'em' => [] ] ); ?>
			</p>

			<div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
				<a href="<?php echo esc_url( nt_link($btn_url) ); ?>" class="btn-primary">
					<span><?php echo wp_kses( $btn_text, ['br'=>[]] ); ?></span>
					<span class="nt-vintage-btn-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 10h6 M12 10v6 M12 18h.01"/></svg>
					</span>
				</a>
				<a href="#events-catering" class="btn-secondary">
					Hire for Events &rarr;
				</a>
			</div>
			
			<div class="nt-hero-vintage__checks" style="margin-top: 32px; display: flex; gap: 16px; flex-wrap: wrap; font-size: 0.85rem; font-weight: 600; color: var(--trad-green);">
				<span>&#10003; No Added Sugar</span>
				<span>&#10003; No Preservatives</span>
				<span>&#10003; Pressed Live</span>
				<span>&#10003; Served Chilled</span>
			</div>
		</div>

		<div class="nt-hero-vintage__illustration" aria-hidden="true" style="position: relative;">
			<div class="nt-vintage-stamp" aria-label="Family Business, Proven Model, Full Support"></div>
			
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>"
				 alt="The Cane House Mascot"
				 class="nt-hero-vintage__mascot-img">
		</div>
	</div>

	<!-- Decorative large background text -->
	<div class="nt-hero-vintage__bgtext" aria-hidden="true">Fresh Harvest</div>
</section>
