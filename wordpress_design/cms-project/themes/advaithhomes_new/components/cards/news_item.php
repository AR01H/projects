<?php
/**
 * components/cards/news_item.php
 * Thin wrapper → cards/mini_card (img/gradient variant).
 * Props: $item { gradient, title, date, tag, url }
 */

defined( 'ABSPATH' ) || exit;

$item = isset( $item ) && is_array( $item ) ? $item : array();

$news_thumb = ! empty( $item['thumbnail'] ) ? (string) $item['thumbnail'] : '';
if ( empty( $news_thumb ) ) {
	$news_thumb = get_template_directory_uri() . THEME_DEFAULT_NEWS_IMG . '?v=' . LOCAL_CACHE_VERSION;
}

adn_component( 'cards/mini_card', array( 'card' => array(
	'img_url' => $news_thumb,
	'title'   => isset( $item['title'] )    ? (string) $item['title']    : '',
	'meta'    => isset( $item['date'] )     ? (string) $item['date']     : '',
	'tag'     => isset( $item['tag'] )      ? (string) $item['tag']      : '',
	'url'     => isset( $item['url'] )      ? (string) $item['url']      : '',
) ) );
