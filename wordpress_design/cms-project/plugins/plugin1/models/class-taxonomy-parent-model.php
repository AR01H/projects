<?php
defined( 'ABSPATH' ) || exit;

class AH_Taxonomy_Parent_Model extends AH_Model_Base {

	protected string $table_suffix = 'taxonomy_parent_terms';

	public static function ensure_table(): void {
		global $wpdb;
		$p  = $wpdb->prefix;
		$cs = 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';

		// Create table
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$p}ah_taxonomy_parent_terms` (
			id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name        VARCHAR(200)  NOT NULL,
			slug        VARCHAR(200)  NOT NULL UNIQUE,
			description TEXT,
			color       VARCHAR(20)   DEFAULT NULL,
			icon_emoji  VARCHAR(20)   DEFAULT NULL,
			image_id    INT UNSIGNED  DEFAULT NULL,
			status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
			sort_order  INT           NOT NULL DEFAULT 0,
			created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Add parent_term_id column to ah_taxonomies if missing
		$has = $wpdb->get_results( $wpdb->prepare(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'parent_term_id'",
			DB_NAME,
			"{$p}ah_taxonomies"
		) );
		if ( empty( $has ) ) {
			$wpdb->query( "ALTER TABLE `{$p}ah_taxonomies` ADD COLUMN `parent_term_id` INT UNSIGNED DEFAULT NULL AFTER `parent_id`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	public function get_all_active(): array {
		return $this->all( array(
			'where'    => "status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}

	public function get_all(): array {
		return $this->all( array(
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}

	public function count_children( int $parent_term_id ): int {
		global $wpdb;
		$table = AH_DB_Helper::table( 'taxonomies' );
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM `{$table}` WHERE parent_term_id = %d",
			$parent_term_id
		) );
	}
}