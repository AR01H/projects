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
}
