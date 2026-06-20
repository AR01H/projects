<?php
/**
 * components/parts/post_sidebar_related_content.php
 * Related content links — each group renders as a sidebar_link_list panel.
 *
 * Props: $related_content { group_key => links[] { icon, title, url } }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $related_content ) || ! is_array( $related_content ) ) { return; }

$_headings = array(
	'articles'   => adn_term( 'sidebar.related_articles', 'Related Articles' ),
	'components' => adn_term( 'sidebar.related_tools',    'Useful Tools' ),
	'support'    => adn_term( 'sidebar.related_support',  'Help & Support' ),
	'external'   => adn_term( 'sidebar.related_external', 'External Links' ),
	'related'    => adn_term( 'sidebar.related_content',  'Related Content' ),
);

foreach ( $related_content as $group => $links ) {
	if ( empty( $links ) ) { continue; }

	$_norm    = strtolower( trim( $group ) );
	$_heading = in_array( $_norm, array( 'new', 'highlights', 'highlight' ), true )
		? ''
		: ( isset( $_headings[ $group ] ) ? $_headings[ $group ] : ucwords( str_replace( '_', ' ', $group ) ) );

	$_items = array();
	foreach ( (array) $links as $link ) {
		if ( empty( $link['title'] ) ) { continue; }
		$_items[] = array(
			'icon'  => ! empty( $link['icon'] ) ? (string) $link['icon'] : '🔗',
			'label' => (string) $link['title'],
			'url'   => isset( $link['url'] ) ? (string) $link['url'] : '',
		);
	}

	if ( empty( $_items ) ) { continue; }

	adn_component( 'parts/sidebar_link_list', array( 'list' => array(
		'heading' => $_heading,
		'items'   => $_items,
	) ) );
}
