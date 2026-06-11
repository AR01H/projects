<?php
/**
 * components/parts/sidebar_hot_topics.php — Sidebar: Hot Topics list.
 *
 * Props: $hot_topics { heading, items[], view_all { label, url } }
 * Usage: adn_component( 'parts/sidebar_hot_topics', array( 'hot_topics' => $ctx['sidebar']['hot_topics'] ) );
 */

defined( 'ABSPATH' ) || exit;

$hot_topics = isset( $hot_topics ) && is_array( $hot_topics ) ? $hot_topics : array();
$items      = isset( $hot_topics['items'] )    ? (array) $hot_topics['items']    : array();
$view_all   = isset( $hot_topics['view_all'] ) ? (array) $hot_topics['view_all'] : array();
?>
<div class="sidebar-card">
	<?php if ( ! empty( $hot_topics['heading'] ) ) : ?>
		<div class="sidebar-card-title"><?php echo esc_html( $hot_topics['heading'] ); ?></div>
	<?php endif; ?>

	<?php foreach ( $items as $item ) : ?>
		<div class="hot-topic-sidebar-item">
			<span class="hot-topic-sidebar-icon"><?php echo esc_html( isset( $item['icon'] ) ? $item['icon'] : '' ); ?></span>
			<a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>" class="hot-topic-sidebar-text">
				<?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?>
			</a>
		</div>
	<?php endforeach; ?>

	<?php if ( ! empty( $view_all['label'] ) ) : ?>
		<a href="<?php echo esc_url( adn_link( isset( $view_all['url'] ) ? $view_all['url'] : '' ) ); ?>" class="view-all-small">
			<?php echo esc_html( $view_all['label'] ); ?>
		</a>
	<?php endif; ?>
</div>
