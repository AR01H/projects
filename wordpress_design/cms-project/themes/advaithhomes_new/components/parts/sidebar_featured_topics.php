<?php
/**
 * components/parts/sidebar_featured_topics.php
 * Thin wrapper → parts/sidebar_link_list.
 *
 * Props: $featured_topics { heading, items[] { icon, thumbnail?, label, url }, view_all? }
 */

defined( 'ABSPATH' ) || exit;

$featured_topics = isset( $featured_topics ) && is_array( $featured_topics ) ? $featured_topics : array();

if ( empty( $featured_topics['items'] ) ) { return; }

$_items = array();
foreach ( (array) $featured_topics['items'] as $it ) {
	$_items[] = array(
		'thumbnail' => isset( $it['thumbnail'] ) ? (string) $it['thumbnail'] : '',
		'icon'      => ! empty( $it['icon'] )    ? (string) $it['icon']      : '📌',
		'label'     => isset( $it['label'] )     ? (string) $it['label']     : '',
		'url'       => isset( $it['url'] )       ? (string) $it['url']       : '',
	);
}

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading'  => ! empty( $featured_topics['heading'] ) ? $featured_topics['heading'] : adn_term( 'sidebar.featured_topics', 'Featured Topics' ),
	'items'    => $_items,
	'view_all' => isset( $featured_topics['view_all'] ) ? (array) $featured_topics['view_all'] : array(),
) ) );
