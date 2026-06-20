<?php
/**
 * components/parts/quick_links_widget.php
 * Thin wrapper → parts/sidebar_link_list.
 *
 * Props: $quick_links { heading, items[] { icon, thumbnail?, label, url }, view_all? }
 */

defined( 'ABSPATH' ) || exit;

$quick_links = isset( $quick_links ) && is_array( $quick_links ) ? $quick_links : array();

if ( empty( $quick_links['items'] ) ) { return; }

$_items = array();
foreach ( (array) $quick_links['items'] as $it ) {
	if ( empty( $it['label'] ) ) { continue; }
	$_items[] = array(
		'thumbnail' => isset( $it['thumbnail'] ) ? (string) $it['thumbnail'] : '',
		'icon'      => isset( $it['icon'] )      ? (string) $it['icon']      : '',
		'label'     => (string) $it['label'],
		'url'       => isset( $it['url'] )       ? (string) $it['url']       : '',
	);
}

if ( empty( $_items ) ) { return; }

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading'  => isset( $quick_links['heading'] ) && '' !== $quick_links['heading']
		? (string) $quick_links['heading']
		: __( 'Quick Links', ADN_TEXT_DOMAIN ),
	'items'    => $_items,
	'view_all' => isset( $quick_links['view_all'] ) ? (array) $quick_links['view_all'] : array(),
) ) );
