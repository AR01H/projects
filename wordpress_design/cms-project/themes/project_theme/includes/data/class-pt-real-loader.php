<?php
/**
 * PT_Real_Loader
 *
 * Reads static data files from real_data/csv/ and real_data/json/.
 * Zero dependency on mock_data/ or any DB class.
 *
 * CSV shapes:
 *   csv($name)  — first row = headers, one item per row → array of assoc rows
 *
 * JSON shapes:
 *   section_heading($key)  — reads real_data/json/section-headings.json, returns one entry
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PT_Real_Loader {

	private static function csv_dir(): string {
		return get_template_directory() . '/real_data/csv';
	}

	private static function json_dir(): string {
		return get_template_directory() . '/real_data/json';
	}

	/**
	 * Row-style CSV: real_data/csv/{name}.csv → array of associative rows.
	 * Returns [] when the file is absent or unreadable.
	 */
	public static function csv( string $name ): array {
		$path = self::csv_dir() . '/' . $name . '.csv';
		if ( ! file_exists( $path ) ) return [];

		$fh = fopen( $path, 'r' );
		if ( false === $fh ) return [];

		/* Strip UTF-8 BOM if present */
		$bom = fread( $fh, 3 );
		if ( "\xef\xbb\xbf" !== $bom ) rewind( $fh );

		$headers = fgetcsv( $fh );
		if ( ! $headers ) { fclose( $fh ); return []; }

		$headers = array_map( 'trim', $headers );
		$rows    = [];
		while ( ( $row = fgetcsv( $fh ) ) !== false ) {
			if ( count( $row ) < count( $headers ) ) continue;
			$rows[] = array_combine(
				$headers,
				array_map( 'trim', array_slice( $row, 0, count( $headers ) ) )
			);
		}
		fclose( $fh );
		return $rows;
	}

	/**
	 * Single section heading from real_data/json/section-headings.json.
	 * Returns [] if the key is absent.
	 */
	public static function section_heading( string $key ): array {
		static $cache = null;
		if ( $cache === null ) {
			$path = self::json_dir() . '/section-headings.json';
			if ( ! file_exists( $path ) ) { $cache = []; }
			else {
				$decoded = json_decode( file_get_contents( $path ), true );
				$cache   = is_array( $decoded ) ? $decoded : [];
			}
		}
		return is_array( $cache[ $key ] ?? null ) ? (array) $cache[ $key ] : [];
	}
}
