<?php
/**
 * components/cards/hot_topic_item.php
 * Thin wrapper → cards/mini_card (icon variant).
 * Props: $item { icon, text, desc, url }
 */

defined( 'ABSPATH' ) || exit;

$item = isset( $item ) && is_array( $item ) ? $item : array();

adn_component( 'cards/mini_card', array( 'card' => array(
	'icon'  => isset( $item['icon'] ) ? (string) $item['icon'] : '',
	'title' => isset( $item['text'] ) ? (string) $item['text'] : '',
	'meta'  => isset( $item['desc'] ) ? (string) $item['desc'] : '',
	'url'   => isset( $item['url'] )  ? (string) $item['url']  : '',
) ) );
