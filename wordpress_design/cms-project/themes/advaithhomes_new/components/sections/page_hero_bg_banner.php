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
    ? esc_url( adn_versioned_url( (string) $hero_img ) )
    : esc_url( adn_versioned_url( get_template_directory_uri() . THEME_DEFAULT_HERO_IMG ) );

$is_home  = isset( $is_home ) ? (bool) $is_home : false;
$_circles = ! isset( $circles ) || (bool) $circles;
$wrapper_class = $is_home ? 'phb-wrapper phb-standard' : 'phb-wrapper phb-premium';
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>">
	<div class="phb-bg">
		<img src="<?php echo $_img; ?>" alt="" loading="eager" fetchpriority="high" />
	</div>
	<div class="phb-overlay" aria-hidden="true">
		<?php if ( $is_home ) : ?>
		<!-- Full-image hero: S-curve shape filled with gradient so white fades into the photo -->
		<svg class="phb-curve" viewBox="0 0 100 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
			<defs>
				<!-- Warm cream: solid 0→50% so the title always has a readable backing, fade by 82% -->
				<linearGradient id="phb-g" x1="0" y1="0" x2="100" y2="0" gradientUnits="userSpaceOnUse">
					<stop offset="0%"   stop-color="#faf8f5" stop-opacity="1"/>
					<stop offset="50%"  stop-color="#faf8f5" stop-opacity="1"/>
					<stop offset="82%"  stop-color="#faf8f5" stop-opacity="0"/>
				</linearGradient>
			</defs>
			<!-- Wide halo — gentle wave, never pinches below ~58%, very soft -->
			<path d="M 0 0 L 62 0
					 C 65 10, 65 20, 60 30
					 C 56 38, 56 46, 58 54
					 C 60 62, 64 70, 62 80
					 C 60 88, 59 94, 60 100
					 L 0 100 Z"
				  fill="url(#phb-g)" opacity="0.3"/>
			<!-- Primary wave — never pinches below ~52% so text stays on cream -->
			<path d="M 0 0 L 58 0
					 C 61 10, 61 20, 56 30
					 C 52 38, 52 46, 54 54
					 C 56 62, 60 70, 58 80
					 C 56 88, 55 94, 56 100
					 L 0 100 Z"
				  fill="url(#phb-g)"/>
		</svg>
		<?php endif; ?>
	</div>
	<?php if ( $_circles && $is_home ) : ?>
		<span class="phb-circle phb-circle--a"></span>
		<span class="phb-circle phb-circle--b"></span>
		<span class="phb-circle phb-circle--c"></span>
	<?php endif; ?>
</div>
