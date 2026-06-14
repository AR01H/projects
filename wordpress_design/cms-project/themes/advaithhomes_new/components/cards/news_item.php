<?php
/**
 * components/cards/news_item.php
 * Thin wrapper → cards/mini_card (img/gradient variant).
 * Props: $item { gradient, title, date, tag, url }
 */

defined( 'ABSPATH' ) || exit;

$item = isset( $item ) && is_array( $item ) ? $item : array();

adn_component( 'cards/mini_card', array( 'card' => array(
	'img'   => isset( $item['gradient'] ) ? (string) $item['gradient'] : '',
	'title' => isset( $item['title'] )    ? (string) $item['title']    : '',
	'meta'  => isset( $item['date'] )     ? (string) $item['date']     : '',
	'tag'   => isset( $item['tag'] )      ? (string) $item['tag']      : '',
	'url'   => isset( $item['url'] )      ? (string) $item['url']      : '',
) ) );
