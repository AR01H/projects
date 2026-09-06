<?php
namespace VintageSoul\Services\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * GoogleServicesBridgeService — Feeds the Google Analytics / Tag Manager
 * IDs configured on the theme's own Theme Settings admin page
 * (admin.php?page=vst-theme-settings) into the CMS Plugin's
 * google-services.js library.
 *
 * That script (plugins/cms-plugin/assets/js/google-services.js) is enqueued
 * on every frontend page regardless of theme (see
 * inc/AssetLoader.php::enqueue_google_services()) and defines
 * initGoogleAnalytics()/initGoogleTagManager()/etc., but nothing ever calls
 * them - this bridge is the missing "who calls it, with what ID" piece, and
 * it's a theme-level admin field precisely so no plugin file needs editing.
 */
class GoogleServicesBridgeService {

	public const OPT_ANALYTICS_IDS   = 'vst_google_analytics_ids';
	public const OPT_TAG_MANAGER_ID  = 'vst_google_tag_manager_id';

	public static function register_hooks(): void {
		// Priority 30: after wp_print_footer_scripts (hooked on wp_footer at
		// 20), so initGoogleAnalytics()/initGoogleTagManager() are already
		// defined by google-services.js when this prints its call to them.
		add_action( 'wp_footer', array( static::class, 'print_init_script' ), 30 );
	}

	/**
	 * Configured GA4 Measurement IDs (comma-separated in the admin field).
	 *
	 * @return array<int, string>
	 */
	public static function get_analytics_ids(): array {
		$raw = (string) get_option( self::OPT_ANALYTICS_IDS, '' );
		$ids = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
		return array_values( $ids );
	}

	/**
	 * Configured Google Tag Manager container ID, or '' if none.
	 */
	public static function get_tag_manager_id(): string {
		return trim( (string) get_option( self::OPT_TAG_MANAGER_ID, '' ) );
	}

	/**
	 * Print the inline script that calls into google-services.js with the
	 * configured IDs. Does nothing when no ID is configured, or in wp-admin.
	 */
	public static function print_init_script(): void {
		if ( is_admin() ) {
			return;
		}

		$ga_ids = self::get_analytics_ids();
		$gtm_id = self::get_tag_manager_id();

		if ( empty( $ga_ids ) && '' === $gtm_id ) {
			return;
		}
		?>
		<script id="vst-google-services-init">
		( function () {
			function boot() {
				<?php if ( ! empty( $ga_ids ) ) : ?>
				if ( typeof initGoogleAnalytics === 'function' ) {
					initGoogleAnalytics( <?php echo wp_json_encode( $ga_ids ); ?> );
				}
				<?php endif; ?>
				<?php if ( '' !== $gtm_id ) : ?>
				if ( typeof initGoogleTagManager === 'function' ) {
					initGoogleTagManager( <?php echo wp_json_encode( $gtm_id ); ?> );
				}
				<?php endif; ?>
			}
			// google-services.js is enqueued in_footer too; if it hasn't
			// defined these yet for some reason, fall back to window load.
			if ( typeof initGoogleAnalytics === 'function' || typeof initGoogleTagManager === 'function' ) {
				boot();
			} else {
				window.addEventListener( 'load', boot );
			}
		} )();
		</script>
		<?php
	}
}
