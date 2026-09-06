<?php
namespace VintageSoul\Services\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * AdminBridgeService — Manages theme-level admin menu exclusions, direct URL protections, and admin CSS overrides.
 */
class AdminBridgeService {

	/**
	 * Slugs to exclude from CMS Admin menu and block direct access
	 *
	 * @var array<int, string>
	 */
	protected static array $excluded_slugs = array(
		'ah-taxonomy',
		'ah-newsletter',
		'ah-notifications',
		'ah-news-bar',
		'ah-media',
		'ah-import',
	);

	/**
	 * Slug of the theme's own "Theme Settings" admin page (Google services,
	 * theme cache clear, cookie-consent re-ask). A new submenu the theme adds
	 * itself, not an override of any existing plugin admin page.
	 */
	public const THEME_SETTINGS_SLUG = 'vst-theme-settings';

	public static function register_hooks(): void {
		add_filter( 'ah_admin_menu_exclude_slugs', array( static::class, 'filter_excluded_slugs' ) );
		add_action( 'admin_menu', array( static::class, 'remove_submenus' ), 999 );
		add_action( 'admin_menu', array( static::class, 'register_theme_settings_menu' ), 20 );
		add_action( 'admin_init', array( static::class, 'block_excluded_admin_pages' ) );
		add_action( 'admin_enqueue_scripts', array( static::class, 'enqueue_admin_styles' ), 99 );
		add_action( 'admin_head', array( static::class, 'inject_admin_head_css' ), 99 );
	}

	/**
	 * Register the theme's own admin menu - its own top-level sidebar item
	 * (like the plugin's "CMS ADMIN"), not tucked away as a submenu under it.
	 */
	public static function register_theme_settings_menu(): void {
		if ( ! function_exists( 'add_menu_page' ) ) {
			return;
		}
		add_menu_page(
			__( 'VintageSoul Theme', 'vintagesoul' ),
			__( 'VintageSoul Theme', 'vintagesoul' ),
			'manage_options',
			self::THEME_SETTINGS_SLUG,
			array( static::class, 'render_theme_settings_page' ),
			'dashicons-admin-appearance',
			4 // Right after the plugin's "CMS ADMIN" (position 3).
		);
	}

	/**
	 * Render the theme's own admin page from admin/pages/ThemeSettings.php.
	 */
	public static function render_theme_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied.' );
		}
		$file = VINTAGESOUL_DIR . '/admin/pages/ThemeSettings.php';
		if ( is_file( $file ) ) {
			include $file;
		}
	}

	/**
	 * Exclude specific slugs from AH_Admin_Menus registration
	 *
	 * @param array<int, string> $slugs
	 * @return array<int, string>
	 */
	public static function filter_excluded_slugs( array $slugs = array() ): array {
		return array_values( array_unique( array_merge( (array) $slugs, static::$excluded_slugs ) ) );
	}

	/**
	 * Remove submenus from WordPress admin sidebar
	 */
	public static function remove_submenus(): void {
		foreach ( static::$excluded_slugs as $slug ) {
			remove_submenu_page( 'ah-dashboard', $slug );
			remove_submenu_page( 'ah-cms', $slug );
			remove_menu_page( $slug );
		}
	}

	/**
	 * Block direct URL access to excluded admin pages and redirect to dashboard
	 */
	public static function block_excluded_admin_pages(): void {
		if ( ! is_admin() || ! isset( $_GET['page'] ) ) {
			return;
		}

		$page = sanitize_key( (string) $_GET['page'] );
		if ( in_array( $page, static::$excluded_slugs, true ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=ah-dashboard' ) );
			exit;
		}
	}

	/**
	 * Enqueue admin styles file
	 */
	public static function enqueue_admin_styles(): void {
		$css_file = VINTAGESOUL_DIR . '/assets/css/admin-overrides.css';
		if ( is_file( $css_file ) ) {
			wp_enqueue_style(
				'vst-admin-overrides',
				VINTAGESOUL_URI . '/assets/css/admin-overrides.css',
				array(),
				VINTAGESOUL_VERSION
			);
		}
	}

	/**
	 * Inject admin CSS into <head> for fast and reliable hiding of Edit Meta and excluded items
	 */
	public static function inject_admin_head_css(): void {
		?>
		<style id="vst-admin-overrides-inline">
			/* ── Hide "Edit Meta" Button & Modal in ah-posts ── */
			button.ah-qe-open,
			.ah-qe-open,
			.ah-qe-modal {
				display: none !important;
			}

			/* ── Hide Excluded Admin Menus from Sidebar ── */
			#adminmenu a[href*="page=ah-taxonomy"],
			#adminmenu a[href*="page=ah-newsletter"],
			#adminmenu a[href*="page=ah-notifications"],
			#adminmenu a[href*="page=ah-news-bar"],
			#adminmenu a[href*="page=ah-media"],
			#adminmenu a[href*="page=ah-import"],
			#adminmenu li:has(> a[href*="page=ah-taxonomy"]),
			#adminmenu li:has(> a[href*="page=ah-newsletter"]),
			#adminmenu li:has(> a[href*="page=ah-notifications"]),
			#adminmenu li:has(> a[href*="page=ah-news-bar"]),
			#adminmenu li:has(> a[href*="page=ah-media"]),
			#adminmenu li:has(> a[href*="page=ah-import"]) {
				display: none !important;
			}

			/* ── Hide Excluded Cards & Links on CMS Dashboard ── */
			.ah-stats-grid a[href*="page=ah-media"],
			.ah-stats-grid a[href*="page=ah-taxonomy"],
			.ah-stats-grid a[href*="page=ah-newsletter"],
			.ah-stats-grid a[href*="page=ah-notifications"],
			.ah-stats-grid a[href*="page=ah-news-bar"],
			.ah-stats-grid a[href*="page=ah-import"],
			.ah-quick-actions a[href*="page=ah-media"],
			.ah-quick-actions a[href*="page=ah-taxonomy"],
			.ah-quick-actions a[href*="page=ah-newsletter"],
			.ah-quick-actions a[href*="page=ah-notifications"],
			.ah-quick-actions a[href*="page=ah-news-bar"],
			.ah-quick-actions a[href*="page=ah-import"] {
				display: none !important;
			}
		</style>
		<?php
	}
}
