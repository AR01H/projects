<?php
/**
 * Settings / configuration service.
 *
 * Reads the active mode from wp_options (set by the Admin UI).
 * Hard-coded define() overrides are still supported for
 * legacy / code-only deployments.
 *
 * Supported modes (string keys):
 *   MODE_NORMAL       - WordPress renders normally.
 *   MODE_COMING_SOON  - Show coming-soon template (HTTP 200).
 *   MODE_MAINTENANCE  - Show maintenance template (HTTP 503).
 *   MODE_HOLIDAY      - Reserved for future use.
 *   MODE_PRIVATE_BETA - Reserved for future use.
 *   MODE_LANDING_PAGE - Reserved for future use.
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
 * Class Settings
 */
final class Settings {

	// ─── Mode Constants ──────────────────────────────────────────────────────

	public const MODE_NORMAL       = 'normal';
	public const MODE_COMING_SOON  = 'coming_soon';
	public const MODE_MAINTENANCE  = 'maintenance';
	public const MODE_HOLIDAY      = 'holiday';
	public const MODE_PRIVATE_BETA = 'private_beta';
	public const MODE_LANDING_PAGE = 'landing_page';

	/** Default option key stored in wp_options. */
	public const OPTION_KEY = 'smm_active_mode';

	/** Option keys for custom HTML content. */
	public const OPTION_CUSTOM_COMING_SOON_HTML = 'smm_custom_coming_soon_html';
	public const OPTION_CUSTOM_MAINTENANCE_HTML = 'smm_custom_maintenance_html';
	public const OPTION_CUSTOM_PAGE_HTML        = 'smm_custom_page_html';

	/** Default mode used on fresh installs. */
	public const DEFAULT_MODE = self::MODE_NORMAL;

	// ─── Runtime Cache ───────────────────────────────────────────────────────

	/** @var string|null Cached active mode for the current request. */
	private ?string $active_mode_cache = null;

	// ─── Public API ──────────────────────────────────────────────────────────

	/**
	 * Returns the currently active mode string.
	 *
	 * Priority (highest → lowest):
	 *  1. Hard-coded define() constants (backward-compat).
	 *  2. wp_options value (set via Admin UI).
	 *  3. DEFAULT_MODE fallback.
	 *
	 * @return string One of the MODE_* constants.
	 */
	public function get_active_mode(): string {
		if ( null !== $this->active_mode_cache ) {
			return $this->active_mode_cache;
		}

		// 1. Legacy define() overrides.
		if ( defined( 'SMM_MAINTENANCE_MODE' ) && SMM_MAINTENANCE_MODE === true ) {
			return $this->active_mode_cache = self::MODE_MAINTENANCE;
		}
		if ( defined( 'SMM_COMING_SOON_MODE' ) && SMM_COMING_SOON_MODE === true ) {
			return $this->active_mode_cache = self::MODE_COMING_SOON;
		}

		// 2. Database option.
		$stored = get_option( self::OPTION_KEY, self::DEFAULT_MODE );
		$mode   = $this->sanitize_mode( (string) $stored );

		return $this->active_mode_cache = $mode;
	}

	/**
	 * Persists a new active mode to the database.
	 *
	 * @param string $mode One of the MODE_* constants.
	 * @return bool        True on success.
	 */
	public function set_active_mode( string $mode ): bool {
		$mode = $this->sanitize_mode( $mode );
		$this->active_mode_cache = $mode;
		return update_option( self::OPTION_KEY, $mode, false );
	}

	/**
	 * Returns the custom HTML for the Coming Soon page.
	 *
	 * @return string Custom HTML content or empty string if not set.
	 */
	public function get_custom_coming_soon_html(): string {
		$html = get_option( self::OPTION_CUSTOM_COMING_SOON_HTML, '' );
		return is_string( $html ) ? $html : '';
	}

	/**
	 * Persists custom HTML content for the Coming Soon page.
	 *
	 * @param string $html Custom HTML content.
	 * @return bool        True on success.
	 */
	public function set_custom_coming_soon_html( string $html ): bool {
		// Use wp_kses_post to allow safe HTML.
		$sanitized = wp_kses_post( $html );
		return update_option( self::OPTION_CUSTOM_COMING_SOON_HTML, $sanitized, false );
	}

	/**
	 * Returns the custom HTML for the Maintenance page.
	 *
	 * @return string Custom HTML content or empty string if not set.
	 */
	public function get_custom_maintenance_html(): string {
		$html = get_option( self::OPTION_CUSTOM_MAINTENANCE_HTML, '' );
		return is_string( $html ) ? $html : '';
	}

	/**
	 * Persists custom HTML content for the Maintenance page.
	 *
	 * @param string $html Custom HTML content.
	 * @return bool        True on success.
	 */
	public function set_custom_maintenance_html( string $html ): bool {
		// Use wp_kses_post to allow safe HTML.
		$sanitized = wp_kses_post( $html );
		return update_option( self::OPTION_CUSTOM_MAINTENANCE_HTML, $sanitized, false );
	}

	/**
	 * Returns the custom HTML for the Custom Page mode.
	 *
	 * @return string Custom HTML content or empty string if not set.
	 */
	public function get_custom_page_html(): string {
		$html = get_option( self::OPTION_CUSTOM_PAGE_HTML, '' );
		return is_string( $html ) ? $html : '';
	}

	/**
	 * Persists custom HTML content for the Custom Page mode.
	 *
	 * @param string $html Custom HTML content.
	 * @return bool        True on success.
	 */
	public function set_custom_page_html( string $html ): bool {
		// Use wp_kses_post to allow safe HTML.
		$sanitized = wp_kses_post( $html );
		return update_option( self::OPTION_CUSTOM_PAGE_HTML, $sanitized, false );
	}

	/**
	 * Returns all registered modes with their labels.
	 *
	 * Extend this array to add future modes - the router will pick them up
	 * automatically as long as a matching template or handler exists.
	 *
	 * @return array<string, string> [ mode_key => label ]
	 */
	public function get_all_modes(): array {
		return [
			self::MODE_NORMAL       => __( 'Normal (Live)',    'site-mode-manager' ),
			self::MODE_COMING_SOON  => __( 'Coming Soon',      'site-mode-manager' ),
			self::MODE_MAINTENANCE  => __( 'Maintenance',      'site-mode-manager' ),
			self::MODE_HOLIDAY      => __( 'Holiday',          'site-mode-manager' ),
			self::MODE_PRIVATE_BETA => __( 'Private Beta',     'site-mode-manager' ),
			self::MODE_LANDING_PAGE => __( 'Landing Page',     'site-mode-manager' ),
		];
	}

	/**
	 * Convenience: is the site currently in the given mode?
	 *
	 * @param string $mode One of the MODE_* constants.
	 * @return bool
	 */
	public function is_mode( string $mode ): bool {
		return $this->get_active_mode() === $mode;
	}

	/**
	 * Initialises the option in the database (called on activation).
	 *
	 * @return void
	 */
	public function maybe_init_option(): void {
		if ( false === get_option( self::OPTION_KEY ) ) {
			add_option( self::OPTION_KEY, self::DEFAULT_MODE, '', false );
		}
	}

	/**
	 * Deletes the options from the database (called on uninstall).
	 *
	 * @return void
	 */
	public function delete_option(): void {
		delete_option( self::OPTION_KEY );
		delete_option( self::OPTION_CUSTOM_COMING_SOON_HTML );
		delete_option( self::OPTION_CUSTOM_MAINTENANCE_HTML );
		delete_option( self::OPTION_CUSTOM_PAGE_HTML );
	}

	// ─── Private Helpers ─────────────────────────────────────────────────────

	/**
	 * Sanitises and validates a mode string, falling back to DEFAULT_MODE.
	 *
	 * @param string $mode Raw mode string.
	 * @return string      Validated mode string.
	 */
	private function sanitize_mode( string $mode ): string {
		$valid = array_keys( $this->get_all_modes() );
		return in_array( $mode, $valid, true ) ? $mode : self::DEFAULT_MODE;
	}
}
