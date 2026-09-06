<?php
namespace VintageSoul\Services\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * CookieConsentBridgeService — Lets an admin force every visitor's already-
 * stored cookie-consent decision to be forgotten, so the banner
 * (components/cookie-consent/cookie-consent.php, assets/js/components/cookie-consent.js)
 * shows again site-wide.
 *
 * That banner's decision lives only in each visitor's own localStorage -
 * there is no server-side per-visitor record to update - so "re-ask everyone"
 * works the same way advaithhomes_new's cookie-consent re-ask does: bump a
 * version number here, localize it to the script, and the script itself
 * (once updated to check it) treats a version mismatch as "no decision yet".
 */
class CookieConsentBridgeService {

	public const OPTION_KEY = 'vst_cookie_consent_version';
	public const SCRIPT_HANDLE = 'vintagesoul-ui-cookie-consent';

	public static function register_hooks(): void {
		// Priority 20: after AssetService::enqueue() (added at the default
		// 10 in Theme::init()) has registered the cookie-consent script handle.
		add_action( 'wp_enqueue_scripts', array( static::class, 'localize_version' ), 20 );
	}

	/**
	 * Current consent version. Visitors whose stored version doesn't match
	 * this are treated as if they'd never decided.
	 */
	public static function get_version(): int {
		return max( 1, (int) get_option( self::OPTION_KEY, 1 ) );
	}

	/**
	 * Bump the version, forcing the banner to show again for everyone.
	 */
	public static function bump_version(): int {
		$next = self::get_version() + 1;
		update_option( self::OPTION_KEY, $next );
		return $next;
	}

	/**
	 * Expose the current version to cookie-consent.js as window.vstCookieConsent.
	 */
	public static function localize_version(): void {
		if ( ! function_exists( 'wp_script_is' ) || ! wp_script_is( self::SCRIPT_HANDLE, 'registered' ) ) {
			return;
		}
		wp_localize_script( self::SCRIPT_HANDLE, 'vstCookieConsent', array(
			'version' => self::get_version(),
		) );
	}
}
