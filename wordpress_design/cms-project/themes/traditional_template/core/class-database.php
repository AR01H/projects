<?php
/**
 * core/database.php - Custom table engine driven by config/database.php.
 *
 * Tables install automatically on theme activation, and one-by-one from
 * Theme -> Admin Tools -> Database. Queries against these tables use
 * self::table( 'key' ) for the name and $wpdb->prepare()/insert()/update()
 * for the SQL - always.
 */

defined( 'ABSPATH' ) || exit;

class App_Database {

/**
 * Full prefixed table name for a registry key: 'submissions' -> wp_nt_submissions.
 */
public static function table( $key ) {
	global $wpdb;
	$tables = App_Theme::config( 'database' );
	$name   = (string) ( $tables[ $key ]['table'] ?? 'app_' . sanitize_key( $key ) );
	return $wpdb->prefix . $name;
}

/**
 * Does the table exist in MySQL right now?
 */
public static function table_exists( $key ) {
	global $wpdb;
	$table = self::table( $key );
	return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
}

/**
 * Install (or repair) ONE registered table through dbDelta.
 *
 * @return bool True when the table exists after the run.
 */
public static function install( $key ) {
	global $wpdb;
	$tables = App_Theme::config( 'database' );
	$schema = (string) ( $tables[ $key ]['schema'] ?? '' );
	if ( '' === $schema ) {
		return false;
	}

	$sql = str_replace(
		array( '{table}', '{charset}' ),
		array( self::table( $key ), $wpdb->get_charset_collate() ),
		$schema
	);

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	return self::table_exists( $key );
}

/**
 * Install every registered table (theme activation + "Install All" tool).
 *
 * @return array key => bool (installed ok?)
 */
public static function install_all() {
	$results = array();
	foreach ( array_keys( App_Theme::config( 'database' ) ) as $key ) {
		$results[ $key ] = self::install( $key );
	}
	return $results;
}

/**
 * Status of every registered table - feeds the Admin Tools -> Database view.
 *
 * @return array key => array( table, desc, exists )
 */
public static function status() {
	$status = array();
	foreach ( App_Theme::config( 'database' ) as $key => $def ) {
		$status[ $key ] = array(
			'table'  => self::table( $key ),
			'desc'   => (string) ( $def['desc'] ?? '' ),
			'exists' => self::table_exists( $key ),
		);
	}
	return $status;
}

}
