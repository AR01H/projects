<?php
/**
 * components/parts/sidebar_news_mini.php
 * Thin wrapper → parts/sidebar_link_list.
 *
 * Props: $news_mini { heading, items[] { title, date, url }, view_all { label, url } }
 */

defined( 'ABSPATH' ) || exit;

$news_mini = isset( $news_mini ) && is_array( $news_mini ) ? $news_mini : array();

if ( empty( $news_mini['items'] ) ) { return; }

$_items = array();
foreach ( (array) $news_mini['items'] as $it ) {
	if ( empty( $it['title'] ) ) { continue; }
	$_items[] = array(
		'icon'  => '📰',
		'label' => (string) $it['title'],
		'meta'  => isset( $it['date'] ) ? (string) $it['date'] : '',
		'url'   => isset( $it['url'] )  ? (string) $it['url']  : '',
	);
}

if ( empty( $_items ) ) { return; }

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading'  => ! empty( $news_mini['heading'] ) ? (string) $news_mini['heading'] : ( defined( 'SITE_NEWS_NOUN' ) ? 'Latest ' . SITE_NEWS_NOUN : 'Latest News' ),
	'items'    => $_items,
	'view_all' => isset( $news_mini['view_all'] ) ? (array) $news_mini['view_all'] : array(),
) ) );
