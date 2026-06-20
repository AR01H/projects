<?php
/**
 * components/parts/post_sidebar_news.php
 * Thin wrapper → parts/sidebar_link_list.
 *
 * Props: $latest_news[] { icon, title, date, url, thumbnail_url }
 */

defined( 'ABSPATH' ) || exit;

$_items_raw = ( isset( $latest_news ) && is_array( $latest_news ) ) ? $latest_news : array();
$_all_url   = defined( 'SITE_NEWS_URL' ) ? (string) SITE_NEWS_URL : '';

if ( empty( $_items_raw ) ) { return; }

$_items = array();
foreach ( $_items_raw as $_n ) {
	if ( empty( $_n['title'] ) ) { continue; }
	$_items[] = array(
		'thumbnail' => ! empty( $_n['thumbnail_url'] ) ? (string) $_n['thumbnail_url'] : '',
		'icon'      => ! empty( $_n['icon'] )          ? (string) $_n['icon']          : '📰',
		'label'     => (string) $_n['title'],
		'meta'      => ! empty( $_n['date'] )          ? (string) $_n['date']          : '',
		'url'       => isset( $_n['url'] )             ? (string) $_n['url']           : '',
	);
}

if ( empty( $_items ) ) { return; }

$_view_all = '' !== $_all_url
	? array( 'label' => adn_term( 'buttons.view_all', 'View all →' ), 'url' => $_all_url )
	: array();

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading'  => defined( 'SITE_NEWS_NOUN' ) ? 'Latest ' . SITE_NEWS_NOUN : adn_term( 'labels.latest_news', 'Latest News' ),
	'items'    => $_items,
	'view_all' => $_view_all,
) ) );
