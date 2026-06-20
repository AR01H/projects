<?php
/**
 * components/parts/sidebar_quick_tools.php
 * Thin wrapper → parts/sidebar_link_list.
 *
 * Props: $quick_tools { heading, items[] { icon, thumbnail?, label, url }, cta { label, url } }
 */

defined( 'ABSPATH' ) || exit;

$quick_tools = isset( $quick_tools ) && is_array( $quick_tools ) ? $quick_tools : array();

if ( empty( $quick_tools['items'] ) ) { return; }

$_items = array();
foreach ( (array) $quick_tools['items'] as $it ) {
	$_items[] = array(
		'thumbnail' => isset( $it['thumbnail'] ) ? (string) $it['thumbnail'] : '',
		'icon'      => ! empty( $it['icon'] )    ? (string) $it['icon']      : '🧮',
		'label'     => isset( $it['label'] )     ? (string) $it['label']     : '',
		'url'       => isset( $it['url'] )       ? (string) $it['url']       : '',
	);
}

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading' => ! empty( $quick_tools['heading'] ) ? $quick_tools['heading'] : ( defined( 'SITE_TOOLS_PLURAL' ) ? SITE_TOOLS_PLURAL : 'Tools' ),
	'items'   => $_items,
	'cta'     => isset( $quick_tools['cta'] ) ? (array) $quick_tools['cta'] : array(),
) ) );
