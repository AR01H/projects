<?php
defined( 'ABSPATH' ) || exit;

class AH_Theme_Admin {

	public static function init(): void {
		add_action( 'admin_menu',             [ self::class, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts',  [ self::class, 'enqueue_assets' ] );
		add_action( 'admin_post_ah_theme_seed',    [ self::class, 'handle_seed' ] );
		add_action( 'admin_post_ah_theme_cleanup', [ self::class, 'handle_cleanup' ] );
	}

	public static function register_menus(): void {
		add_menu_page(
			__( 'AH Theme', 'ah-theme' ),
			__( 'AH Theme', 'ah-theme' ),
			'manage_options',
			'ah-theme-admin',
			[ self::class, 'page_dashboard' ],
			'dashicons-admin-appearance',
			61
		);
		add_submenu_page( 'ah-theme-admin', __( 'Overview',        'ah-theme' ), __( 'Overview',        'ah-theme' ), 'manage_options', 'ah-theme-admin',   [ self::class, 'page_dashboard'  ] );
		add_submenu_page( 'ah-theme-admin', __( 'Install Mock Data','ah-theme' ), __( 'Install Mock Data','ah-theme' ), 'manage_options', 'ah-theme-mock',    [ self::class, 'page_mock'       ] );
		add_submenu_page( 'ah-theme-admin', __( 'Cleanup Data',    'ah-theme' ), __( 'Cleanup Data',    'ah-theme' ), 'manage_options', 'ah-theme-cleanup', [ self::class, 'page_cleanup'    ] );
	}

	public static function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'ah-theme' ) === false ) return;
		// Inline styles for the theme admin pages (reuses WP's native admin style + our overrides)
		wp_add_inline_style( 'wp-admin', self::admin_css() );
	}

	// ── Page renderers ────────────────────────────────────────────────────────

	public static function page_dashboard(): void {
		require get_template_directory() . '/admin/theme-dashboard.php';
	}

	public static function page_mock(): void {
		require get_template_directory() . '/admin/theme-mock-data.php';
	}

	public static function page_cleanup(): void {
		require get_template_directory() . '/admin/theme-cleanup.php';
	}

	// ── POST handlers ─────────────────────────────────────────────────────────

	public static function handle_seed(): void {
		check_admin_referer( 'ah_theme_seed' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/mock_data/seeder.php';
		$result = AH_Theme_Seeder::seed_all();

		$msg = 'Seeded ' . $result['inserted'] . ' rows and updated ' . $result['updated'] . ' options successfully.';
		if ( ! empty( $result['errors'] ) ) {
			$msg .= ' Warnings: ' . implode( '; ', $result['errors'] );
		}
		wp_redirect( add_query_arg( [ 'page' => 'ah-theme-mock', 'seeded' => '1', 'msg' => urlencode( $msg ) ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_cleanup(): void {
		check_admin_referer( 'ah_theme_cleanup' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/mock_data/seeder.php';
		$result = AH_Theme_Seeder::cleanup_all();

		$msg = 'Cleanup complete — ' . $result['deleted'] . ' items removed.';
		wp_redirect( add_query_arg( [ 'page' => 'ah-theme-cleanup', 'cleaned' => '1', 'msg' => urlencode( $msg ) ], admin_url( 'admin.php' ) ) );
		exit;
	}

	// ── Inline CSS ────────────────────────────────────────────────────────────

	private static function admin_css(): string {
		return '
.ah-admin-wrap { max-width:860px; }
.ah-admin-header { display:flex; align-items:center; gap:14px; margin-bottom:28px; }
.ah-admin-logo { width:44px; height:44px; background:#b7791f; border-radius:10px; display:grid; place-items:center; color:white; font-size:1.2rem; font-weight:700; flex-shrink:0; }
.ah-admin-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.ah-admin-header p { margin:2px 0 0; color:#64748b; font-size:.875rem; }
.ah-admin-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:16px; margin-bottom:28px; }
.ah-admin-card { background:white; border:1px solid #e2e8f0; border-radius:10px; padding:20px; }
.ah-admin-card__label { font-size:.75rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px; }
.ah-admin-card__value { font-size:1.6rem; font-weight:700; color:#0f172a; }
.ah-admin-card__sub { font-size:.78rem; color:#94a3b8; margin-top:4px; }
.ah-admin-card--ok .ah-admin-card__value { color:#16a34a; }
.ah-admin-card--warn .ah-admin-card__value { color:#d97706; }
.ah-admin-card--missing .ah-admin-card__value { color:#dc2626; }
.ah-admin-box { background:white; border:1px solid #e2e8f0; border-radius:10px; padding:24px 28px; margin-bottom:20px; }
.ah-admin-box h2 { font-size:1rem; font-weight:700; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; }
.ah-admin-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.ah-admin-table th { text-align:left; font-weight:600; color:#64748b; font-size:.78rem; text-transform:uppercase; letter-spacing:.06em; padding:8px 12px; border-bottom:1px solid #f1f5f9; }
.ah-admin-table td { padding:10px 12px; border-bottom:1px solid #f8fafc; }
.ah-admin-table tr:last-child td { border-bottom:none; }
.ah-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.75rem; font-weight:600; }
.ah-badge--ok   { background:#dcfce7; color:#16a34a; }
.ah-badge--warn { background:#fef3c7; color:#d97706; }
.ah-badge--missing { background:#fee2e2; color:#dc2626; }
.ah-admin-notice { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:.9rem; }
.ah-admin-notice--success { background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; }
.ah-admin-notice--error   { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }
.ah-admin-notice--warn    { background:#fef3c7; border:1px solid #fde68a; color:#92400e; }
.ah-btn-danger { background:#dc2626; color:white; border-color:#dc2626; }
.ah-btn-danger:hover { background:#b91c1c; color:white; }
';
	}
}
