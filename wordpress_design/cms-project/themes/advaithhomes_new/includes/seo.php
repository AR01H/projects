<?php
/**
 * SEO Layer — Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Includes
 */
defined( 'ABSPATH' ) || exit;

$GLOBALS['adn_seo'] = array();

function adn_seo_register( array $meta ): void {
	\Adn\Theme\Service\SeoService::register( $meta );
}

function adn_seo_document_title( string $title ): string {
	return \Adn\Theme\Service\SeoService::documentTitle( $title );
}
add_filter( 'pre_get_document_title', 'adn_seo_document_title', 99 );

add_filter( 'rank_math/frontend/title', 'adn_seo_document_title', 99 );

add_filter( 'rank_math/frontend/canonical', function( $canonical ) {
	$s = \Adn\Theme\Service\SeoService::resolve();
	if ( ! empty( $s['canonical'] ) ) {
		return $s['canonical'];
	}
	return $canonical;
}, 99 );

add_filter( 'rank_math/frontend/description', function( $desc ) {
	$s = \Adn\Theme\Service\SeoService::resolve();
	if ( ! empty( $s['desc'] ) ) {
		return $s['desc'];
	}
	return $desc;
}, 99 );

add_filter( 'rank_math/frontend/robots', function( array $robots ): array {
	$reg        = (array) ( $GLOBALS['adn_seo'] ?? array() );
	$_is_bare   = isset( $_GET['content'] ) && 'true' === (string) $_GET['content'];
	$_is_search = isset( $_GET['search'] )  && '' !== (string) $_GET['search'];
	$_is_dialog = isset( $_GET['dialog'] )  || isset( $_GET['embed'] );
	$_is_thin   = is_author() || is_date() || is_attachment() || is_search() || is_404();
	$_noindex   = ! empty( $reg['noindex'] ) || $_is_bare || $_is_search || $_is_dialog || $_is_thin;

	if ( $_noindex ) {
		return array(
			'noindex' => 'noindex',
			'follow'  => 'follow',
		);
	}

	return array(
		'index'             => 'index',
		'follow'            => 'follow',
		'max-snippet'       => 'max-snippet:-1',
		'max-video-preview' => 'max-video-preview:-1',
		'max-image-preview' => 'max-image-preview:large',
	);
}, 99 );

function adn_seo_resolve(): array {
	return \Adn\Theme\Service\SeoService::resolve();
}

function adn_seo_head_output(): void {
	\Adn\Theme\Service\SeoService::headOutput();
}
add_action( 'wp_head', 'adn_seo_head_output', 1 );

// Filter Rank Math JSON-LD to inject alternateName from seo.json
add_filter( 'rank_math/json_ld', function( $data, $json_ld ) {
	$_cfg_alts = (array) \Adn\Theme\Service\SeoService::getConfigValue( 'defaults.alternate_names', array() );
	if ( ! empty( $_cfg_alts ) && isset( $data['WebSite'] ) ) {
		$data['WebSite']['alternateName'] = array_values( array_filter( array_map( 'trim', $_cfg_alts ) ) );
	}
	return $data;
}, 99, 2 );

// Remove duplicate WordPress core canonical & robots tags since SeoService handles them.
if ( ! defined( 'WPSEO_VERSION' ) && ! defined( 'RANK_MATH_VERSION' ) ) {
	remove_action( 'wp_head', 'rel_canonical' );
	remove_action( 'wp_head', 'wp_robots', 1 );
}
