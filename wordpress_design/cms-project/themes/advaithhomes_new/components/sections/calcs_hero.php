<?php
/**
 * components/sections/calcs_hero.php — Calculators hero (split grid).
 *
 * Props: $hero { title, description, bg_icon }
 * Usage: adn_component( 'sections/calcs_hero', array( 'hero' => $ctx['hero'] ) );
 */

defined( 'ABSPATH' ) || exit;

$hero = isset( $hero ) && is_array( $hero ) ? $hero : array();
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
		<div class="calcs-hero-img" aria-hidden="true">
			<?php echo esc_html( isset( $hero['bg_icon'] ) ? $hero['bg_icon'] : '' ); ?>
		</div>
	</div>
</section>
