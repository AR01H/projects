<?php
/**
 * components/sections/category_hero.php — Category page hero, full-bleed image.
 *
 * Image covers the entire section. A left→right gradient fade keeps the left
 * side readable. Decorative circles add soft depth on the left.
 *
 * Props: $hero { title, description, trust_items[] }
 */

defined( 'ABSPATH' ) || exit;

$hero        = isset( $hero ) && is_array( $hero ) ? $hero : array();
$trust_items = isset( $hero['trust_items'] ) ? (array) $hero['trust_items'] : array();

$_default_img = get_template_directory_uri() . '/assets/images/backgrounds/home_hero.jpg';
$_hero_img    = get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img;
?>

<?php /* Full-bleed background image */ ?>
<div class="category-hero-bg">
	<img src="<?php echo esc_url( $_hero_img ); ?>" alt="" loading="eager" />
</div>

<?php /* Gradient fade overlay + decorative circles */ ?>
<div class="category-hero-overlay" aria-hidden="true">
	<span class="chero-circle chero-circle--a"></span>
	<span class="chero-circle chero-circle--b"></span>
	<span class="chero-circle chero-circle--c"></span>
</div>

<?php /* Text content — sits over the overlay */ ?>
<div class="container">
	<div class="category-hero-content">
		<h1><?php echo esc_html( isset( $hero['title'] ) ? $hero['title'] : '' ); ?></h1>
		<?php if ( ! empty( $hero['description'] ) ) : ?>
			<p><?php echo esc_html( $hero['description'] ); ?></p>
		<?php endif; ?>

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
</div>
