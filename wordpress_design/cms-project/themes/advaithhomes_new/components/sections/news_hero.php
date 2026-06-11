<?php
/**
 * components/sections/news_hero.php — News page dark-gradient hero with stats.
 *
 * Props: $hero { title, description, stats[] { value, label } }
 * Usage: adn_component( 'sections/news_hero', array( 'hero' => $ctx['hero'] ) );
 */

defined( 'ABSPATH' ) || exit;

$hero  = isset( $hero )  && is_array( $hero )  ? $hero  : array();
$stats = isset( $hero['stats'] ) ? (array) $hero['stats'] : array();
?>
<div class="news-hero-inner">
	<?php if ( ! empty( $hero['title'] ) ) : ?>
		<h1 class="news-hero-title"><?php echo esc_html( $hero['title'] ); ?></h1>
	<?php endif; ?>

	<?php if ( ! empty( $hero['description'] ) ) : ?>
		<p class="news-hero-desc"><?php echo esc_html( $hero['description'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $stats ) ) : ?>
		<div class="news-hero-stats">
			<?php foreach ( $stats as $stat ) : ?>
				<div class="news-hero-stat">
					<div class="nhs-value"><?php echo esc_html( isset( $stat['value'] ) ? $stat['value'] : '' ); ?></div>
					<div class="nhs-label"><?php echo esc_html( isset( $stat['label'] ) ? $stat['label'] : '' ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
