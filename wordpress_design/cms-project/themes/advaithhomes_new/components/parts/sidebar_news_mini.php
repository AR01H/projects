<?php
/**
 * components/parts/sidebar_news_mini.php - Sidebar: compact news list.
 *
 * Wraps cards/news_item in a sidebar card shell with a heading and view-all link.
 * Reuses the same data shape as news_item: { title, date, gradient, url }.
 *
 * Props: $news_mini { heading, items[], view_all { label, url } }
 * Usage: adn_component( 'parts/sidebar_news_mini', array( 'news_mini' => $ctx['sidebar']['news'] ) );
 */

defined( 'ABSPATH' ) || exit;

$news_mini = isset( $news_mini ) && is_array( $news_mini ) ? $news_mini : array();
$items     = isset( $news_mini['items'] )    ? (array) $news_mini['items']    : array();
$view_all  = isset( $news_mini['view_all'] ) ? (array) $news_mini['view_all'] : array();
?>
<div class="sidebar-card sidebar-card--news">
	<?php if ( ! empty( $news_mini['heading'] ) ) : ?>
		<div class="sidebar-card-title"><?php echo esc_html( $news_mini['heading'] ); ?></div>
	<?php endif; ?>

	<?php foreach ( $items as $item ) : ?>
		<?php adn_component( 'cards/news_item', array( 'item' => $item ) ); ?>
	<?php endforeach; ?>

	<?php if ( ! empty( $view_all['label'] ) ) : ?>
		<a href="<?php echo esc_url( adn_link( isset( $view_all['url'] ) ? $view_all['url'] : '' ) ); ?>" class="view-all-small">
			<?php echo esc_html( $view_all['label'] ); ?>
		</a>
	<?php endif; ?>
</div>
