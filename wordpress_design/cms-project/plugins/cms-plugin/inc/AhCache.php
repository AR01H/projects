<?php
/**
 * Backward-compatibility wrapper — delegates to Ah\Cms\Cache\CacheManager.
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Cache/CacheManager.php';

class_alias( \Ah\Cms\Cache\CacheManager::class, 'AH_Cache' );
