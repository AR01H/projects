<?php
defined( 'ABSPATH' ) || exit;

class AH_Enquiry_Model {

	private static bool $installed = false;

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'adn_enquiries';
	}

	public static function install_table(): void {
		if ( self::$installed ) { return; }
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
			`id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`form_type`   VARCHAR(20)  NOT NULL DEFAULT 'contact',
			`name`        VARCHAR(200) NOT NULL DEFAULT '',
			`email`       VARCHAR(200) NOT NULL DEFAULT '',
			`help_topic`  VARCHAR(200) NOT NULL DEFAULT '',
			`data`        LONGTEXT     NOT NULL,
			`ip_address`  VARCHAR(45)  DEFAULT NULL,
			`region`      VARCHAR(200) NOT NULL DEFAULT '',
			`user_agent`  VARCHAR(500) NOT NULL DEFAULT '',
			`sub_status`  VARCHAR(20)  NOT NULL DEFAULT 'new',
			`admin_notes` TEXT         NOT NULL,
			`created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `form_type`  (`form_type`),
			KEY `sub_status` (`sub_status`),
			KEY `created_at` (`created_at`)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		self::$installed = true;
	}

	public static function maybe_install(): void {
		$ver = get_option( 'adn_enquiry_table_ver', '' );
		if ( '2' === $ver ) { return; }
		self::$installed = false; // allow re-run when upgrading
		self::install_table();
		update_option( 'adn_enquiry_table_ver', '2' );
	}

	public static function create(
		string $form_type,
		string $name,
		string $email,
		string $help_topic,
		array  $data,
		string $ip,
		string $region     = '',
		string $user_agent = ''
	): int {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table(),
			array(
				'form_type'   => $form_type,
				'name'        => $name,
				'email'       => $email,
				'help_topic'  => $help_topic,
				'data'        => wp_json_encode( $data ),
				'ip_address'  => $ip,
				'region'      => $region,
				'user_agent'  => $user_agent,
				'sub_status'  => 'new',
				'admin_notes' => '',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	public static function get( int $id ): ?object {
		global $wpdb;
		$table = self::table();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $id ) );
		return $row ?: null;
	}

	public static function get_list(
		string $form_type,
		string $status = '',
		int    $limit  = 20,
		int    $offset = 0
	): array {
		global $wpdb;
		$table = self::table();
		if ( '' !== $status ) {
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE form_type = %s AND sub_status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$form_type, $status, $limit, $offset
			) );
		} else {
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE form_type = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$form_type, $limit, $offset
			) );
		}
		return is_array( $rows ) ? $rows : array();
	}

	public static function count( string $form_type, string $status = '' ): int {
		global $wpdb;
		$table = self::table();
		if ( '' !== $status ) {
			return (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE form_type = %s AND sub_status = %s",
				$form_type, $status
			) );
		}
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM `{$table}` WHERE form_type = %s",
			$form_type
		) );
	}

	public static function update_meta( int $id, string $sub_status, string $admin_notes ): bool {
		global $wpdb;
		$ok = $wpdb->update(
			self::table(),
			array( 'sub_status' => $sub_status, 'admin_notes' => $admin_notes ),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		return false !== $ok;
	}
}
