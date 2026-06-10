<?php
/**
 * Calculators Hub data (mockup #5).
 * Loads the editable list from real_data/json/calculators.json, resolves
 * relative URLs, computes per-category counts, and reads the active category
 * filter (?category=slug). Content lives in the JSON file - the components
 * stay generic. Overridable via the `ah_calculators_data` filter.
 */
defined( 'ABSPATH' ) || exit;

$data = class_exists( 'AH_Real_Loader' ) ? AH_Real_Loader::json( 'calculators' ) : array();
if ( ! is_array( $data ) ) {
	$data = array();
}

/* Resolve relative URLs → absolute. */
$resolve = static function ( array &$node ) use ( &$resolve ): void {
	foreach ( $node as $key => &$val ) {
		if ( is_array( $val ) ) {
			$resolve( $val );
		} elseif ( 'url' === $key && is_string( $val ) && isset( $val[0] ) && '/' === $val[0] ) {
			$val = home_url( $val );
		}
	}
	unset( $val );
};
$resolve( $data );

$calculators = $data['calculators'] ?? array();
$categories  = $data['categories']  ?? array();

/* Per-category counts (for the sidebar). */
$counts = array( 'all' => count( $calculators ) );
foreach ( $calculators as $c ) {
	$cat = $c['category'] ?? '';
	if ( $cat ) {
		$counts[ $cat ] = ( $counts[ $cat ] ?? 0 ) + 1;
	}
}

$active = sanitize_title( $_GET['category'] ?? 'all' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( '' === $active ) {
	$active = 'all';
}

return apply_filters( 'ah_calculators_data', array(
	'header'      => $data['header'] ?? array(),
	'categories'  => $categories,
	'calculators' => $calculators,
	'popular'     => array_values( array_filter( $calculators, static fn( $c ) => ! empty( $c['popular'] ) ) ),
	'counts'      => $counts,
	'active'      => $active,
	'base_url'    => get_permalink(),
) );
