<?php
defined( 'ABSPATH' ) || exit;

class AH_Asset_Loader {

	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'frontend_assets' ) );
	}

	public static function frontend_assets(): void {
		// Use get_template_directory_uri() - not AH_THEME_URL - so assets always
		// resolve to the active theme folder whether this class is called from the
		// plugin or directly from a theme's functions.php.
		$url = get_template_directory_uri();
		$ver = defined( 'AH_PLUGIN_VERSION' ) ? AH_PLUGIN_VERSION : AH_THEME_VERSION;

		wp_enqueue_style( 'ah-variables',  $url . '/assets/css/variables.css',  array(),                              $ver );
		wp_enqueue_style( 'ah-animations', $url . '/assets/css/animations.css', array( 'ah-variables' ),             $ver );
		wp_enqueue_style( 'ah-main',       $url . '/assets/css/main.css',        array( 'ah-variables', 'ah-animations' ), $ver );

		wp_enqueue_script( 'ah-main', $url . '/assets/js/main.js', array( 'jquery' ), $ver, true );

		wp_localize_script( 'ah-main', 'ahTheme', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ah_frontend_nonce' ),
		) );
	}

	/**
	 * Plugin-owned Google services JS (initGoogleAnalytics(), etc. - see
	 * assets/js/google-services.js). Deliberately its own method/hook, kept
	 * separate from frontend_assets() above: that method also re-enqueues
	 * main.css/js from the *active theme's* own folder, which is only safe
	 * to call once per site. This method has no such entanglement, so it's
	 * registered directly (see src/Bootstrap/HookRegistrar.php) and works
	 * on any site running this plugin, independent of whether
	 * frontend_assets()/init() above is ever called by that site's theme.
	 *
	 * No hard script dependency on the theme's cookie-consent script: that
	 * handle may not exist at all on some sites, and depending on a handle
	 * WordPress never sees registered would make it silently drop this
	 * whole script. google-services.js instead checks
	 * window.adnCookieConsent lazily, at call time (not load time), so
	 * enqueue order between the two doesn't matter.
	 */
	public static function enqueue_google_services(): void {
		$path = AH_PLUGIN_DIR . '/assets/js/google-services.js';
		$ver  = file_exists( $path ) ? filemtime( $path ) : AH_PLUGIN_VERSION;
		wp_enqueue_script(
			'ah-google-services',
			AH_PLUGIN_URL . '/assets/js/google-services.js',
			array(),
			$ver,
			true
		);
	}
}
