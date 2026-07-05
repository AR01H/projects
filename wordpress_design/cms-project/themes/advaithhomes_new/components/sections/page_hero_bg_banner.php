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
	<img src="<?php echo $_img; ?>" alt="" loading="eager" fetchpriority="high" />
</div>
<div class="phb-overlay" aria-hidden="true">
	<!-- Full-image hero: S-curve shape filled with gradient so white fades into the photo -->
	<svg class="phb-curve" viewBox="0 0 100 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
		<defs>
			<!-- Warm cream: solid 0→38%, smooth fade by 64% -->
			<linearGradient id="phb-g" x1="0" y1="0" x2="100" y2="0" gradientUnits="userSpaceOnUse">
				<stop offset="0%"   stop-color="#faf8f5" stop-opacity="1"/>
				<stop offset="38%"  stop-color="#faf8f5" stop-opacity="1"/>
				<stop offset="64%"  stop-color="#faf8f5" stop-opacity="0"/>
			</linearGradient>
		</defs>
		<!-- Wide halo — same 3-peak wave, shifted right, very soft -->
		<path d="M 0 0 L 63 0
		         C 66 8, 66 15, 62 22
		         C 58 29, 52 33, 52 42
		         C 52 51, 62 57, 64 65
		         C 66 73, 56 82, 56 91
		         C 56 96, 58 99, 58 100
		         L 0 100 Z"
		      fill="url(#phb-g)" opacity="0.3"/>
		<!-- Primary wave — no stroke, pure smooth fade -->
		<path d="M 0 0 L 55 0
		         C 58 8, 58 15, 54 22
		         C 50 29, 44 33, 44 42
		         C 44 51, 54 57, 56 65
		         C 58 73, 48 82, 48 91
		         C 48 96, 50 99, 50 100
		         L 0 100 Z"
		      fill="url(#phb-g)"/>
	</svg>
	<?php if ( $_circles ) : ?>
		<span class="phb-circle phb-circle--a"></span>
		<span class="phb-circle phb-circle--b"></span>
		<span class="phb-circle phb-circle--c"></span>
	<?php endif; ?>
</div>
