<?php
/**
 * PT_Theme_Seeder - installs mock data for project_theme.
 *
 * Triggered from WP Admin → Project Theme → Mock Data.
 * All operations are idempotent (safe to run multiple times).
 */

defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';

class PT_Theme_Seeder {

	/* ── Entry points ────────────────────────────────────────────── */

	/**
	 * Full install: schema + all mock content.
	 * @return array{inserted:int,updated:int,skipped:int,errors:string[]}
	 */
	public static function seed_all(): array {
		PT_Stories_DB::create_table();
		return self::run_methods( [ 'seed_stories' ] );
	}

	/**
	 * Schema only: creates DB tables without inserting demo rows.
	 * @return array{inserted:int,updated:int,skipped:int,errors:string[]}
	 */
	public static function seed_schema_only(): array {
		PT_Stories_DB::create_table();
		return [ 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [] ];
	}

	/**
	 * Wipe all seeded data (does NOT drop tables).
	 * @return array{deleted:int}
	 */
	public static function cleanup_all(): array {
		global $wpdb;
		$t       = PT_Stories_DB::table();
		$deleted = (int) $wpdb->query( "DELETE FROM `{$t}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return [ 'deleted' => $deleted ];
	}

	/**
	 * Row counts for the admin dashboard status cards.
	 * @return array<string,int|string>
	 */
	public static function table_counts(): array {
		return [
			'stories' => PT_Stories_DB::count(),
		];
	}

	/* ── Seeders ─────────────────────────────────────────────────── */

	/**
	 * Seeds stories from mock_data/csv/stories.csv.
	 * Skips rows where the id already exists in the DB.
	 */
	public static function seed_stories(): array {
		$rows     = self::load_csv( 'stories' );
		$inserted = $skipped = 0;

		foreach ( $rows as $row ) {
			$id = sanitize_title( $row['id'] ?? '' );
			if ( ! $id ) continue;

			if ( PT_Stories_DB::find( $id ) ) {
				$skipped++;
				continue;
			}

			$ok = PT_Stories_DB::save( [
				'id'             => $id,
				'title'          => $row['title']          ?? '',
				'client'         => $row['client']         ?? '',
				'industry'       => $row['industry']       ?? '',
				'tagline'        => $row['tagline']        ?? '',
				'summary'        => $row['summary']        ?? '',
				'result_1_label' => $row['result_1_label'] ?? '',
				'result_1_value' => $row['result_1_value'] ?? '',
				'result_2_label' => $row['result_2_label'] ?? '',
				'result_2_value' => $row['result_2_value'] ?? '',
				'result_3_label' => $row['result_3_label'] ?? '',
				'result_3_value' => $row['result_3_value'] ?? '',
				'image'          => $row['image']          ?? '',
				'featured'       => $row['featured']       ?? 0,
				'published'      => $row['published']      ?? 1,
				'sort_order'     => $row['sort_order']     ?? 0,
			] );

			if ( $ok ) $inserted++;
		}

		return [ 'inserted' => $inserted, 'updated' => 0, 'skipped' => $skipped ];
	}

	/* ── CSV loader ──────────────────────────────────────────────── */

	/**
	 * Load a CSV from mock_data/csv/{name}.csv.
	 * Returns an array of associative arrays (first row = header).
	 *
	 * @param  string $name  Filename without extension
	 * @return array<int,array<string,string>>
	 */
	public static function load_csv( string $name ): array {
		$path = get_template_directory() . '/mock_data/csv/' . sanitize_file_name( $name ) . '.csv';

		if ( ! file_exists( $path ) ) return [];

		$realpath = realpath( $path );
		$allowed  = realpath( get_template_directory() . '/mock_data/csv' );
		if ( ! $realpath || strpos( $realpath, $allowed ) !== 0 ) return [];

		$rows    = [];
		$headers = null;

		if ( ( $fh = fopen( $realpath, 'r' ) ) === false ) return []; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

		while ( ( $cols = fgetcsv( $fh ) ) !== false ) {
			if ( $headers === null ) {
				$headers = array_map( 'trim', $cols );
				continue;
			}
			if ( count( $cols ) < count( $headers ) ) {
				$cols = array_pad( $cols, count( $headers ), '' );
			}
			$rows[] = array_combine( $headers, $cols );
		}

		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return $rows;
	}

	/* ── Internal helper ─────────────────────────────────────────── */

	/** @return array{inserted:int,updated:int,skipped:int,errors:string[]} */
	private static function run_methods( array $methods ): array {
		$results = [ 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [] ];

		foreach ( $methods as $method ) {
			try {
				$r = self::$method();
				$results['inserted'] += $r['inserted'] ?? 0;
				$results['updated']  += $r['updated']  ?? 0;
				$results['skipped']  += $r['skipped']  ?? 0;
			} catch ( \Throwable $e ) {
				$results['errors'][] = "{$method}: " . $e->getMessage();
			}
		}

		return $results;
	}
}
