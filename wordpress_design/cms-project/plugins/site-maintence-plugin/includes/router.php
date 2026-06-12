<?php
/**
 * Central request router.
 *
 * Runs on every frontend request (template_redirect, priority 1).
 * Decides what content to display based on the active mode.
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
 * Class Router
 */
final class Router {

	/** @var Settings */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Shared settings service.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	// ─── Public Entry Point ──────────────────────────────────────────────────

	/**
	 * Main dispatch method - hooked to template_redirect (priority 1).
	 *
	 * Checks the active mode and either intercepts the request or lets
	 * WordPress handle it normally.
	 *
	 * @return void
	 */
	public function dispatch(): void {
		// Always allow wp-admin and login screen.
		if ( $this->is_admin_or_login() ) {
			return;
		}

		// Always allow logged-in administrators.
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$mode = $this->settings->get_active_mode();

		/**
		 * Filter: smm_dispatch_mode
		 * Allows third-party code to override the mode before routing.
		 *
		 * @param string $mode Current active mode.
		 */
		$mode = (string) apply_filters( 'smm_dispatch_mode', $mode );

		switch ( $mode ) {
			case Settings::MODE_COMING_SOON:
				$this->render_coming_soon();
				break;

			case Settings::MODE_MAINTENANCE:
				$this->render_maintenance();
				break;

			case Settings::MODE_LANDING_PAGE:
				$this->render_custom_page();
				break;

			case Settings::MODE_NORMAL:
			default:
				// Let WordPress / the active theme handle the request.
				return;
		}
	}

	// ─── Mode Renderers ──────────────────────────────────────────────────────

	/**
	 * Render the Coming Soon page (HTTP 200).
	 *
	 * @return void
	 */
	private function render_coming_soon(): void {
		status_header( 200 );
		nocache_headers();

		/**
		 * Action: smm_before_coming_soon_template
		 * Fires before the coming-soon template is loaded.
		 */
		do_action( 'smm_before_coming_soon_template' );

		$template = $this->locate_template( 'coming-soon.php' );
		$this->load_template( $template );

		/**
		 * Action: smm_after_coming_soon_template
		 */
		do_action( 'smm_after_coming_soon_template' );

		exit;
	}

	/**
	 * Render the Maintenance page (HTTP 503 + Retry-After).
	 *
	 * @return void
	 */
	private function render_maintenance(): void {
		/**
		 * Filter: smm_maintenance_retry_after
		 * Number of seconds until the site is expected to be back online.
		 *
		 * @param int $seconds Default 3600 (1 hour).
		 */
		$retry_after = (int) apply_filters( 'smm_maintenance_retry_after', 3600 );

		status_header( 503 );
		header( 'Retry-After: ' . $retry_after );
		nocache_headers();

		/**
		 * Action: smm_before_maintenance_template
		 */
		do_action( 'smm_before_maintenance_template' );

		$template = $this->locate_template( 'maintenance.php' );
		$this->load_template( $template );

		/**
		 * Action: smm_after_maintenance_template
		 */
		do_action( 'smm_after_maintenance_template' );

		exit;
	}

	/**
	 * Render the Custom Page (HTTP 200).
	 *
	 * @return void
	 */
	private function render_custom_page(): void {
		status_header( 200 );
		nocache_headers();

		/**
		 * Action: smm_before_custom_page_template
		 * Fires before the custom page template is loaded.
		 */
		do_action( 'smm_before_custom_page_template' );

		$template = $this->locate_template( 'custom-page.php' );
		$this->load_template( $template );

		/**
		 * Action: smm_after_custom_page_template
		 */
		do_action( 'smm_after_custom_page_template' );

		exit;
	}

	// ─── Template Location ───────────────────────────────────────────────────

	/**
	 * Locates a template file.
	 *
	 * Theme overrides are supported: a child/parent theme can place a file at
	 * `site-mode-manager/{filename}` to override the plugin default.
	 *
	 * @param string $filename Template filename (e.g. 'coming-soon.php').
	 * @return string          Absolute path to the resolved template.
	 */
	private function locate_template( string $filename ): string {
		// Allow theme overrides.
		$theme_file = locate_template( [ 'site-mode-manager/' . $filename ] );
		if ( $theme_file ) {
			return $theme_file;
		}

		// Default plugin template.
		return SMM_PLUGIN_DIR . 'templates/' . $filename;
	}

	/**
	 * Loads a template file in an isolated scope.
	 *
	 * @param string $template_path Absolute path to the template.
	 * @return void
	 */
	private function load_template( string $template_path ): void {
		if ( ! file_exists( $template_path ) ) {
			wp_die(
				esc_html__( 'Site Mode Manager: Template file not found.', 'site-mode-manager' ),
				esc_html__( 'Template Missing', 'site-mode-manager' ),
				[ 'response' => 500 ]
			);
		}

		// Pass shared data to template via a simple value object.
		$smm = new TemplateData( $this->settings );

		include $template_path; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	// ─── Helpers ─────────────────────────────────────────────────────────────

	/**
	 * Returns true when the current request targets wp-admin or wp-login.php.
	 *
	 * @return bool
	 */
	private function is_admin_or_login(): bool {
		if ( is_admin() ) {
			return true;
		}

		// Detect login page without relying on is_login_page() (WP 6.1+).
		$script = isset( $_SERVER['SCRIPT_FILENAME'] )
			? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) )
			: '';

		return 'wp-login.php' === $script;
	}
}
