<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/data_reader_base.php';

/**
 * ADN_JSON_Reader - reads JSON files from data/json/{name}.json.
 *
 *   data($name) - any JSON (array or object) → associative array
 *   kv($name)   - JSON object (key→value)    → [ key => value ] (assoc only)
 */
class ADN_JSON_Reader extends ADN_Data_Reader {

	/**
	 * Decode a JSON file to an associative array. [] when absent/invalid.
	 */
	public static function data( $name ) {
		$path = self::resolve( 'json', $name, 'json' );
		if ( '' === $path ) {
			return array();
		}

		$content = file_get_contents( $path );
		if ( false === $content || '' === $content ) {
			return array();
		}

		$decoded = json_decode( $content, true );
		return ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) )
			? $decoded
			: array();
	}

	/**
	 * Read a JSON object as [ key => value ]. Returns [] if the JSON is a
	 * plain indexed array rather than an object.
	 */
	public static function kv( $name ) {
		$data = self::data( $name );
		if ( ! $data ) {
			return array();
		}
		// Associative (non-sequential) only.
		return array_keys( $data ) !== range( 0, count( $data ) - 1 ) ? $data : array();
	}
}
