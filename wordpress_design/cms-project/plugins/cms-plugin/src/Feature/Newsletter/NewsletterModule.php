<?php

namespace Ah\Cms\Feature\Newsletter;

defined( 'ABSPATH' ) || exit;

class NewsletterModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueueAssets' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Newsletter', 'Newsletter', 'manage_options', 'ah-newsletter', [ Controller\NewsletterAdminController::class, 'render' ] );
	}
	public static function enqueueAssets( string $hook ): void {
		if ( strpos( $hook, 'ah-newsletter' ) === false ) { return; }
		wp_enqueue_style( 'ah-newsletter', AH_PLUGIN_URL . '/src/Feature/Newsletter/Assets/css/newsletter.css', [], '1.0' );
	}
}
