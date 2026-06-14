<?php
/**
 * components/cards/regulation_item.php
 * Thin wrapper → cards/mini_card (badge variant).
 * Props: $item { badge_lines[], title, url }
 */

defined( 'ABSPATH' ) || exit;

$item = isset( $item ) && is_array( $item ) ? $item : array();

adn_component( 'cards/mini_card', array( 'card' => array(
	'badge' => isset( $item['badge_lines'] ) ? (array) $item['badge_lines'] : array(),
	'title' => isset( $item['title'] )       ? (string) $item['title']      : '',
	'url'   => isset( $item['url'] )         ? (string) $item['url']        : '',
) ) );
