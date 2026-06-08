<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Real_Loader
 *
 * Reads static data files from real_data/csv/ and real_data/json/.
 * Zero dependency on mock_data/, AH_Data, or any DB class.
 *
 * Use this for content that is static / rarely changes and has NO admin edit UI.
 * For plugin-managed DB data (services, team, reviews, FAQs) use the DB helpers instead.
 *
 * CSV shapes:
 *   csv($name)  — first row = headers, one item per row → array of assoc rows
 *   kv($name)   — two columns "key,value"              → [ key => value ]
 *
 * JSON shapes:
 *   json($name)    — JSON array of objects  → array of assoc rows
 *   kv_json($name) — JSON object (key→val)  → [ key => value ]
 */
class AH_Real_Loader {

	private static function csv_dir(): string {
		return get_template_directory() . '/real_data/csv';
	}

	private static function json_dir(): string {
		return get_template_directory() . '/real_data/json';
	}

	/**
	 * Row-style CSV: real_data/csv/{name}.csv → array of associative rows.
	 * Returns [] when file is absent or unreadable.
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
		// Strip UTF-8 BOM if present.
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
	 * Key-value CSV: real_data/csv/{name}.csv (columns: key, value) → [ key => value ].
	 * Returns [] when file is absent.
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
	 * JSON array: real_data/json/{name}.json → array of assoc rows.
	 * Returns [] when file is absent or invalid.
	 */
	public static function json( string $name ): array {
		$path = self::json_dir() . '/' . $name . '.json';
		if ( ! file_exists( $path ) ) {
			return [];
		}
		$raw = file_get_contents( $path );
		if ( ! $raw ) {
			return [];
		}
		$data = json_decode( $raw, true );
		return is_array( $data ) ? $data : [];
	}

	/**
	 * JSON object: real_data/json/{name}.json (key→value object) → [ key => value ].
	 * Returns [] when file is absent or the root is not an object.
	 */
	public static function kv_json( string $name ): array {
		$data = self::json( $name );
		if ( ! $data ) {
			return [];
		}
		// Must be an associative object, not an indexed array.
		return array_keys( $data ) !== range( 0, count( $data ) - 1 ) ? $data : [];
	}
}
