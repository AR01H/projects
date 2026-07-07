<?php
/**
 * config/database.php - Custom DB TABLE registry.
 *
 * core/database.php loops this array:
 *   - theme activation installs every table automatically,
 *   - Theme -> Admin Tools -> Database shows each table's status with an
 *     "Install / Repair" button per table (checked and installed one by one),
 *   - nt_db_table( 'key' ) returns the full prefixed table name for queries.
 *
 * ADD A TABLE (2 steps): add an entry here, then use nt_db_table( 'key' )
 * in your handlers. No installer code - the engine runs dbDelta.
 *
 * Schema placeholders (replaced by the engine):
 *   {table}   -> wp_{prefix}table name
 *   {charset} -> $wpdb->get_charset_collate()
 *
 * dbDelta rules: two spaces after "PRIMARY KEY", one column/KEY per line.
 */

defined( 'ABSPATH' ) || exit;

return array(

	// Contact form submissions - powers the "Contact Submissions" submenu.
	'submissions' => array(
		'table'  => 'nt_submissions',
		'desc'   => 'Contact form submissions inbox.',
		'schema' => "CREATE TABLE {table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(190) NOT NULL DEFAULT '',
			email VARCHAR(190) NOT NULL DEFAULT '',
			phone VARCHAR(64) NOT NULL DEFAULT '',
			message TEXT NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'new',
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY created_at (created_at)
		) {charset};",
	),

	// Example - copy this shape for the next site-specific table:
	// 'enquiries' => array(
	//     'table'  => 'nt_enquiries',
	//     'desc'   => 'Guidance enquiry submissions.',
	//     'schema' => "CREATE TABLE {table} ( ... ) {charset};",
	// ),
);
