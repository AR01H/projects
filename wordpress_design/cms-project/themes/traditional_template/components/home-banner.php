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

	<div class="nt-hero-vintage__inner container">
		<div class="nt-hero-vintage__content">
			<div class="nt-hero-vintage__badges">
				<span class="nt-hero-vintage__badge">100%<br>Natural</span>
				<span class="nt-hero-vintage__badge nt-hero-vintage__badge--green">No Added<br>Sugar</span>
			</div>

			<h1 class="nt-hero-vintage__title">
				<?php echo wp_kses( $title, [ 'br' => [], 'em' => [], 'span' => ['class' => []], 'strong' => [] ] ); ?>
			</h1>

			<p class="nt-hero-vintage__desc">
				<?php echo wp_kses( $description, [ 'br' => [], 'em' => [] ] ); ?>
			</p>

			<a href="<?php echo esc_url( nt_link($btn_url) ); ?>" class="nt-hero-vintage__cta btn-primary">
				<?php echo esc_html($btn_text); ?> &rarr;
			</a>
		</div>

		<div class="nt-hero-vintage__illustration" aria-hidden="true">
			<img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=600&q=80"
				 alt="Traditional sugarcane press machine"
				 class="nt-hero-vintage__machine-img">
		</div>
	</div>

	<!-- Decorative large background text -->
	<div class="nt-hero-vintage__bgtext" aria-hidden="true">Fresh Harvest</div>
</section>
