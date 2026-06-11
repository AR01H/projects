<?php
/**
 * components/parts/sidebar_trending.php — Trending This Week list.
 *
 * Props: $trending { heading, items[] { num, title, meta, url } }
 * Usage: adn_component( 'parts/sidebar_trending', array( 'trending' => $ctx['sidebar']['trending'] ) );
 */

defined( 'ABSPATH' ) || exit;

$trending = isset( $trending ) && is_array( $trending ) ? $trending : array();
$items    = isset( $trending['items'] ) ? (array) $trending['items'] : array();

if ( empty( $items ) ) {
	return;
}
?>
<div class="news-sb-box">
	<?php if ( ! empty( $trending['heading'] ) ) : ?>
		<div class="news-sb-title"><?php echo esc_html( $trending['heading'] ); ?></div>
	<?php endif; ?>

	<ol class="trending-list">
		<?php foreach ( $items as $item ) : ?>
			<li class="trending-item">
				<span class="trending-num"><?php echo esc_html( isset( $item['num'] ) ? $item['num'] : '' ); ?></span>
				<div class="trending-body">
					<a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>" class="trending-title">
						<?php echo esc_html( isset( $item['title'] ) ? $item['title'] : '' ); ?>
					</a>
					<?php if ( ! empty( $item['meta'] ) ) : ?>
						<span class="trending-meta"><?php echo esc_html( $item['meta'] ); ?></span>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
</div>
