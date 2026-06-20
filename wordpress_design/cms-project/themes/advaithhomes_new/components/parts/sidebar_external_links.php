<?php
/**
 * components/parts/sidebar_external_links.php
 * Thin wrapper → parts/sidebar_link_list.
 *
 * Props: $external_links { heading, items[] { icon, title, url, desc } }
 */

defined( 'ABSPATH' ) || exit;

$external_links = isset( $external_links ) && is_array( $external_links ) ? $external_links : array();

if ( empty( $external_links['items'] ) ) { return; }

$_items = array();
foreach ( (array) $external_links['items'] as $it ) {
	if ( empty( $it['title'] ) ) { continue; }
	$_items[] = array(
		'icon'  => isset( $it['icon'] ) ? (string) $it['icon'] : '🔗',
		'label' => (string) $it['title'],
		'meta'  => isset( $it['desc'] ) ? (string) $it['desc'] : '',
		'url'   => isset( $it['url'] )  ? (string) $it['url']  : '',
	);
}

if ( empty( $_items ) ) { return; }

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading' => ! empty( $external_links['heading'] ) ? (string) $external_links['heading'] : '',
	'items'   => $_items,
) ) );
