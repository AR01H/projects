<?php
/**
 * components/parts/sidebar_most_read.php
 * Thin wrapper → parts/sidebar_link_list.
 * Number is passed as icon so it displays in accent colour.
 *
 * Props: $most_read { heading, items[] { num, title, url }, view_all { label, url } }
 */

defined( 'ABSPATH' ) || exit;

$most_read = isset( $most_read ) && is_array( $most_read ) ? $most_read : array();

if ( empty( $most_read['items'] ) ) { return; }

$_items = array();
$_n = 1;
foreach ( (array) $most_read['items'] as $it ) {
	if ( empty( $it['title'] ) ) { continue; }
	$_num = isset( $it['num'] ) && '' !== (string) $it['num'] ? (string) $it['num'] : sprintf( '%02d', $_n );
	$_items[] = array(
		'icon'  => $_num,
		'label' => (string) $it['title'],
		'url'   => isset( $it['url'] ) ? (string) $it['url'] : '',
	);
	$_n++;
}

if ( empty( $_items ) ) { return; }

$_view_all = isset( $most_read['view_all'] ) ? (array) $most_read['view_all'] : array();

adn_component( 'parts/sidebar_link_list', array( 'list' => array(
	'heading'  => ! empty( $most_read['heading'] ) ? (string) $most_read['heading'] : '',
	'items'    => $_items,
	'view_all' => $_view_all,
) ) );
