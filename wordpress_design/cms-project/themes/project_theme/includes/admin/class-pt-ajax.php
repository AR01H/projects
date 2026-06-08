<?php
/**
 * PT_Ajax — admin AJAX handlers.
 *
 * All handlers require manage_options + a per-action nonce.
 * Registered via wp_ajax_pt_* hooks (admin-only, no nopriv).
 *
 * Actions:
 *   pt_schema_install   — run dbDelta on all tables
 *   pt_schema_drop      — DROP all theme tables
 *   pt_seed_mock        — seed all mock data from CSV
 *   pt_cleanup          — DELETE all rows (keep tables)
 *   pt_get_status       — return table counts + schema state
 */

defined( 'ABSPATH' ) || exit;

class PT_Ajax {

	public static function init(): void {
		$actions = [
			'pt_schema_install',
			'pt_schema_drop',
			'pt_seed_mock',
			'pt_cleanup',
			'pt_get_status',
		];
		foreach ( $actions as $action ) {
			add_action( 'wp_ajax_' . $action, [ self::class, 'dispatch' ] );
		}
	}

	/* ── Dispatcher ──────────────────────────────────────────────── */

	public static function dispatch(): void {
		$action = sanitize_key( $_POST['action'] ?? '' );

		/* Every AJAX call must carry a valid nonce */
		check_ajax_referer( 'pt_admin_ajax', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorised.' ], 403 );
		}

		require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';

		switch ( $action ) {
			case 'pt_schema_install':
				self::handle_schema_install();
				break;
			case 'pt_schema_drop':
				self::handle_schema_drop();
				break;
			case 'pt_seed_mock':
				self::handle_seed_mock();
				break;
			case 'pt_cleanup':
				self::handle_cleanup();
				break;
			case 'pt_get_status':
				self::handle_get_status();
				break;
			default:
				wp_send_json_error( [ 'message' => 'Unknown action.' ], 400 );
		}
	}

	/* ── Handlers ────────────────────────────────────────────────── */

	private static function handle_schema_install(): void {
		PT_Stories_DB::create_table();
		/* Force re-detection on next status poll */
		delete_option( PT_Stories_DB::DB_VERSION );
		update_option( PT_Stories_DB::DB_VERSION, true );

		wp_send_json_success( [
			'message' => 'Schema installed / updated via dbDelta.',
			'counts'  => self::get_counts(),
			'schema'  => self::get_schema_state(),
		] );
	}

	private static function handle_schema_drop(): void {
		PT_Stories_DB::drop_table();

		wp_send_json_success( [
			'message' => 'All theme tables dropped.',
			'counts'  => [],
			'schema'  => self::get_schema_state(),
		] );
	}

	private static function handle_seed_mock(): void {
		require_once get_template_directory() . '/mock_data/seeder.php';

		/* Ensure table exists before seeding */
		PT_Stories_DB::create_table();
		$result = PT_Theme_Seeder::seed_all();

		$msg = 'Seeded: ' . $result['inserted'] . ' inserted, '
		     . ( $result['skipped'] ?? 0 ) . ' skipped.';
		if ( ! empty( $result['errors'] ) ) {
			$msg .= ' Errors: ' . implode( '; ', $result['errors'] );
		}

		wp_send_json_success( [
			'message' => $msg,
			'counts'  => self::get_counts(),
		] );
	}

	private static function handle_cleanup(): void {
		require_once get_template_directory() . '/mock_data/seeder.php';
		$result = PT_Theme_Seeder::cleanup_all();

		wp_send_json_success( [
			'message' => 'Cleanup complete — ' . $result['deleted'] . ' rows removed.',
			'counts'  => self::get_counts(),
		] );
	}

	private static function handle_get_status(): void {
		wp_send_json_success( [
			'counts' => self::get_counts(),
			'schema' => self::get_schema_state(),
		] );
	}

	/* ── Helpers ─────────────────────────────────────────────────── */

	/**
	 * Returns row counts for all managed tables.
	 * @return array<string,int>
	 */
	public static function get_counts(): array {
		return [
			'stories' => PT_Stories_DB::count(),
		];
	}

	/**
	 * Returns schema state for all managed tables.
	 * @return array<string,array{exists:bool,version:string|false}>
	 */
	public static function get_schema_state(): array {
		global $wpdb;

		$tables = [
			'stories' => PT_Stories_DB::table(),
		];

		$state = [];
		foreach ( $tables as $key => $table ) {
			$exists         = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$state[ $key ] = [
				'table'   => $table,
				'exists'  => $exists,
				'version' => get_option( PT_Stories_DB::DB_VERSION ) ? PT_Stories_DB::DB_VERSION : false,
			];
		}

		return $state;
	}
}
