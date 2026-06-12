<?php
/**
 * Core Plugin bootstrap class.
 *
 * @package SiteModeManager
 */

declare( strict_types=1 );

namespace SiteModeManager;

// Block direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 *
 * Singleton that wires all service classes together and kicks off the plugin.
 */
final class Plugin {

	// ─── Singleton ───────────────────────────────────────────────────────────

	/** @var Plugin|null Single shared instance. */
	private static ?Plugin $instance = null;

	/**
	 * Returns (and creates on first call) the single Plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor - use instance(). */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	// ─── Service Properties ──────────────────────────────────────────────────

	/** @var Settings */
	private Settings $settings;

	/** @var Router */
	private Router $router;

	/** @var HooksLoader */
	private HooksLoader $hooks;

	/** @var AdminUI */
	private AdminUI $admin_ui;

	// ─── Init ────────────────────────────────────────────────────────────────

	/**
	 * Instantiate all service classes.
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		$this->settings = new Settings();
		$this->router   = new Router( $this->settings );
		$this->hooks    = new HooksLoader();
		$this->admin_ui = new AdminUI( $this->settings );
	}

	/**
	 * Register all WordPress hooks through the HooksLoader.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		// i18n.
		$this->hooks->add_action( 'init', $this, 'load_textdomain' );

		// Frontend router.
		$this->hooks->add_action( 'template_redirect', $this->router, 'dispatch', 1 );

		// Admin UI.
		$this->hooks->add_action( 'admin_menu',    $this->admin_ui, 'register_menu' );
		$this->hooks->add_action( 'admin_bar_menu', $this->admin_ui, 'admin_bar_node', 100 );

		// AJAX handlers (admin-only nonce-verified).
		$this->hooks->add_action( 'wp_ajax_smm_toggle_mode', $this->admin_ui, 'handle_ajax_toggle' );
		$this->hooks->add_action( 'wp_ajax_smm_save_coming_soon_html', $this->admin_ui, 'handle_save_coming_soon_html' );
		$this->hooks->add_action( 'wp_ajax_smm_save_maintenance_html', $this->admin_ui, 'handle_save_maintenance_html' );
		$this->hooks->add_action( 'wp_ajax_smm_save_page_html', $this->admin_ui, 'handle_save_page_html' );

		// Assets.
		$this->hooks->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_assets' );

		$this->hooks->run();
	}

	// ─── Public Callbacks ────────────────────────────────────────────────────

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'site-mode-manager',
			false,
			dirname( SMM_PLUGIN_BASE ) . '/languages'
		);
	}

	/**
	 * Enqueue admin-only assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Only on our settings page.
		if ( false === strpos( $hook, 'site-mode-manager' ) ) {
			return;
		}

		wp_enqueue_style(
			'smm-admin',
			SMM_PLUGIN_URL . 'assets/css/admin.css',
			[],
			SMM_VERSION
		);

		wp_enqueue_script(
			'smm-admin',
			SMM_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			SMM_VERSION,
			true
		);

		wp_localize_script(
			'smm-admin',
			'smmAdmin',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'smm_toggle_mode' ),
				'i18n'    => [
					'saving'  => __( 'Saving…', 'site-mode-manager' ),
					'saved'   => __( 'Saved!',  'site-mode-manager' ),
					'error'   => __( 'Error - please try again.', 'site-mode-manager' ),
				],
			]
		);
	}
}
