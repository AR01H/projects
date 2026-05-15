<?php
defined( 'ABSPATH' ) || exit;

class AH_Admin_Bootstrap {

	public static function init(): void {
		add_action( 'admin_menu', array( 'AH_Admin_Menus', 'register' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		AH_Ajax_Handlers::init();
	}

	public static function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'ah-' ) === false ) return;

		wp_enqueue_style(
			'ah-admin-style',
			AH_THEME_URL . '/admin/assets/css/admin-style.css',
			array( 'wp-color-picker' ),
			AH_THEME_VERSION
		);
		wp_enqueue_script(
			'ah-admin-script',
			AH_THEME_URL . '/admin/assets/js/admin-script.js',
			array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker', 'media-upload', 'thickbox' ),
			AH_THEME_VERSION,
			true
		);
		wp_localize_script( 'ah-admin-script', 'ahAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ah_admin_nonce' ),
			'confirm' => __( 'Are you sure? This cannot be undone.', 'ah-theme' ),
		) );

		// WP media library for image picker
		wp_enqueue_media();
		add_thickbox();
	}
}
