<?php
/**
 * PT_Stories_Data
 *
 * Centralised data access for the Stories section.
 * Source of truth: real_data/csv/stories-list.csv + real_data/json/stories-page.json
 *
 * Usage in intermediate_pages/stories.php:
 *   require_once get_template_directory() . '/includes/data/class-stories-data.php';
 *   $hero     = PT_Stories_Data::page_hero();
 *   $stories  = PT_Stories_Data::all();
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PT_Stories_Data {

	/* ── Paths ──────────────────────────────────────────────────── */

	private static function csv_path(): string {
		return get_template_directory() . '/real_data/csv/stories-list.csv';
	}

	private static function json_path(): string {
		return get_template_directory() . '/real_data/json/stories-page.json';
	}

	/* ── Page-level content ─────────────────────────────────────── */

	public static function page_hero(): array {
		$page = self::load_json();
		return $page['hero'] ?? [
			'tag'         => 'Client Stories',
			'heading'     => 'Results That Speak for Themselves',
			'description' => 'Real outcomes from real partnerships.',
			'cta_label'   => 'Start Your Story',
			'cta_url'     => '/contact',
		];
	}

	public static function grid_heading(): array {
		$page = self::load_json();
		return $page['grid_heading'] ?? [
			'tag'     => 'More Success Stories',
			'heading' => 'Every Project Is Unique',
		];
	}

	public static function cta_section(): array {
		$page = self::load_json();
		return $page['cta_section'] ?? [
			'tag'                 => 'Work With Us',
			'heading'             => 'Ready to Write Your Success Story?',
			'description'         => "We would love to help.",
			'cta_primary_label'   => 'Get in Touch',
			'cta_primary_url'     => '/contact',
			'cta_secondary_label' => 'See Our Services',
			'cta_secondary_url'   => '/services',
		];
	}

	/* ── Story records ──────────────────────────────────────────── */

	/** All published stories as an array of associative arrays. */
	public static function all( bool $published_only = true ): array {
		$rows = self::load_csv();

		if ( $published_only ) {
			$rows = array_values( array_filter( $rows, static fn( $r ) => ! empty( $r['published'] ) ) );
		}

		return $rows;
	}

	/** The first story marked featured = 1, or the first story overall. */
	public static function featured(): ?array {
		foreach ( self::all() as $s ) {
			if ( ! empty( $s['featured'] ) ) return $s;
		}
		return self::all()[0] ?? null;
	}

	/** All published stories that are NOT featured. */
	public static function non_featured(): array {
		return array_values( array_filter( self::all(), static fn( $s ) => empty( $s['featured'] ) ) );
	}

	/** Find one story by its `id` column. Returns null if not found. */
	public static function find( string $id ): ?array {
		foreach ( self::all( false ) as $s ) {
			if ( ( $s['id'] ?? '' ) === $id ) return $s;
		}
		return null;
	}

	/* ── Raw loaders ────────────────────────────────────────────── */

	private static function load_csv(): array {
		$path = self::csv_path();
		if ( ! file_exists( $path ) ) return [];

		$handle = fopen( $path, 'r' );
		if ( ! $handle ) return [];

		$headers = fgetcsv( $handle );
		if ( ! $headers ) { fclose( $handle ); return []; }

		$rows = [];
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) === count( $headers ) ) {
				$rows[] = array_combine( $headers, $row );
			}
		}

		fclose( $handle );
		return $rows;
	}

	private static ?array $json_cache = null;

	private static function load_json(): array {
		if ( self::$json_cache !== null ) return self::$json_cache;

		$path = self::json_path();
		if ( ! file_exists( $path ) ) return self::$json_cache = [];

		$decoded = json_decode( file_get_contents( $path ), true );
		return self::$json_cache = ( is_array( $decoded ) ? $decoded : [] );
	}
}
