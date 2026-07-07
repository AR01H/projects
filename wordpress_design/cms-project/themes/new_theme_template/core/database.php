<?php
/**
 * core/database.php - Custom table engine driven by config/database.php.
 *
 * Tables install automatically on theme activation, and one-by-one from
 * Theme -> Admin Tools -> Database. Queries against these tables use
 * nt_db_table( 'key' ) for the name and $wpdb->prepare()/insert()/update()
 * for the SQL - always.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Full prefixed table name for a registry key: 'submissions' -> wp_nt_submissions.
 */
function nt_db_table( $key ) {
	global $wpdb;
	$tables = nt_config( 'database' );
	$name   = (string) ( $tables[ $key ]['table'] ?? 'nt_' . sanitize_key( $key ) );
	return $wpdb->prefix . $name;
}

/**
 * Does the table exist in MySQL right now?
 */
function nt_db_table_exists( $key ) {
	global $wpdb;
	$table = nt_db_table( $key );
	return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
}

/**
 * Install (or repair) ONE registered table through dbDelta.
 *
 * @return bool True when the table exists after the run.
 */
function nt_db_install( $key ) {
	global $wpdb;
	$tables = nt_config( 'database' );
	$schema = (string) ( $tables[ $key ]['schema'] ?? '' );
	if ( '' === $schema ) {
		return false;
	}

	$sql = str_replace(
		array( '{table}', '{charset}' ),
		array( nt_db_table( $key ), $wpdb->get_charset_collate() ),
		$schema
	);

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	return nt_db_table_exists( $key );
}

/**
 * Install every registered table (theme activation + "Install All" tool).
 *
 * @return array key => bool (installed ok?)
 */
function nt_db_install_all() {
	$results = array();
	foreach ( array_keys( nt_config( 'database' ) ) as $key ) {
		$results[ $key ] = nt_db_install( $key );
	}
	return $results;
}

/**
 * Status of every registered table - feeds the Admin Tools -> Database view.
 *
 * @return array key => array( table, desc, exists )
 */
function nt_db_status() {
	$status = array();
	foreach ( nt_config( 'database' ) as $key => $def ) {
		$status[ $key ] = array(
			'table'  => nt_db_table( $key ),
			'desc'   => (string) ( $def['desc'] ?? '' ),
			'exists' => nt_db_table_exists( $key ),
		);
	}
	return $status;
}
