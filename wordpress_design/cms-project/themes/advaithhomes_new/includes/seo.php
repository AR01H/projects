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

add_filter( 'rank_math/frontend/robots', function( array $robots ): array {
	$reg    = (array) $GLOBALS['adn_seo'];
	$custom = trim( (string) ( $reg['title'] ?? '' ) );
	if ( '' === $custom ) { return $robots; }
	$noindex_key = array_search( 'noindex', $robots, true );
	if ( false !== $noindex_key ) { unset( $robots[ $noindex_key ] ); }
	if ( ! in_array( 'index', $robots, true ) ) { array_unshift( $robots, 'index' ); }
	return $robots;
}, 99 );

function adn_seo_resolve(): array {
	return \Adn\Theme\Service\SeoService::resolve();
}

function adn_seo_head_output(): void {
	\Adn\Theme\Service\SeoService::headOutput();
}
add_action( 'wp_head', 'adn_seo_head_output', 1 );

add_action( 'template_redirect', function() {
	if ( '/favicon.png' === wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ) {
		wp_redirect( get_template_directory_uri() . '/assets/images/logos/logo_with_text.png', 301 );
		exit;
	}
} );
