<?php
/**
 * components/cards/regulation_item.php
 * Thin wrapper → cards/mini_card.
 *
 * Priority (matches home page news_three_col regulations):
 *   thumbnail + overlay  → photo card with overlay label
 *   icon                 → icon card
 *   badge_lines          → legacy badge block (GOV UK style)
 *
 * Props: $item { thumbnail?, overlay?, icon?, badge_lines[]?, title, date?, url }
 */

defined( 'ABSPATH' ) || exit;

$item   = isset( $item ) && is_array( $item ) ? $item : array();
$_thumb = isset( $item['thumbnail'] ) ? (string) $item['thumbnail'] : '';
$_icon  = isset( $item['icon'] )      ? (string) $item['icon']      : '';

$_card = array(
	'title' => isset( $item['title'] ) ? (string) $item['title'] : '',
	'meta'  => isset( $item['date'] )  ? (string) $item['date']  : '',
	'url'   => isset( $item['url'] )   ? (string) $item['url']   : '',
);

if ( '' !== $_thumb ) {
	$_card['img_url'] = $_thumb;
	if ( ! empty( $item['overlay'] ) ) {
		$_card['overlay'] = (string) $item['overlay'];
	}
} elseif ( '' !== $_icon ) {
	$_card['icon'] = $_icon;
} else {
	$_card['badge'] = isset( $item['badge_lines'] ) ? (array) $item['badge_lines'] : array();
}

adn_component( 'cards/mini_card', array( 'card' => $_card ) );
