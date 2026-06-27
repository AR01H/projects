<?php
/**
 * components/sections/news_featured.php - Featured article split card.
 *
 * Props: $featured { bg_icon, label, tag, title, excerpt, date, read_time, url }
 * Usage: adn_component( 'sections/news_featured', array( 'featured' => $ctx['featured'] ) );
 */

defined( 'ABSPATH' ) || exit;

$featured = isset( $featured ) && is_array( $featured ) ? $featured : array();

if ( empty( $featured ) ) {
	return;
}

$url   = esc_url( adn_link( isset( $featured['url'] ) ? $featured['url'] : '' ) );
$thumb = isset( $featured['thumbnail'] ) ? (string) $featured['thumbnail'] : '';
?>
<div class="featured-article">
	<a href="<?php echo $url; ?>" class="featured-article-img<?php echo $thumb ? ' featured-article-img--has-thumb' : ''; ?>" aria-label="<?php echo esc_attr( isset( $featured['title'] ) ? $featured['title'] : '' ); ?>">
		<?php if ( $thumb ) : ?>
			<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" class="fa-thumbnail">
			<div class="fa-bg-icon"><?php echo adn_icon( isset( $featured['bg_icon'] ) ? $featured['bg_icon'] : '' ); ?></div>
		<?php else : ?>
			<div class="fa-center-icon"><?php echo adn_icon( isset( $featured['bg_icon'] ) ? $featured['bg_icon'] : 'fa-newspaper' ); ?></div>
		<?php endif; ?>
		<?php if ( ! empty( $featured['label'] ) ) : ?>
			<div class="fa-label"><?php echo esc_html( $featured['label'] ); ?></div>
		<?php endif; ?>
	</a>

	<div class="featured-article-body">

		<?php if ( ! empty( $featured['title'] ) ) : ?>
			<h2 class="fa-title">
				<a href="<?php echo $url; ?>"><?php echo esc_html( $featured['title'] ); ?></a>
			</h2>
		<?php endif; ?>

		<?php if ( ! empty( $featured['excerpt'] ) ) : ?>
			<p class="fa-excerpt"><?php echo esc_html( $featured['excerpt'] ); ?></p>
		<?php endif; ?>

		<a href="<?php echo $url; ?>" class="btn btn-primary fa-read-btn">
			<?php echo esc_html( SITE_SECTION_NEWS_READ_BTN ); ?> &rsaquo;
		</a>
	</div>
</div>
