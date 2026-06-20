<?php
/**
 * components/parts/news_widget.php - Thin wrapper around parts/list_widget.
 *
 * Maps legacy news_widget props { heading, items[] } into list_widget format
 * (each item's gradient/title/date/tag/url → mini_card img-variant props).
 * The white card shell (.news-widget) is kept here for layout; inner content
 * is rendered by list_widget → mini_card.
 *
 * Props via $widget array:
 *   heading  array  { title, link_label?, link_url? }
 *   items    array  { gradient, title, date, tag, url }[]
 */

defined( 'ABSPATH' ) || exit;

$widget = isset( $widget ) && is_array( $widget ) ? $widget : array();
$items  = isset( $widget['items'] ) && is_array( $widget['items'] ) ? $widget['items'] : array();

if ( empty( $items ) ) { return; }

$cards = array();
foreach ( $items as $_item ) {
	$_thumb    = isset( $_item['thumbnail'] ) ? (string) $_item['thumbnail'] : '';
	$_icon     = isset( $_item['icon'] )      ? (string) $_item['icon']      : '';
	$_gradient = isset( $_item['gradient'] )  ? (string) $_item['gradient']  : '';
	$_overlay  = isset( $_item['overlay'] )   ? (string) $_item['overlay']   : '';

	$_card = array(
		'title' => isset( $_item['title'] ) ? (string) $_item['title'] : '',
		'meta'  => isset( $_item['date'] )  ? (string) $_item['date']  : '',
		'tag'   => isset( $_item['tag'] )   ? (string) $_item['tag']   : '',
		'url'   => isset( $_item['url'] )   ? (string) $_item['url']   : '',
	);

	// Priority: real photo > icon > gradient > default icon
	if ( '' !== $_thumb ) {
		$_card['img_url'] = $_thumb;
		if ( '' !== $_overlay ) {
			$_card['overlay'] = $_overlay;
		}
	} elseif ( '' !== $_icon ) {
		$_card['icon'] = $_icon;
	} elseif ( '' !== $_gradient ) {
		$_card['img'] = $_gradient;
	} else {
		$_card['icon'] = '📰';
	}

	$cards[] = $_card;
}
?>
<div class="news-widget mini_card_container_design">
	<?php adn_component( 'parts/list_widget', array( 'widget' => array(
		'heading' => isset( $widget['heading'] ) && is_array( $widget['heading'] ) ? $widget['heading'] : array(),
		'items'   => $cards,
	) ) ); ?>
</div>
