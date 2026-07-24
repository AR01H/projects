<?php

namespace Ah\Cms\Feature\Cache;

defined( 'ABSPATH' ) || exit;

class CacheModule {
	public static function register(): void {
		add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		add_action( 'ah_cache_warm', [ Service\CacheManager::class, 'warm' ] );
		add_action( 'ah_cache_cleanup', [ Service\CacheManager::class, 'cleanup' ] );
	}
	public static function registerMenu(): void {
		add_submenu_page( 'ah-cms', 'Cache', 'Cache', 'manage_options', 'ah-cache', [ Controller\CacheAdminController::class, 'render' ] );
	}
}
