<?php
defined( 'ABSPATH' ) || exit;

class AH_Asset_Loader {

	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'frontend_assets' ) );
	}

	public static function frontend_assets(): void {
		wp_enqueue_style(
			'ah-variables',
			AH_THEME_URL . '/assets/css/variables.css',
			array(),
			AH_THEME_VERSION
		);
		wp_enqueue_style(
			'ah-animations',
			AH_THEME_URL . '/assets/css/animations.css',
			array( 'ah-variables' ),
			AH_THEME_VERSION
		);
		wp_enqueue_style(
			'ah-main',
			AH_THEME_URL . '/assets/css/main.css',
			array( 'ah-variables', 'ah-animations' ),
			AH_THEME_VERSION
		);
		wp_enqueue_script(
			'ah-main',
			AH_THEME_URL . '/assets/js/main.js',
			array( 'jquery' ),
			AH_THEME_VERSION,
			true
		);

		wp_localize_script( 'ah-main', 'ahTheme', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ah_frontend_nonce' ),
		) );
	}
}
