<?php
/**
 * components/sections/category_hero.php — Category page hero with trust bar.
 *
 * Props: $hero { title, description, image_icon, trust_items[] }
 * Usage: adn_component( 'sections/category_hero', array( 'hero' => $ctx['hero'] ) );
 */

defined( 'ABSPATH' ) || exit;

$hero        = isset( $hero ) && is_array( $hero ) ? $hero : array();
$trust_items = isset( $hero['trust_items'] ) ? (array) $hero['trust_items'] : array();
?>
<div class="category-hero-inner">
	<div class="category-hero-content">
		<h1><?php echo esc_html( isset( $hero['title'] ) ? $hero['title'] : '' ); ?></h1>
		<p><?php echo esc_html( isset( $hero['description'] ) ? $hero['description'] : '' ); ?></p>

		<?php if ( $trust_items ) : ?>
			<div class="trust-bar">
				<?php foreach ( $trust_items as $item ) : ?>
					<div class="trust-bar-item">
						<span class="trust-bar-icon">&#x2713;</span>
						<?php echo esc_html( $item ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="category-hero-img">
		<div class="hero-placeholder-img">
			<?php echo adn_icon( isset( $hero['image_icon'] ) ? $hero['image_icon'] : '' ); ?>
		</div>
	</div>
</div>
