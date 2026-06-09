<?php
/**
 * PT_Stories_Admin - registers the Stories admin menu, routes views, handles form submissions.
 *
 * Two views, same menu page:
 *   ?page=pt-stories              → list (all stories table)
 *   ?page=pt-stories&action=edit&id=xxx  → edit existing
 *   ?page=pt-stories&action=add          → add new
 */

defined( 'ABSPATH' ) || exit;

class PT_Stories_Admin {

	public static function init(): void {
		add_action( 'admin_menu',            [ self::class, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
		add_action( 'admin_init',            [ self::class, 'maybe_create_table' ] );
		add_action( 'admin_post_pt_story_save',    [ self::class, 'handle_save' ] );
		add_action( 'admin_post_pt_story_delete',  [ self::class, 'handle_delete' ] );
		add_action( 'admin_post_pt_story_reorder', [ self::class, 'handle_reorder' ] );
		add_action( 'admin_post_pt_theme_seed',    [ self::class, 'handle_seed' ] );
		add_action( 'admin_post_pt_theme_cleanup', [ self::class, 'handle_cleanup' ] );

		/* AJAX */
		require_once get_template_directory() . '/includes/admin/class-pt-ajax.php';
		PT_Ajax::init();
	}

	/* ── Menu ────────────────────────────────────────────────────── */

	public static function register_menus(): void {
		add_menu_page(
			'Project Theme',
			'Project Theme',
			'manage_options',
			'pt-theme',
			[ self::class, 'page_dashboard' ],
			'dashicons-layout',
			4
		);

		add_submenu_page( 'pt-theme', 'Dashboard',  'Dashboard',  'manage_options', 'pt-theme',       [ self::class, 'page_dashboard'  ] );
		add_submenu_page( 'pt-theme', 'Stories',    'Stories',    'manage_options', 'pt-stories',     [ self::class, 'page_stories'    ] );
		add_submenu_page( 'pt-theme', 'Mock Data',  'Mock Data',  'manage_options', 'pt-mock-data',   [ self::class, 'page_mock_data'  ] );
		add_submenu_page( 'pt-theme', 'Cleanup',    'Cleanup',    'manage_options', 'pt-cleanup',     [ self::class, 'page_cleanup'    ] );
	}

	/* ── Table creation (one-time) ───────────────────────────────── */

	public static function maybe_create_table(): void {
		if ( ! is_admin() ) return;
		require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';
		PT_Stories_DB::maybe_create();
	}

	/* ── Assets ──────────────────────────────────────────────────── */

	public static function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'pt-' ) === false && strpos( $hook, 'pt_' ) === false ) return;

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_add_inline_style( 'wp-admin', self::admin_css() );

		/* Admin JS - AJAX + API helper */
		$js = get_template_directory_uri() . '/assets/js/pt-admin.js';
		wp_enqueue_script( 'pt-admin', $js, [ 'jquery' ], wp_get_theme()->get( 'Version' ), true );
		wp_localize_script( 'pt-admin', 'PT_Admin', [
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'pt_admin_ajax' ),
			'apiBase'  => rest_url( 'pt/v1' ),
			'apiNonce' => wp_create_nonce( 'wp_rest' ),
		] );
	}

	/* ── Page renderers ──────────────────────────────────────────── */

	public static function page_dashboard(): void {
		require get_template_directory() . '/admin/theme-dashboard.php';
	}

	public static function page_stories(): void {
		$action = sanitize_key( $_GET['action'] ?? 'list' );

		if ( in_array( $action, [ 'edit', 'add' ], true ) ) {
			require get_template_directory() . '/admin/stories-edit.php';
		} else {
			require get_template_directory() . '/admin/stories-list.php';
		}
	}

	public static function page_mock_data(): void {
		require get_template_directory() . '/admin/theme-mock-data.php';
	}

	public static function page_cleanup(): void {
		require get_template_directory() . '/admin/theme-cleanup.php';
	}

	/* ── POST: Seed mock data ────────────────────────────────────── */

	public static function handle_seed(): void {
		check_admin_referer( 'pt_theme_seed' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/mock_data/seeder.php';

		$type = sanitize_key( $_POST['seed_type'] ?? 'all' );

		if ( $type === 'schema' ) {
			$result = PT_Theme_Seeder::seed_schema_only();
			$msg    = 'Schema installed via dbDelta.';
		} elseif ( $type === 'selected' ) {
			$selected = array_map( 'sanitize_key', (array) ( $_POST['seed_types'] ?? [] ) );
			$result   = PT_Theme_Seeder::seed_all(); /* expand when more types are added */
			$msg      = 'Selected mock data installed: ' . $result['inserted'] . ' inserted, ' . ( $result['skipped'] ?? 0 ) . ' skipped.';
		} else {
			$result = PT_Theme_Seeder::seed_all();
			$msg    = 'Mock data installed: ' . $result['inserted'] . ' inserted, ' . ( $result['skipped'] ?? 0 ) . ' skipped.';
		}

		if ( ! empty( $result['errors'] ) ) {
			$msg .= ' Errors: ' . implode( '; ', $result['errors'] );
		}

		wp_redirect( add_query_arg(
			[ 'page' => 'pt-mock-data', 'seeded' => empty( $result['errors'] ) ? '1' : '0', 'msg' => urlencode( $msg ) ],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/* ── POST: Cleanup ───────────────────────────────────────────── */

	public static function handle_cleanup(): void {
		check_admin_referer( 'pt_theme_cleanup' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/mock_data/seeder.php';

		$result = PT_Theme_Seeder::cleanup_all();
		$msg    = 'Cleanup complete - ' . $result['deleted'] . ' rows removed.';

		wp_redirect( add_query_arg(
			[ 'page' => 'pt-cleanup', 'cleaned' => '1', 'msg' => urlencode( $msg ) ],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/* ── POST: Save story ────────────────────────────────────────── */

	public static function handle_save(): void {
		check_admin_referer( 'pt_story_save' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';

		$data = [
			'id'             => sanitize_title( $_POST['id']             ?? '' ),
			'title'          => $_POST['title']          ?? '',
			'client'         => $_POST['client']         ?? '',
			'industry'       => $_POST['industry']       ?? '',
			'tagline'        => $_POST['tagline']        ?? '',
			'summary'        => $_POST['summary']        ?? '',
			'result_1_label' => $_POST['result_1_label'] ?? '',
			'result_1_value' => $_POST['result_1_value'] ?? '',
			'result_2_label' => $_POST['result_2_label'] ?? '',
			'result_2_value' => $_POST['result_2_value'] ?? '',
			'result_3_label' => $_POST['result_3_label'] ?? '',
			'result_3_value' => $_POST['result_3_value'] ?? '',
			'image'          => $_POST['image']          ?? '',
			'featured'       => $_POST['featured']       ?? '',
			'published'      => $_POST['published']      ?? '',
			'sort_order'     => $_POST['sort_order']     ?? 0,
		];

		if ( empty( $data['id'] ) ) {
			wp_redirect( add_query_arg( [ 'page' => 'pt-stories', 'action' => 'add', 'error' => 'noid' ], admin_url( 'admin.php' ) ) );
			exit;
		}

		$ok = PT_Stories_DB::save( $data );

		wp_redirect( add_query_arg(
			[ 'page' => 'pt-stories', 'saved' => $ok ? '1' : '0', 'id' => $data['id'] ],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/* ── POST: Delete story ──────────────────────────────────────── */

	public static function handle_delete(): void {
		check_admin_referer( 'pt_story_delete' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';

		$id = sanitize_title( $_POST['id'] ?? '' );
		if ( $id ) PT_Stories_DB::delete( $id );

		wp_redirect( add_query_arg( [ 'page' => 'pt-stories', 'deleted' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	/* ── POST: Reorder stories ───────────────────────────────────── */

	public static function handle_reorder(): void {
		check_admin_referer( 'pt_story_reorder' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';

		$order = array_map( 'sanitize_title', (array) ( $_POST['order'] ?? [] ) );
		if ( $order ) PT_Stories_DB::reorder( $order );

		wp_redirect( add_query_arg( [ 'page' => 'pt-stories', 'reordered' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	/* ── Inline CSS ──────────────────────────────────────────────── */

	private static function admin_css(): string {
		return '
/* ── PT Admin shell ────────────────────────────────────────── */
.pt-admin-wrap { max-width:1100px; }
.pt-admin-header { display:flex; align-items:center; gap:14px; margin-bottom:28px; }
.pt-admin-logo { width:44px; height:44px; background:var(--pt-color-2,#2a4a82); border-radius:10px; display:grid; place-items:center; color:#fff; font-size:1.1rem; font-weight:700; flex-shrink:0; }
.pt-admin-header h1 { font-size:1.4rem; font-weight:700; margin:0; }
.pt-admin-header p  { margin:2px 0 0; color:#64748b; font-size:.875rem; }
.pt-admin-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px; margin-bottom:24px; }
.pt-admin-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; }
.pt-admin-card__label { font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; margin-bottom:5px; }
.pt-admin-card__value { font-size:1.55rem; font-weight:700; color:#0f172a; }
.pt-admin-card__sub   { font-size:.75rem; color:#94a3b8; margin-top:3px; }
.pt-admin-card--ok   .pt-admin-card__value { color:#16a34a; }
.pt-admin-card--warn .pt-admin-card__value { color:#d97706; }
.pt-admin-box { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px 26px; margin-bottom:20px; }
.pt-admin-box h2 { font-size:.95rem; font-weight:700; margin:0 0 14px; padding-bottom:10px; border-bottom:1px solid #f1f5f9; }
.pt-notice { padding:11px 16px; border-radius:7px; margin-bottom:16px; font-size:.875rem; }
.pt-notice--ok   { background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; }
.pt-notice--err  { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }

/* ── Stories table ──────────────────────────────────────────── */
.pt-stories-table-wrap { background:#fff; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; }
.pt-stories-toolbar { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid #f1f5f9; gap:12px; }
.pt-stories-toolbar h2 { margin:0; font-size:1rem; font-weight:700; }
.pt-stories-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.pt-stories-table th { text-align:left; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; padding:10px 14px; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
.pt-stories-table td { padding:11px 14px; border-bottom:1px solid #f8fafc; vertical-align:middle; }
.pt-stories-table tr:last-child td { border-bottom:none; }
.pt-stories-table tr:hover td { background:#f8fafc; }
.pt-stories-table .col-sort { width:40px; text-align:center; cursor:grab; color:#cbd5e1; font-size:1.1rem; }
.pt-stories-table .col-sort:active { cursor:grabbing; }
.pt-stories-table .col-cb { width:32px; }
.pt-stories-table .col-order { width:60px; text-align:center; }
.pt-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:700; letter-spacing:.03em; }
.pt-badge--yes  { background:#dcfce7; color:#16a34a; }
.pt-badge--no   { background:#f1f5f9; color:#94a3b8; }
.pt-row-actions { display:flex; gap:8px; align-items:center; margin-top:4px; }
.pt-row-actions a { font-size:.78rem; color:#3b82f6; text-decoration:none; }
.pt-row-actions a:hover { text-decoration:underline; }
.pt-row-actions .del { color:#ef4444; }
.pt-row-actions form { display:inline; }
.pt-row-actions button { background:none; border:none; padding:0; font-size:.78rem; color:#ef4444; cursor:pointer; text-decoration:none; font-family:inherit; }
.pt-row-actions button:hover { text-decoration:underline; }
.pt-story-title { font-weight:600; color:#0f172a; font-size:.9rem; }
.pt-story-id    { font-size:.72rem; color:#94a3b8; margin-top:2px; font-family:monospace; }

/* ── Drag handle ─────────────────────────────────────────────── */
.pt-drag-handle { cursor:grab; color:#cbd5e1; font-size:1.2rem; user-select:none; }
.pt-drag-handle:hover { color:#94a3b8; }
.pt-sortable-ghost { opacity:.4; background:#fef3c7 !important; }

/* ── Edit form tabs ──────────────────────────────────────────── */
.pt-tabs { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:28px; }
.pt-tab-btn { background:none; border:none; padding:11px 20px; font-size:.9rem; font-weight:600; color:#64748b; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .15s; }
.pt-tab-btn.is-active { color:#2a4a82; border-bottom-color:#2a4a82; }
.pt-tab-btn:hover:not(.is-active) { color:#2a4a82; }
.pt-tab-pane { display:none; }
.pt-tab-pane.is-active { display:block; }

/* ── Form layout ─────────────────────────────────────────────── */
.pt-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px 28px; }
.pt-form-grid--3 { grid-template-columns:1fr 1fr 1fr; }
.pt-form-group { display:flex; flex-direction:column; gap:6px; }
.pt-form-group.full { grid-column:1 / -1; }
.pt-form-group label { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; }
.pt-form-group input[type="text"],
.pt-form-group input[type="url"],
.pt-form-group input[type="number"],
.pt-form-group textarea,
.pt-form-group select {
    width:100%; padding:9px 12px; border:1.5px solid #e2e8f0; border-radius:7px;
    font-size:.9rem; font-family:inherit; background:#fff; color:#0f172a;
    transition:border-color .15s;
}
.pt-form-group input:focus,
.pt-form-group textarea:focus { border-color:#2a4a82; outline:none; box-shadow:0 0 0 3px rgba(42,74,130,.1); }
.pt-form-group textarea { resize:vertical; min-height:100px; }
.pt-form-hint { font-size:.75rem; color:#94a3b8; }
.pt-form-checkbox-row { display:flex; align-items:center; gap:10px; padding:10px 0; }
.pt-form-checkbox-row input[type="checkbox"] { width:18px; height:18px; accent-color:#2a4a82; cursor:pointer; }
.pt-form-checkbox-row label { font-size:.9rem; font-weight:600; color:#0f172a; cursor:pointer; margin:0; }

/* ── Results card ────────────────────────────────────────────── */
.pt-result-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; }
.pt-result-card h3 { font-size:.85rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; margin:0 0 14px; }
.pt-results-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.pt-result-pair { display:flex; flex-direction:column; gap:8px; }
.pt-result-pair label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; }
.pt-result-preview { display:flex; gap:8px; align-items:flex-end; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-top:4px; }
.pt-result-preview__val { font-size:1.4rem; font-weight:800; color:#2a4a82; line-height:1; }
.pt-result-preview__lbl { font-size:.75rem; color:#64748b; }
';
	}
}
