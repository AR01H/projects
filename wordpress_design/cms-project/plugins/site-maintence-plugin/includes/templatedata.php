<?php
/**
 * Read-only value object that templates receive as $smm.
 *
 * Keeps templates free of direct Settings / WP function calls while
 * still giving them everything they need.
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
 * Class TemplateData
 */
final class TemplateData {

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

	// ─── Convenience Getters ─────────────────────────────────────────────────

	/**
	 * Blog / site name.
	 *
	 * @return string
	 */
	public function site_name(): string {
		return get_bloginfo( 'name' );
	}

	/**
	 * Site tagline.
	 *
	 * @return string
	 */
	public function site_description(): string {
		return get_bloginfo( 'description' );
	}

	/**
	 * Current active mode.
	 *
	 * @return string
	 */
	public function active_mode(): string {
		return $this->settings->get_active_mode();
	}

	/**
	 * Plugin assets URL (trailing slash).
	 *
	 * @return string
	 */
	public function assets_url(): string {
		return SMM_PLUGIN_URL . 'assets/';
	}

	/**
	 * Home URL.
	 *
	 * @return string
	 */
	public function home_url(): string {
		return esc_url( home_url( '/' ) );
	}

	/**
	 * Plugin version.
	 *
	 * @return string
	 */
	public function version(): string {
		return SMM_VERSION;
	}

	/**
	 * Outputs a sanitised <title> string suitable for use in <head>.
	 *
	 * @param string $page_title Page-specific title prefix.
	 * @return string
	 */
	public function page_title( string $page_title ): string {
		return esc_html( $page_title . ' | ' . $this->site_name() );
	}

	/**
	 * Returns the absolute path to a template partial.
	 *
	 * @param string $partial Partial filename relative to templates/partials/.
	 * @return string
	 */
	public function partial_path( string $partial ): string {
		return SMM_PLUGIN_DIR . 'templates/partials/' . ltrim( $partial, '/' );
	}

	/**
	 * Returns the custom HTML for the Coming Soon page, or empty string if not set.
	 *
	 * @return string
	 */
	public function custom_coming_soon_html(): string {
		return $this->settings->get_custom_coming_soon_html();
	}

	/**
	 * Returns the custom HTML for the Maintenance page, or empty string if not set.
	 *
	 * @return string
	 */
	public function custom_maintenance_html(): string {
		return $this->settings->get_custom_maintenance_html();
	}

	/**
	 * Returns the custom HTML for the Custom Page mode, or empty string if not set.
	 *
	 * @return string
	 */
	public function custom_page_html(): string {
		return $this->settings->get_custom_page_html();
	}
}
