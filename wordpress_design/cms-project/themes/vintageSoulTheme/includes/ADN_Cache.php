<?php
/**
 * Global ADN_Cache compatibility bridge for VintageSoulTheme.
 * Allows CMS Plugin (CacheManager, GlobalSettings) to trigger theme cache clearing and retrieval.
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\Plugins\GlobalSettingsBridgeService;

if ( ! class_exists( 'ADN_Cache' ) ) {
	class ADN_Cache {

		public static function clear_all(): bool {
			return GlobalSettingsBridgeService::clear_all();
		}

		public static function get( string $key, string $group = 'general' ) {
			return GlobalSettingsBridgeService::get( $key, $group );
		}

		public static function set( string $key, $data, string $group = 'general', int $ttl = 3600 ): bool {
			return GlobalSettingsBridgeService::set( $key, $data, $group, $ttl );
		}

		public static function is_enabled(): bool {
			return GlobalSettingsBridgeService::is_cache_enabled();
		}
	}
}
