<?php
/**
 * core/class-theme.php - Wires the whole theme together in an OOP structure.
 */

defined( 'ABSPATH' ) || exit;

class App_Theme {

	public static function init() {
		// Load Constants
		require_once __DIR__ . '/../config/theme.php';

		// Load Core Engine Classes
		require_once __DIR__ . '/class-helpers.php';
		require_once __DIR__ . '/class-data-provider.php';
		require_once __DIR__ . '/class-database.php';
		require_once __DIR__ . '/class-router.php';
		require_once __DIR__ . '/class-assets.php';
		require_once __DIR__ . '/class-ajax.php';
		require_once __DIR__ . '/class-rest.php';

		if ( is_admin() ) {
			require_once __DIR__ . '/class-admin.php';
			App_Admin::boot();
		}

		self::include_mapped_files();
		self::register_hooks();
	}

	public static function config( $name ) {
		static $cache = array();
		$name = basename( (string) $name, '.php' );
		if ( isset( $cache[ $name ] ) ) {
			return $cache[ $name ];
		}
		$file = NT_THEME_DIR . '/config/' . $name . '.php';
		$data = is_file( $file ) ? require $file : array();
		$cache[ $name ] = apply_filters( 'app_config_' . $name, is_array( $data ) ? $data : array() );
		return $cache[ $name ];
	}

	private static function include_mapped_files() {
		$map     = self::config( 'files' );
		$buckets = array( 'always' );
		$buckets[] = is_admin() ? 'admin' : 'front';

		foreach ( $buckets as $bucket ) {
			foreach ( (array) ( $map[ $bucket ] ?? array() ) as $rel ) {
				App_Helpers::require_theme_file( $rel );
			}
		}
	}

	private static function register_hooks() {
		add_action( 'after_setup_theme', array( __CLASS__, 'setup_theme' ) );
		
		add_action( 'wp_enqueue_scripts', array( 'App_Assets', 'global_assets' ) );
		add_action( 'wp_enqueue_scripts', array( 'App_Assets', 'page_assets' ), 20 );
		
		add_filter( 'template_include', array( 'App_Router', 'static_pages' ), 98 );
		add_filter( 'template_include', array( 'App_Router', 'dynamic_routes' ), 99 );
		add_filter( 'redirect_canonical', array( 'App_Router', 'suppress_canonical' ), 1, 2 );
		add_action( 'template_redirect', array( 'App_Router', 'handle_redirects' ), 1 );
		
		add_action( 'init', array( 'App_Ajax', 'register_actions' ) );
		add_action( 'rest_api_init', array( 'App_Rest', 'register_routes' ) );

		// Activation tasks
		add_action( 'after_switch_theme', array( 'App_Router', 'sync_pages' ) );
		add_action( 'after_switch_theme', array( 'App_Database', 'install_all' ) );
		add_action( 'after_switch_theme', 'flush_rewrite_rules' );
	}

	public static function setup_theme() {
		$setup = self::config( 'setup' );

		foreach ( (array) ( $setup['supports'] ?? array() ) as $feature ) {
			add_theme_support( $feature );
		}
		if ( ! empty( $setup['html5'] ) ) {
			add_theme_support( 'html5', (array) $setup['html5'] );
		}

		$menus = array();
		foreach ( (array) ( $setup['menus'] ?? array() ) as $location => $label ) {
			$menus[ $location ] = __( $label, NT_TEXT_DOMAIN ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		}
		if ( $menus ) {
			register_nav_menus( $menus );
		}

		foreach ( (array) ( $setup['image_sizes'] ?? array() ) as $name => $size ) {
			add_image_size( $name, $size[0], $size[1], ! empty( $size[2] ) );
		}

		load_theme_textdomain( NT_TEXT_DOMAIN, NT_THEME_DIR . '/languages' );
	}
}
