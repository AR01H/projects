<?php
/**
 * components/cards/news_list_item.php — List-style news row.
 *
 * Props: $item { cat_key, icon, tags[] { label, class }, title, excerpt, date, read_time, url }
 * Usage: adn_component( 'cards/news_list_item', array( 'item' => $item ) );
 */

defined( 'ABSPATH' ) || exit;

$item    = isset( $item ) && is_array( $item ) ? $item : array();
$cat_key = isset( $item['cat_key'] ) ? sanitize_key( $item['cat_key'] ) : 'all';
$tags    = isset( $item['tags'] )    ? (array) $item['tags']            : array();
$url     = esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) );
?>
<div class="news-list-item" data-cat="<?php echo esc_attr( $cat_key ); ?>">
	<div class="nli-icon-wrap">
		<span><?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?></span>
	</div>

	<div class="nli-body">
		<div class="nli-tags">
			<?php foreach ( $tags as $tag ) : ?>
				<span class="nli-tag <?php echo esc_attr( isset( $tag['class'] ) ? $tag['class'] : '' ); ?>">
					<?php echo esc_html( isset( $tag['label'] ) ? $tag['label'] : '' ); ?>
				</span>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $item['title'] ) ) : ?>
			<h3 class="nli-title">
				<a href="<?php echo $url; ?>"><?php echo esc_html( $item['title'] ); ?></a>
			</h3>
		<?php endif; ?>

		<?php if ( ! empty( $item['excerpt'] ) ) : ?>
			<p class="nli-excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
		<?php endif; ?>

		<div class="nli-meta">
			<?php if ( ! empty( $item['date'] ) ) : ?>
				<span><?php echo esc_html( $item['date'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $item['read_time'] ) ) : ?>
				<span><?php echo esc_html( $item['read_time'] ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</div>
