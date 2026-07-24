<?php

namespace Ah\Cms\Feature\Posts;

defined( 'ABSPATH' ) || exit;

class PostsModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueueAssets' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Blog Posts', 'Blog Posts', 'manage_options', 'ah-posts', [ Controller\PostsAdminController::class, 'render' ] );
	}
	public static function enqueueAssets( string $hook ): void {
		if ( strpos( $hook, 'ah-posts' ) === false ) { return; }
		wp_enqueue_style( 'ah-posts', AH_PLUGIN_URL . '/src/Feature/Posts/Assets/css/posts.css', [], '1.0' );
	}
}
