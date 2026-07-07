<?php
/**
 * admin/includes/tools.php - Admin Tool callbacks.
 *
 * Each function backs one entry in the config/admin.php 'tools' registry.
 * They run ONLY through nt_admin_run_tool() (core/admin.php), which has
 * already checked capability + nonce - so the body is just the action.
 * Return the status message to show; download-type tools exit themselves.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Flush the WordPress object cache.
 */
function nt_tool_clear_object_cache() {
	wp_cache_flush();
	return __( 'Object cache flushed.', NT_TEXT_DOMAIN );
}

/**
 * Delete ALL transients (and their timeout rows) from the options table.
 * The LIKE patterns are fixed literals (underscores escaped), no user input.
 */
function nt_tool_clear_transients() {
	global $wpdb;
	$deleted = (int) $wpdb->query(
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE '\\_transient\\_%'
		    OR option_name LIKE '\\_site\\_transient\\_%'"
	);
	wp_cache_flush();
	/* translators: %d: number of deleted option rows */
	return sprintf( __( '%d transient row(s) removed.', NT_TEXT_DOMAIN ), $deleted );
}

/**
 * Rebuild permalinks.
 */
function nt_tool_flush_rewrites() {
	flush_rewrite_rules();
	return __( 'Rewrite rules flushed.', NT_TEXT_DOMAIN );
}

/**
 * Create WP page rows for the config/pages.php registry (idempotent).
 */
function nt_tool_sync_pages() {
	$created = nt_sync_pages();
	/* translators: %d: number of created pages */
	return sprintf( __( '%d page(s) created.', NT_TEXT_DOMAIN ), $created );
}

/**
 * Install / repair EVERY table registered in config/database.php.
 */
function nt_tool_install_tables() {
	$results = nt_db_install_all();
	$ok      = count( array_filter( $results ) );
	/* translators: 1: installed count, 2: registered count */
	return sprintf( __( '%1$d of %2$d table(s) installed / verified.', NT_TEXT_DOMAIN ), $ok, count( $results ) );
}

/**
 * Install / repair ONE registered table (the per-row button on the
 * Admin Tools -> Database screen posts the table key).
 */
function nt_tool_install_table() {
	$key = isset( $_POST['table_key'] ) ? sanitize_key( wp_unslash( $_POST['table_key'] ) ) : '';
	if ( '' === $key || ! array_key_exists( $key, nt_config( 'database' ) ) ) {
		return __( 'Unknown table key.', NT_TEXT_DOMAIN );
	}
	return nt_db_install( $key )
		/* translators: %s: table name */
		? sprintf( __( 'Table "%s" installed / verified.', NT_TEXT_DOMAIN ), nt_db_table( $key ) )
		/* translators: %s: table name */
		: sprintf( __( 'Table "%s" could NOT be created - check the schema.', NT_TEXT_DOMAIN ), nt_db_table( $key ) );
}

/**
 * Contact Submissions: toggle a row between 'new' and 'read'.
 */
function nt_tool_submission_status() {
	global $wpdb;
	$id     = isset( $_POST['submission_id'] ) ? absint( $_POST['submission_id'] ) : 0;
	$status = isset( $_POST['new_status'] ) ? sanitize_key( wp_unslash( $_POST['new_status'] ) ) : '';
	if ( ! $id || ! in_array( $status, array( 'new', 'read' ), true ) ) {
		return __( 'Invalid request.', NT_TEXT_DOMAIN );
	}
	$updated = $wpdb->update(
		nt_db_table( 'submissions' ),
		array( 'status' => $status ),
		array( 'id' => $id ),
		array( '%s' ),
		array( '%d' )
	);
	return false === $updated
		? __( 'Update failed.', NT_TEXT_DOMAIN )
		/* translators: %s: new status */
		: sprintf( __( 'Submission marked as %s.', NT_TEXT_DOMAIN ), $status );
}

/**
 * Contact Submissions: delete a row permanently.
 */
function nt_tool_submission_delete() {
	global $wpdb;
	$id = isset( $_POST['submission_id'] ) ? absint( $_POST['submission_id'] ) : 0;
	if ( ! $id ) {
		return __( 'Invalid request.', NT_TEXT_DOMAIN );
	}
	$deleted = $wpdb->delete( nt_db_table( 'submissions' ), array( 'id' => $id ), array( '%d' ) );
	return $deleted
		? __( 'Submission deleted.', NT_TEXT_DOMAIN )
		: __( 'Delete failed.', NT_TEXT_DOMAIN );
}

/**
 * Export every option group (config/admin.php 'options') as a JSON download.
 * Exits after streaming - no redirect.
 */
function nt_tool_export_settings() {
	$admin  = nt_config( 'admin' );
	$groups = array();
	foreach ( (array) ( $admin['options'] ?? array() ) as $group => $def ) {
		$groups[ $group ] = get_option( (string) ( $def['option'] ?? 'nt_' . $group ), array() );
	}

	$payload = array(
		'_theme'    => wp_get_theme()->get( 'Name' ),
		'_version'  => NT_THEME_VERSION,
		'_exported' => gmdate( 'c' ),
		'groups'    => $groups,
	);

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="theme-settings-' . gmdate( 'Ymd-His' ) . '.json"' );
	echo wp_json_encode( $payload, JSON_PRETTY_PRINT );
	exit;
}

/**
 * Import option groups from an exported JSON file. Only groups/fields that
 * are declared in config/admin.php are accepted, and every value passes the
 * same type sanitizer used by the save engine.
 */
function nt_tool_import_settings() {
	if ( empty( $_FILES['nt_import_file'] ) || ! is_array( $_FILES['nt_import_file'] ) ) {
		return __( 'No file uploaded.', NT_TEXT_DOMAIN );
	}
	$file = $_FILES['nt_import_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	if ( ! empty( $file['error'] ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return __( 'Upload failed. Please try again.', NT_TEXT_DOMAIN );
	}
	if ( (int) $file['size'] > 1048576 ) { // 1 MB is far beyond any settings file.
		return __( 'File too large.', NT_TEXT_DOMAIN );
	}

	$payload = json_decode( (string) file_get_contents( $file['tmp_name'] ), true );
	if ( ! is_array( $payload ) || empty( $payload['groups'] ) || ! is_array( $payload['groups'] ) ) {
		return __( 'Not a valid settings export file.', NT_TEXT_DOMAIN );
	}

	$admin    = nt_config( 'admin' );
	$imported = 0;
	foreach ( (array) ( $admin['options'] ?? array() ) as $group => $def ) {
		if ( ! isset( $payload['groups'][ $group ] ) || ! is_array( $payload['groups'][ $group ] ) ) {
			continue;
		}
		$incoming = $payload['groups'][ $group ];
		$clean    = array();
		foreach ( (array) ( $def['fields'] ?? array() ) as $key => $type ) {
			$clean[ $key ] = nt_admin_sanitize( $type, $incoming[ $key ] ?? '' );
		}
		update_option( (string) ( $def['option'] ?? 'nt_' . $group ), $clean );
		$imported++;
	}

	/* translators: %d: number of imported option groups */
	return sprintf( __( '%d settings group(s) imported.', NT_TEXT_DOMAIN ), $imported );
}
