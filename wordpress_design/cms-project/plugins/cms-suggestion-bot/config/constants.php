<?php
/**
 * config/constants.php - secondary constants (option keys, hook/action
 * names, capability). Split from the main plugin file so this list stays
 * easy to scan on its own.
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

define( 'CSB_CAPABILITY', 'manage_options' );

define( 'CSB_SETTINGS_OPTION', 'csb_settings' );

define( 'CSB_CRON_HOOK_DAILY', 'csb_cron_daily' );
define( 'CSB_CRON_HOOK_WEEKLY', 'csb_cron_weekly' );
define( 'CSB_CRON_HOOK_MONTHLY', 'csb_cron_monthly' );

define( 'CSB_AJAX_GENERATE_CACHE', 'csb_generate_cache' );
define( 'CSB_AJAX_DESTROY_CACHE', 'csb_destroy_cache' );
define( 'CSB_AJAX_REBUILD_CACHE', 'csb_rebuild_cache' );

define( 'CSB_RESOURCES_DIR', CSB_PLUGIN_DIR . '/resources' );

define( 'CSB_MENU_SLUG', 'cms-suggestion-bot' );
