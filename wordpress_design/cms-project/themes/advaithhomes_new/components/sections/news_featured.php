<?php
/**
 * components/sections/news_featured.php — Featured article split card.
 *
 * Props: $featured { bg_icon, label, tag, title, excerpt, date, read_time, url }
 * Usage: adn_component( 'sections/news_featured', array( 'featured' => $ctx['featured'] ) );
 */

defined( 'ABSPATH' ) || exit;

$featured = isset( $featured ) && is_array( $featured ) ? $featured : array();

if ( empty( $featured ) ) {
	return;
}

$url = esc_url( adn_link( isset( $featured['url'] ) ? $featured['url'] : '' ) );
?>
<div class="featured-article">
	<a href="<?php echo $url; ?>" class="featured-article-img" aria-label="<?php echo esc_attr( isset( $featured['title'] ) ? $featured['title'] : '' ); ?>">
		<div class="fa-bg-icon"><?php echo esc_html( isset( $featured['bg_icon'] ) ? $featured['bg_icon'] : '' ); ?></div>
		<?php if ( ! empty( $featured['label'] ) ) : ?>
			<div class="fa-label"><?php echo esc_html( $featured['label'] ); ?></div>
		<?php endif; ?>
	</a>

	<div class="featured-article-body">
		<?php if ( ! empty( $featured['tag'] ) ) : ?>
			<div class="fa-tag"><?php echo esc_html( $featured['tag'] ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $featured['title'] ) ) : ?>
			<h2 class="fa-title">
				<a href="<?php echo $url; ?>"><?php echo esc_html( $featured['title'] ); ?></a>
			</h2>
		<?php endif; ?>

		<?php if ( ! empty( $featured['excerpt'] ) ) : ?>
			<p class="fa-excerpt"><?php echo esc_html( $featured['excerpt'] ); ?></p>
		<?php endif; ?>

		<div class="fa-meta">
			<?php if ( ! empty( $featured['date'] ) ) : ?>
				<span><?php echo esc_html( $featured['date'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $featured['read_time'] ) ) : ?>
				<span><?php echo esc_html( $featured['read_time'] ); ?></span>
			<?php endif; ?>
		</div>

		<a href="<?php echo $url; ?>" class="btn btn-primary fa-read-btn">
			<?php echo esc_html__( 'Read Full Story', ADN_TEXT_DOMAIN ); ?> &rsaquo;
		</a>
	</div>
</div>
