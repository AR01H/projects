<?php
/**
 * components/sections/calcs_hero.php - Calculators page hero, full-bleed banner.
 *
 * Uses page_hero_bg_banner as the background layer. Breadcrumb and text
 * content render on top at z-index 2 (same pattern as category_hero.php).
 *
 * Props:
 *   $hero        array  { title, description }
 *   $breadcrumb  array  [ { label, url } ] - optional
 */

defined( 'ABSPATH' ) || exit;

$hero       = isset( $hero )       && is_array( $hero )       ? $hero       : array();
$breadcrumb = isset( $breadcrumb ) && is_array( $breadcrumb ) ? $breadcrumb : array();

$_default_img = get_template_directory_uri() . '/assets/images/backgrounds/home_hero.jpg';
$_hero_img    = get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img;
?>
<section class="calcs-hero">

	<?php adn_component( 'sections/page_hero_bg_banner', array( 'hero_img' => $_hero_img ) ); ?>

	<div class="container">
		<div class="calcs-hero-content">

			<?php if ( ! empty( $breadcrumb ) ) : ?>
				<nav class="hero-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', ADN_TEXT_DOMAIN ); ?>">
					<?php foreach ( $breadcrumb as $i => $crumb ) :
						$_bc_label = isset( $crumb['label'] ) ? (string) $crumb['label'] : '';
						$_bc_url   = isset( $crumb['url'] )   ? $crumb['url']            : null;
						$_bc_last  = ( $i === count( $breadcrumb ) - 1 );
					?>
						<?php if ( ! $_bc_last && null !== $_bc_url ) : ?>
							<a href="<?php echo esc_url( adn_link( $_bc_url ) ); ?>" class="hero-bc-item"><?php echo esc_html( $_bc_label ); ?></a>
							<span class="hero-bc-sep" aria-hidden="true">›</span>
						<?php else : ?>
							<span class="hero-bc-item hero-bc-active"<?php echo $_bc_last ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $_bc_label ); ?></span>
						<?php endif; ?>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<?php if ( ! empty( $hero['title'] ) ) : ?>
				<h1><?php echo esc_html( $hero['title'] ); ?></h1>
			<?php endif; ?>

			<?php if ( ! empty( $hero['description'] ) ) : ?>
				<p><?php echo esc_html( $hero['description'] ); ?></p>
			<?php endif; ?>

		</div>
	</div>

</section>
