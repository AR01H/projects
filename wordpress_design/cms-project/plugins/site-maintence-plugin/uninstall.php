<?php
/**
 * Uninstall handler.
 *
 * Runs when the plugin is deleted via the WordPress admin (Plugins → Delete).
 * This file is called directly by WordPress - the plugin bootstrap is NOT loaded.
 *
 * @package SiteModeManager
 */

// WordPress security check - this file must only be called by WP uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// ── Single-site cleanup ───────────────────────────────────────────────────────

delete_option( 'smm_active_mode' );
delete_option( 'smm_custom_coming_soon_html' );

// ── Multisite cleanup ─────────────────────────────────────────────────────────

if ( is_multisite() ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( (int) $blog_id );
		delete_option( 'smm_active_mode' );
		delete_option( 'smm_custom_coming_soon_html' );
		restore_current_blog();
	}
}
