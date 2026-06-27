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
			<!-- Gradient: solid white 0→65%, fades to transparent at 70% curve edge -->
			<linearGradient id="phb-g" x1="0" y1="0" x2="80" y2="0" gradientUnits="userSpaceOnUse">
				<stop offset="0%"   stop-color="white" stop-opacity="1"/>
				<stop offset="60%"  stop-color="white" stop-opacity="1"/>
				<stop offset="100%" stop-color="white" stop-opacity="0"/>
			</linearGradient>
		</defs>
		<!-- Outer soft-fade halo -->
		<path d="M 0 0 L 82 0 C 80 18, 66 34, 74 50 C 82 66, 68 82, 72 100 L 0 100 Z" fill="url(#phb-g)" opacity="0.45"/>
		<!-- Primary panel - covers ~70vw, S-curve edge fades into image -->
		<path d="M 0 0 L 72 0 C 70 18, 56 34, 64 50 C 72 66, 58 82, 62 100 L 0 100 Z" fill="url(#phb-g)"/>
	</svg>
	<?php if ( $_circles ) : ?>
		<span class="phb-circle phb-circle--a"></span>
		<span class="phb-circle phb-circle--b"></span>
		<span class="phb-circle phb-circle--c"></span>
	<?php endif; ?>
</div>
