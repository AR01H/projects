<?php
/**
 * CH_CSV_Loader - the INSTANT (runtime) CSV reader.
 *
 * This reader lives on its own in the  csv/  folder.
 * The CSV DATA files it reads live in   mock_data/csv/  (the same files the
 * mock-data seeder inserts into the database).
 *
 * Used for INSTANT fetching: ch_get_*() in includes/helpers.php fall back to
 * CH_Data, which delegates its CSV reading here. So when the database is empty,
 * the live site reads the CSV files directly through this class.
 *
 * Two CSV shapes are supported:
 *   • Row CSVs        - first line = column headers, one item per row.
 *                       load_csv() returns an array of associative rows.
 *   • Key-value CSVs  - two columns "key,value" (settings.csv, etc.).
 *                       load_kv_csv() returns [ key => value ].
 */
defined( 'ABSPATH' ) || exit;

class CH_CSV_Loader {

	/** Absolute path to the CSV data directory (the seeder's source files). */
	public static function dir(): string {
		return get_template_directory() . '/mock_data/csv';
	}

	/** Absolute path to a single CSV file. */
	public static function path( string $name ): string {
		return self::dir() . '/' . $name . '.csv';
	}

	/** Does a given CSV file exist? */
	public static function exists( string $name ): bool {
		return file_exists( self::path( $name ) );
	}

	/** List every available CSV name (without the .csv extension). */
	public static function list_files(): array {
		$names = array();
		foreach ( (array) glob( self::dir() . '/*.csv' ) as $file ) {
			$names[] = basename( $file, '.csv' );
		}
		sort( $names );
		return $names;
	}

	/**
	 * Read a row-style CSV into an array of associative rows.
	 * Returns [] when the file is missing or empty.
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function load_csv( string $name ): array {
		$path = self::path( $name );
		if ( ! file_exists( $path ) ) {
			return array();
		}
		$fh = fopen( $path, 'r' );
		if ( false === $fh ) {
			return array();
		}

		// Skip a UTF-8 BOM if present (Excel adds one).
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

		$rows = array();
		while ( ( $row = fgetcsv( $fh ) ) !== false ) {
			if ( count( $row ) < count( $headers ) ) {
				continue; // skip malformed / short lines
			}
			// Truncate extra columns so array_combine never mismatches.
			$rows[] = array_combine(
				$headers,
				array_map( 'trim', array_slice( $row, 0, count( $headers ) ) )
			);
		}
		fclose( $fh );
		return $rows;
	}

	/**
	 * Read a two-column key/value CSV into [ key => value ].
	 *
	 * @return array<string,string>
	 */
	public static function load_kv_csv( string $name ): array {
		$rows = self::load_csv( $name );
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

	/**
	 * Quick inspection helper - return parsed rows of a CSV for printing/debug.
	 * Example:  echo '<pre>'; print_r( CH_CSV_Loader::preview( 'flavours' ) ); echo '</pre>';
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function preview( string $name ): array {
		return self::load_csv( $name );
	}
}
