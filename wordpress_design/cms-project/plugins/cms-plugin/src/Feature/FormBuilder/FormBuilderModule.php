<?php

namespace Ah\Cms\Feature\FormBuilder;

defined( 'ABSPATH' ) || exit;

class FormBuilderModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueueAssets' ] );
		add_shortcode( 'ah_form', [ Shortcode\FormShortcode::class, 'render' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Form Builder', 'Form Builder', 'manage_options', 'ah-form-builder', [ Controller\FormBuilderAdminController::class, 'render' ] );
	}
	public static function enqueueAssets( string $hook ): void {
		if ( strpos( $hook, 'ah-form-builder' ) === false ) { return; }
		wp_enqueue_style( 'ah-form-builder', AH_PLUGIN_URL . '/src/Feature/FormBuilder/Assets/css/form-builder.css', [], '1.0' );
	}
}
