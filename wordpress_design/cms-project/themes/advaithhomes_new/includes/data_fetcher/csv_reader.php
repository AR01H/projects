<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/data_reader_base.php';

/**
 * ADN_CSV_Reader - reads CSV files from data/csv/{name}.csv.
 *
 *   rows($name) - first row is the header, one item per line → array of assoc rows
 *   kv($name)   - two columns "key,value"                    → [ key => value ]
 */
class ADN_CSV_Reader extends ADN_Data_Reader {

	/**
	 * Row-style CSV → array of associative rows. [] when absent/empty.
	 */
	public static function rows( $name ) {
		$path = self::resolve( 'csv', $name, 'csv' );
		if ( '' === $path ) {
			return array();
		}

		$fh = fopen( $path, 'r' );
		if ( false === $fh ) {
			return array();
		}

		// Skip a UTF-8 BOM if present, otherwise rewind to the first byte.
		$bom = fread( $fh, 3 );
		if ( "\xef\xbb\xbf" !== $bom ) {
			rewind( $fh );
		}

		$headers = fgetcsv( $fh );
		if ( ! $headers ) {
			fclose( $fh );
			return array();
		}
		$headers = array_map( 'trim', $headers );
		$count   = count( $headers );

		$rows = array();
		while ( ( $row = fgetcsv( $fh ) ) !== false ) {
			if ( count( $row ) < $count ) {
				continue; // malformed / short line
			}
			$rows[] = array_combine(
				$headers,
				array_map( 'trim', array_slice( $row, 0, $count ) )
			);
		}
		fclose( $fh );

		return $rows;
	}

	/**
	 * Two-column "key,value" CSV → [ key => value ]. [] when absent.
	 */
	public static function kv( $name ) {
		$rows = self::rows( $name );
		if ( ! $rows ) {
			return array();
		}
		$out = array();
		foreach ( $rows as $row ) {
			$k = trim( $row['key'] ?? '' );
			if ( '' !== $k ) {
				$out[ $k ] = $row['value'] ?? '';
			}
		}
		return $out;
	}
}
