<?php
/**
 * components/sections/page_hero_bg_banner.php
 *
 * Reusable full-bleed hero background layer: photo + left-to-right gradient
 * fade + optional decorative circle blobs.
 *
 * Place this component at the TOP of any hero section that has:
 *   position: relative; overflow: hidden;
 * Content rendered AFTER this component needs:
 *   position: relative; z-index: 2;
 * to appear above the overlay (which sits at z-index 1).
 *
 * Props:
 *   $hero_img  string  Full URL of the hero image. Falls back to home_hero.jpg.
 *   $circles   bool    Render decorative circle blobs? Default: true.
 *
 * Usage:
 *   adn_component( 'sections/page_hero_bg_banner', array(
 *       'hero_img' => $_hero_img,
 *       'circles'  => true,
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$_img     = ( isset( $hero_img ) && '' !== (string) $hero_img )
    ? esc_url( (string) $hero_img )
    : esc_url( get_template_directory_uri() . '/assets/images/backgrounds/home_hero.jpg' );

$_circles = ! isset( $circles ) || (bool) $circles;
?>
<div class="phb-bg">
	<img src="<?php echo $_img; ?>" alt="" loading="eager" />
</div>
<div class="phb-overlay" aria-hidden="true">
	<!-- Full-image hero: S-curve shape filled with gradient so white fades into the photo -->
	<svg class="phb-curve" viewBox="0 0 100 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
		<defs>
			<!-- Gradient: solid white on left, fades to transparent at the curve edge -->
			<linearGradient id="phb-g" x1="0" y1="0" x2="65" y2="0" gradientUnits="userSpaceOnUse">
				<stop offset="0%"   stop-color="white" stop-opacity="1"/>
				<stop offset="46%"  stop-color="white" stop-opacity="1"/>
				<stop offset="100%" stop-color="white" stop-opacity="0"/>
			</linearGradient>
		</defs>
		<!-- Outer soft-fade halo — slightly wider S-curve, lower opacity -->
		<path d="M 0 0 L 72 0 C 70 18, 54 34, 62 50 C 70 66, 56 82, 60 100 L 0 100 Z" fill="url(#phb-g)" opacity="0.45"/>
		<!-- Primary S-curve panel — gradient fades white into the image at the curve boundary -->
		<path d="M 0 0 L 60 0 C 58 18, 42 34, 50 50 C 58 66, 44 82, 48 100 L 0 100 Z" fill="url(#phb-g)"/>
	</svg>
	<?php if ( $_circles ) : ?>
		<span class="phb-circle phb-circle--a"></span>
		<span class="phb-circle phb-circle--b"></span>
		<span class="phb-circle phb-circle--c"></span>
	<?php endif; ?>
</div>
