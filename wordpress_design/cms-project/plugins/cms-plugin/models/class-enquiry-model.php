<?php
defined( 'ABSPATH' ) || exit;

class AH_Enquiry_Model {

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_enquiries';
	}

	public static function install_table(): void {
		global $wpdb;
		$table = self::table();
		$cs    = $wpdb->get_charset_collate();

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$table}` (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			form_type   VARCHAR(20)  NOT NULL DEFAULT 'contact',
			name        VARCHAR(200) NOT NULL DEFAULT '',
			email       VARCHAR(200) NOT NULL DEFAULT '',
			help_topic  VARCHAR(200) NOT NULL DEFAULT '',
			data        JSON         NOT NULL,
			ip_address  VARCHAR(45)  DEFAULT NULL,
			sub_status  VARCHAR(20)  NOT NULL DEFAULT 'new',
			admin_notes TEXT         NOT NULL DEFAULT '',
			created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
			KEY idx_form_type  (form_type),
			KEY idx_sub_status (sub_status),
			KEY idx_created_at (created_at)
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Insert a new enquiry row.
	 *
	 * @param string $form_type  'contact' or 'guidance'
	 * @param string $name       Submitter name
	 * @param string $email      Submitter email
	 * @param string $help_topic Enquiry type / help_with topic
	 * @param array  $data       All form fields (JSON-encoded internally)
	 * @param string $ip         Submitter IP address
	 * @return int               Insert ID, or 0 on failure
	 */
	public static function create( string $form_type, string $name, string $email, string $help_topic, array $data, string $ip ): int {
		global $wpdb;

		$inserted = $wpdb->insert(
			self::table(),
			array(
				'form_type'  => $form_type,
				'name'       => $name,
				'email'      => $email,
				'help_topic' => $help_topic,
				'data'       => wp_json_encode( $data ),
				'ip_address' => $ip ?: null,
				'sub_status' => 'new',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Retrieve a single enquiry by ID.
	 *
	 * @param int $id Row ID
	 * @return object|null
	 */
	public static function get( int $id ): ?object {
		global $wpdb;
		$table = self::table();
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE id = %d",
			$id
		) ) ?: null;
	}

	/**
	 * Return a paginated list of enquiries.
	 *
	 * @param string $form_type 'contact' or 'guidance'
	 * @param string $status    Optional sub_status filter
	 * @param int    $limit     Rows per page
	 * @param int    $offset    Row offset
	 * @return array
	 */
	public static function get_list( string $form_type, string $status = '', int $limit = 20, int $offset = 0 ): array {
		global $wpdb;
		$table = self::table();

		if ( '' !== $status ) {
			$sql = $wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE form_type = %s AND sub_status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$form_type,
				$status,
				$limit,
				$offset
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE form_type = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$form_type,
				$limit,
				$offset
			);
		}

		return $wpdb->get_results( $sql ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Count enquiries for a form type, optionally filtered by status.
	 *
	 * @param string $form_type 'contact' or 'guidance'
	 * @param string $status    Optional sub_status filter
	 * @return int
	 */
	public static function count( string $form_type, string $status = '' ): int {
		global $wpdb;
		$table = self::table();

		if ( '' !== $status ) {
			return (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE form_type = %s AND sub_status = %s",
				$form_type,
				$status
			) );
		}

		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM `{$table}` WHERE form_type = %s",
			$form_type
		) );
	}

	/**
	 * Update status and admin notes for an enquiry.
	 *
	 * @param int    $id          Row ID
	 * @param string $sub_status  New status value
	 * @param string $admin_notes Admin notes text
	 * @return bool
	 */
	public static function update_meta( int $id, string $sub_status, string $admin_notes ): bool {
		global $wpdb;
		$result = $wpdb->update(
			self::table(),
			array(
				'sub_status'  => $sub_status,
				'admin_notes' => $admin_notes,
			),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		return false !== $result;
	}
}
