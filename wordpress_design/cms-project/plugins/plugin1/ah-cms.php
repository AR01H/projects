<?php
/**
 * Plugin Name:  CMS ADMIN
 * Description:  CMS engine - admin portal, database, models, helpers, and form builder.
 *               Install as a plugin and pair with any frontend theme that reads wp_ah_* tables.
 * Version:      1.0.2
 * Author:       Akhilesh Ravuri
 * Text Domain:  ah-theme
 */
defined( 'ABSPATH' ) || exit;

// ── Constants ────────────────────────────────────────────────────────────────
define( 'AH_PLUGIN_VERSION', '1.0.3' );
define( 'AH_DB_VERSION_KEY', 'ah_cms_db_version' );

// Table name infix - all custom tables are named: {wpdb_prefix}ah{TABLE_MID_FIX}{table_suffix}
// e.g. wp_ah_cms_plug_services. Change this only before first install.
define( 'TABLE_MID_FIX', '_cms_plug_' );

// plugin_dir_path() has a trailing slash; strip it so paths match the existing
// AH_THEME_DIR convention (no trailing slash) - autoloader adds its own slash.
define( 'AH_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'AH_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

// Backward-compat aliases: every model, admin page, helper, and importer that
// already uses AH_THEME_DIR / AH_THEME_URL continues to work unchanged.
define( 'AH_THEME_DIR',     AH_PLUGIN_DIR );
define( 'AH_THEME_URL',     AH_PLUGIN_URL );
define( 'AH_THEME_VERSION', AH_PLUGIN_VERSION );

// ── Autoloader ───────────────────────────────────────────────────────────────
require_once AH_PLUGIN_DIR . '/inc/class-autoloader.php';
AH_Autoloader::register();

// ── Components ───────────────────────────────────────────────────────────────
require_once AH_PLUGIN_DIR . '/components/toaster/index.php';

// ── Admin portal ─────────────────────────────────────────────────────────────
if ( is_admin() ) {
	AH_Admin_Bootstrap::init();
}

// ── Public AJAX (form builder frontend - works for logged-in and guests) ─────
AH_Ajax_Handlers::init_public();

// ── Shortcode [ah_form id="N"] ───────────────────────────────────────────────
add_action( 'init', static function () {
	add_shortcode( 'ah_form', array( 'AH_Form_Builder', 'render' ) );
} );

// ── ahTheme JS object (needed by form-builder shortcode frontend JS) ─────────
// The active theme's functions.php should enqueue its own scripts; this only
// localizes the object so fetch() in the shortcode can reach admin-ajax.php.
add_action( 'wp_enqueue_scripts', static function () {
	wp_localize_script( 'jquery', 'ahTheme', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ah_frontend_nonce' ),
	) );
} );

// ── Database ─────────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, array( 'AH_DB_Installer', 'install' ) );
add_action( 'wp_loaded', array( 'AH_DB_Installer', 'maybe_upgrade' ) );

// ── Rules Engine: async cron processor ───────────────────────────────────────
// evaluate() queues actions as 'pending' in ah_trigger_logs; this cron fires
// them in the background every minute (pending + failed retries).

add_filter( 'cron_schedules', static function ( array $s ): array {
	if ( ! isset( $s['ah_every_minute'] ) ) {
		$s['ah_every_minute'] = array(
			'interval' => 60,
			'display'  => 'Every Minute (AH Rules Engine)',
		);
	}
	return $s;
} );

add_action( 'ah_rules_cron_process', array( 'AH_Rules_Engine', 'cron_process' ) );

// Schedule on first load; clear any old retry-only hook.
add_action( 'init', static function (): void {
	// Remove old hook name if it exists from a prior version
	$old = wp_next_scheduled( 'ah_rules_cron_retry' );
	if ( $old ) wp_unschedule_event( $old, 'ah_rules_cron_retry' );

	if ( ! wp_next_scheduled( 'ah_rules_cron_process' ) ) {
		wp_schedule_event( time(), 'ah_every_minute', 'ah_rules_cron_process' );
	}
} );

// Clear schedule on plugin deactivation.
register_deactivation_hook( __FILE__, static function (): void {
	$ts = wp_next_scheduled( 'ah_rules_cron_process' );
	if ( $ts ) wp_unschedule_event( $ts, 'ah_rules_cron_process' );
} );

// ── Builder page frontend routing ─────────────────────────────────────────────
add_action( 'template_redirect', static function () {
	if ( ! is_404() ) return;
	global $wpdb;
	$table = $wpdb->prefix . 'ah_builder_pages';
	$home_path    = trim( (string) parse_url( home_url(), PHP_URL_PATH ), '/' );
	$request_path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $home_path !== '' && strpos( $request_path, $home_path ) === 0 ) {
		$request_path = ltrim( substr( $request_path, strlen( $home_path ) ), '/' );
	}
	$slug = sanitize_title( trim( $request_path, '/' ) );
	if ( ! $slug ) return;
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$page = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE slug = %s AND status = 'active'", $slug ) );
	if ( ! $page ) return;
	$GLOBALS['ah_builder_page'] = $page;
	include AH_PLUGIN_DIR . '/templates/template-builder-page.php';
	exit;
} );
