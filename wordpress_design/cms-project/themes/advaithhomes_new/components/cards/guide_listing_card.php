<?php
/**
 * components/cards/guide_listing_card.php - Guides listing page card (with image area, bookmark, meta).
 *
 * Distinct from cards/guide_card.php which is the compact card used on home/category pages.
 *
 * Props: $item { img_class, icon, category, title, desc, date, read_time, url }
 * img_class maps to a CSS gradient class (guide-img-green, guide-img-blue, etc.)
 * Usage: adn_component( 'cards/guide_listing_card', array( 'item' => $item ) );
 */

defined( 'ABSPATH' ) || exit;

$item      = isset( $item ) && is_array( $item ) ? $item : array();
$img_class = isset( $item['img_class'] ) ? ' ' . sanitize_html_class( $item['img_class'] ) : '';
$url       = esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) );
?>
<a href="<?php echo $url; ?>" class="guide-listing-card">
	<div class="guide-listing-img">
		<?php if ( ! empty( $item['thumbnail'] ) ) : ?>
			<img src="<?php echo esc_url( $item['thumbnail'] ); ?>"
			     alt="<?php echo esc_attr( isset( $item['title'] ) ? $item['title'] : '' ); ?>"
			     loading="lazy">
		<?php else : ?>
			<div class="guide-listing-img-bg<?php echo $img_class; ?>">
				<span><?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?></span>
			</div>
		<?php endif; ?>
		<button class="guide-bookmark" type="button" onclick="event.preventDefault();" aria-label="<?php echo esc_attr__( 'Bookmark', ADN_TEXT_DOMAIN ); ?>">🔖</button>
	</div>

	<div class="guide-listing-body">
		<?php if ( ! empty( $item['category'] ) ) : ?>
			<div class="guide-listing-category"><?php echo esc_html( $item['category'] ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $item['title'] ) ) : ?>
			<div class="guide-listing-title"><?php echo esc_html( $item['title'] ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $item['desc'] ) ) : ?>
			<div class="guide-listing-desc"><?php echo esc_html( $item['desc'] ); ?></div>
		<?php endif; ?>

		<div class="guide-listing-meta">
			<?php if ( ! empty( $item['date'] ) ) : ?>
				<div class="guide-listing-date">📅 <?php echo esc_html( $item['date'] ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $item['read_time'] ) ) : ?>
				<span><?php echo esc_html( $item['read_time'] ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</a>
