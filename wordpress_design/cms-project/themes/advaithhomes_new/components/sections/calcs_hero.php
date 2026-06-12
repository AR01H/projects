<?php
/**
 * components/sections/calcs_hero.php — Calculators hero (split grid).
 *
 * Props: $hero { title, description, bg_icon }
 * Usage: adn_component( 'sections/calcs_hero', array( 'hero' => $ctx['hero'] ) );
 */

defined( 'ABSPATH' ) || exit;

$hero = isset( $hero ) && is_array( $hero ) ? $hero : array();

$_default_img = get_template_directory_uri() . '/assets/images/backgrounds/home_hero.jpg';
$_hero_img    = get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img;
?>
<section class="calcs-hero">
	<div class="calcs-hero-inner">
		<div class="calcs-hero-text">
			<?php if ( ! empty( $hero['title'] ) ) : ?>
				<h1><?php echo esc_html( $hero['title'] ); ?></h1>
			<?php endif; ?>
			<?php if ( ! empty( $hero['description'] ) ) : ?>
				<p><?php echo esc_html( $hero['description'] ); ?></p>
			<?php endif; ?>
		</div>
		<div class="calcs-hero-img">
			<img src="<?php echo esc_url( $_hero_img ); ?>" alt="" loading="eager" />
		</div>
	</div>
</section>
