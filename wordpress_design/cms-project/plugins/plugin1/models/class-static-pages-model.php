<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Static_Pages_Model
 *
 * Stores raw HTML "static page / component" content in the database
 * ({prefix}ah_static_pages) instead of flat static/{slug}.html files.
 *
 *   • slug    — matches the backing WP page's post_name (resolves the permalink)
 *   • html    — the raw HTML the user pastes in the editor
 *   • page_id — the backing WP page (kept for permalink + template routing)
 *
 * The table DDL lives ONLY in AH_DB_Schema (table 41d). This model reads/writes
 * and self-heals if the table is missing. A one-time migration imports any
 * legacy static/*.html files so nothing is lost.
 */
class AH_Static_Pages_Model {

	private string $table_suffix = 'static_pages';
	private static bool $table_ready = false;

	public function table(): string {
		return AH_DB_Helper::table( $this->table_suffix );
	}

	/** Self-heal: the schema owns the DDL; if missing, run the idempotent installer once. */
	public static function ensure_table(): void {
		if ( self::$table_ready ) {
			return;
		}
		global $wpdb;
		$table  = AH_DB_Helper::table( 'static_pages' );
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( ! $exists && class_exists( 'AH_DB_Installer' ) ) {
			AH_DB_Installer::install();
		}
		self::$table_ready = true;
	}

	private static function clean_slug( string $slug ): string {
		return strtolower( preg_replace( '/[^a-z0-9-]/', '', sanitize_title( $slug ) ) );
	}

	// ── Read ─────────────────────────────────────────────────────────────────
	public function get_by_slug( string $slug ): ?object {
		global $wpdb;
		self::ensure_table();
		$table = $this->table();
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE slug = %s LIMIT 1",
			self::clean_slug( $slug )
		) );
	}

	/** Returns the stored HTML for a slug, or '' if none. */
	public function get_html( string $slug ): string {
		$row = $this->get_by_slug( $slug );
		return $row ? (string) $row->html : '';
	}

	public function all(): array {
		global $wpdb;
		self::ensure_table();
		$table = $this->table();
		return $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY slug ASC" ) ?: array();
	}

	public function exists( string $slug ): bool {
		return (bool) $this->get_by_slug( $slug );
	}

	// ── Write ────────────────────────────────────────────────────────────────
	/** Insert or update a static page by slug. Returns the row id. */
	public function upsert( string $slug, string $html, int $page_id = 0, string $title = '' ): int {
		global $wpdb;
		self::ensure_table();

		$slug  = self::clean_slug( $slug );
		if ( $slug === '' ) {
			return 0;
		}
		$title = $title !== '' ? sanitize_text_field( $title ) : ucwords( str_replace( '-', ' ', $slug ) );
		$table = $this->table();
		$row   = $this->get_by_slug( $slug );

		$data = array(
			'slug'    => $slug,
			'title'   => $title,
			'html'    => $html,
			'page_id' => $page_id ?: null,
			'status'  => 'active',
		);

		if ( $row ) {
			$wpdb->update( $table, $data, array( 'id' => (int) $row->id ) );
			return (int) $row->id;
		}
		$wpdb->insert( $table, $data );
		return (int) $wpdb->insert_id;
	}

	public function delete_by_slug( string $slug ): void {
		global $wpdb;
		self::ensure_table();
		$wpdb->delete( $this->table(), array( 'slug' => self::clean_slug( $slug ) ), array( '%s' ) );
	}

	// ── One-time migration: import legacy static/*.html into the DB ──────────
	/**
	 * Idempotent. For each static/*.html in the active theme that is not yet in
	 * the DB, insert a row. Existing DB rows are never overwritten.
	 */
	public static function import_files_once(): void {
		self::ensure_table();

		$dir = trailingslashit( get_template_directory() ) . 'static/';
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = glob( $dir . '*.html' ) ?: array();
		if ( ! $files ) {
			return;
		}

		$model = new self();
		foreach ( $files as $file ) {
			$slug = self::clean_slug( basename( $file, '.html' ) );
			if ( $slug === '' || $model->exists( $slug ) ) {
				continue;
			}
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_get_contents
			$html = (string) file_get_contents( $file );
			$page = get_page_by_path( $slug );
			$model->upsert( $slug, $html, $page ? (int) $page->ID : 0 );
		}
	}
}
