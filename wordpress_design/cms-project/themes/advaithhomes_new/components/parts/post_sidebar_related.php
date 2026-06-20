<?php
/**
 * components/parts/post_sidebar_related.php
 * Thin wrapper → parts/sidebar_link_list.
 *
 * Props: $related_guides[] { icon, title, read_time, url }
 */

defined( 'ABSPATH' ) || exit;

$_guides = ( isset( $related_guides ) && is_array( $related_guides ) ) ? $related_guides : array();

if ( empty( $_guides ) ) { return; }

$_items = array();
foreach ( $_guides as $_g ) {
	if ( empty( $_g['title'] ) ) { continue; }
	$_items[] = array(
		'icon'  => ! empty( $_g['icon'] ) ? (string) $_g['icon'] : '📖',
		'label' => (string) $_g['title'],
		'meta'  => ! empty( $_g['read_time'] ) ? '⏱ ' . (string) $_g['read_time'] : '',
		'url'   => isset( $_g['url'] ) ? (string) $_g['url'] : '',
	);
}

if ( empty( $_items ) ) { return; }

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading' => defined( 'SITE_SIDEBAR_RELATED' ) ? SITE_SIDEBAR_RELATED : adn_term( 'sidebar.related_guides', 'Related Guides' ),
	'items'   => $_items,
) ) );
