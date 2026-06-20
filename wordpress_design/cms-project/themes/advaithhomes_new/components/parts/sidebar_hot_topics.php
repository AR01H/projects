<?php
/**
 * components/parts/sidebar_hot_topics.php
 * Thin wrapper → parts/sidebar_link_list.
 *
 * Props: $hot_topics { heading, items[] { icon, thumbnail?, label, url }, view_all? }
 */

defined( 'ABSPATH' ) || exit;

$hot_topics = isset( $hot_topics ) && is_array( $hot_topics ) ? $hot_topics : array();

if ( empty( $hot_topics['items'] ) ) { return; }

$_items = array();
foreach ( (array) $hot_topics['items'] as $it ) {
	$_items[] = array(
		'thumbnail' => isset( $it['thumbnail'] ) ? (string) $it['thumbnail'] : '',
		'icon'      => ! empty( $it['icon'] )    ? (string) $it['icon']      : '🔥',
		'label'     => isset( $it['label'] )     ? (string) $it['label']     : '',
		'url'       => isset( $it['url'] )       ? (string) $it['url']       : '',
	);
}

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading'  => ! empty( $hot_topics['heading'] ) ? $hot_topics['heading'] : adn_term( 'sidebar.hot_topics', 'Hot Topics' ),
	'items'    => $_items,
	'view_all' => isset( $hot_topics['view_all'] ) ? (array) $hot_topics['view_all'] : array(),
) ) );
