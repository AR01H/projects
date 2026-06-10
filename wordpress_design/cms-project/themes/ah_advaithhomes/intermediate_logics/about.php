<?php
/**
 * About page data.
 * Editable content lives in real_data/json/about.json (header, mission/help,
 * principles, values, team intro). Stats/signals stay DB-driven.
 * Overridable via the `ah_about_data` filter.
 */
defined( 'ABSPATH' ) || exit;

$content = class_exists( 'AH_Real_Loader' ) ? AH_Real_Loader::json( 'about' ) : array();
if ( ! is_array( $content ) ) {
	$content = array();
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
$resolve( $content );

return apply_filters( 'ah_about_data', array_merge( array(
	'stats'   => function_exists( 'ah_get_site_stats' )    ? ah_get_site_stats()    : array(),
	'signals' => function_exists( 'ah_get_trust_signals' ) ? ah_get_trust_signals() : array(),
), $content ) );
