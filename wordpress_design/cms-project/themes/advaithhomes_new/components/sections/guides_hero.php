<?php
/**
 * components/sections/guides_hero.php — Guides listing hero (light theme).
 *
 * Props: $hero { eyebrow, title, description, bg_icon, trust_items[] }
 * Usage: adn_component( 'sections/guides_hero', array( 'hero' => $ctx['hero'] ) );
 */

defined( 'ABSPATH' ) || exit;

$hero        = isset( $hero )  && is_array( $hero )  ? $hero  : array();
$trust_items = isset( $hero['trust_items'] ) ? (array) $hero['trust_items'] : array();
?>
<section class="guides-hero">
	<?php if ( ! empty( $hero['bg_icon'] ) ) : ?>
		<div class="guides-hero-deco" aria-hidden="true">
			<span class="guides-hero-deco-icon"><?php echo adn_icon( $hero['bg_icon'] ); ?></span>
		</div>
	<?php endif; ?>

	<div class="container">
		<div class="guides-hero-body">
			<?php if ( ! empty( $hero['eyebrow'] ) ) : ?>
				<div class="hero-eyebrow"><?php echo esc_html( $hero['eyebrow'] ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $hero['title'] ) ) : ?>
				<h1><?php echo esc_html( $hero['title'] ); ?></h1>
			<?php endif; ?>

			<?php if ( ! empty( $hero['description'] ) ) : ?>
				<p class="guides-hero-desc"><?php echo esc_html( $hero['description'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $trust_items ) ) : ?>
				<div class="trust-bar">
					<?php foreach ( $trust_items as $item ) : ?>
						<div class="trust-bar-item">
							<span class="trust-bar-icon">✓</span>
							<?php echo esc_html( $item ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
