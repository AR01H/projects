<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Real_Loader
 *
 * Reads data files from real_data/csv/ and real_data/json/.
 * Zero dependency on schema/, mock_data/, CH_CSV_Loader, or CH_Data.
 *
 * CSV shapes supported:
 *   csv($name)    - first row = headers, one item per row → array of assoc rows
 *   kv($name)     - two columns "key,value"              → [ key => value ]
 *
 * JSON shapes supported:
 *   json($name)   - JSON array of objects                → array of assoc rows
 *   kv_json($name)- JSON object (key→value pairs)        → [ key => value ]
 */
class CH_Real_Loader {

	private static function csv_dir(): string {
		return get_template_directory() . '/real_data/csv';
	}

	private static function json_dir(): string {
		return get_template_directory() . '/real_data/json';
	}

	/**
	 * Read a row-style CSV from real_data/csv/{name}.csv.
	 * Returns array of associative rows, or [] when file is absent.
	 */
	public static function csv( string $name ): array {
		$path = self::csv_dir() . '/' . $name . '.csv';
		if ( ! file_exists( $path ) ) {
			return [];
		}
		$fh = fopen( $path, 'r' );
		if ( false === $fh ) {
			return [];
		}
		$bom = fread( $fh, 3 );
		if ( "\xef\xbb\xbf" !== $bom ) {
			rewind( $fh );
		}
		$headers = fgetcsv( $fh );
		if ( ! $headers ) {
			fclose( $fh );
			return [];
		}
		$headers = array_map( 'trim', $headers );
		$rows    = [];
		while ( ( $row = fgetcsv( $fh ) ) !== false ) {
			if ( count( $row ) < count( $headers ) ) {
				continue;
			}
			$rows[] = array_combine(
				$headers,
				array_map( 'trim', array_slice( $row, 0, count( $headers ) ) )
			);
		}
		fclose( $fh );
		return $rows;
	}

	/**
	 * Read a two-column key/value CSV from real_data/csv/{name}.csv.
	 * Returns [ key => value ] or [] when file is absent.
	 */
	public static function kv( string $name ): array {
		$rows = self::csv( $name );
		if ( ! $rows ) {
			return [];
		}
		$out = [];
		foreach ( $rows as $row ) {
			$k = trim( $row['key'] ?? '' );
			if ( '' !== $k ) {
				$out[ $k ] = $row['value'] ?? '';
			}
		}
		return $out;
	}

	/**
	 * Read a JSON array from real_data/json/{name}.json.
	 * Returns array of rows, or [] when file is absent or invalid.
	 */
	public static function json( string $name ): array {
		$path = self::json_dir() . '/' . $name . '.json';
		if ( ! file_exists( $path ) ) {
			return [];
		}
		$content = file_get_contents( $path );
		if ( ! $content ) {
			return [];
		}
		$data = json_decode( $content, true );
		return is_array( $data ) ? $data : [];
	}

	/**
	 * Read a JSON object from real_data/json/{name}.json as [ key => value ].
	 * Use this for settings stored as a JSON object instead of a CSV.
	 */
	public static function kv_json( string $name ): array {
		$data = self::json( $name );
		if ( ! $data ) {
			return [];
		}
		// Must be an associative (non-indexed) object.
		return array_keys( $data ) !== range( 0, count( $data ) - 1 ) ? $data : [];
	}
}
