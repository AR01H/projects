<?php
/**
 * Areas page data. Content in real_data/json/areas.json (header, popular areas,
 * features, map). Overridable via the `ah_areas_data` filter.
 */
defined( 'ABSPATH' ) || exit;

$data = class_exists( 'AH_Real_Loader' ) ? AH_Real_Loader::json( 'areas' ) : array();
if ( ! is_array( $data ) ) {
	$data = array();
}

$resolve = static function ( array &$node ) use ( &$resolve ): void {
	foreach ( $node as $key => &$val ) {
		if ( is_array( $val ) ) {
			$resolve( $val );
		} elseif ( 'url' === $key && is_string( $val ) && isset( $val[0] ) && '/' === $val[0] ) {
			$val = home_url( $val );
		} elseif ( 'image' === $key && is_string( $val ) && 0 === strpos( $val, '/assets' ) ) {
			$val = get_template_directory_uri() . $val;
		}
	}
	unset( $val );
};
$resolve( $data );

return apply_filters( 'ah_areas_data', $data );
