<?php
/**
 * uninstall.php - runs only when the plugin is deleted from wp-admin
 * (never on deactivation). Removes nothing unless the admin explicitly
 * opted in via Settings -> "Delete all plugin data on uninstall" (see
 * Admin\Pages\SettingsPage) - otherwise every table, cache file, and
 * option is left in place in case the plugin is reinstalled later.
 */

declare( strict_types = 1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! get_option( 'csb_delete_data_on_uninstall', false ) ) {
	return;
}

global $wpdb;

$prefix = $wpdb->prefix . 'cms_sug_bot_';
$tables = array(
	'cache', 'chunks', 'hash', 'reader', 'knowledge', 'conversations',
	'messages', 'logs', 'api_keys', 'settings', 'statistics', 'jobs',
	'queue', 'embeddings', 'versions',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS `{$prefix}{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

delete_option( 'csb_db_version' );
delete_option( 'csb_delete_data_on_uninstall' );

wp_clear_scheduled_hook( 'csb_cron_daily' );
wp_clear_scheduled_hook( 'csb_cron_weekly' );
wp_clear_scheduled_hook( 'csb_cron_monthly' );
