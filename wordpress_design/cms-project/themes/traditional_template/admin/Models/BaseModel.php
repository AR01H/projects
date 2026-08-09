<?php
defined( 'ABSPATH' ) || exit;

abstract class TT_Base_Model {
	protected static $table = '';

	protected static function get_table() {
		global $wpdb;
		return $wpdb->prefix . static::$table;
	}

	public static function get_all( $order_by = 'sort_order ASC' ) {
		global $wpdb;
		$tbl = self::get_table();
		return $wpdb->get_results( "SELECT * FROM {$tbl} ORDER BY {$order_by}", ARRAY_A );
	}

	public static function get( $id ) {
		global $wpdb;
		$tbl = self::get_table();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl} WHERE id = %d", $id ), ARRAY_A );
	}

	public static function insert( array $data ) {
		global $wpdb;
		$wpdb->insert( self::get_table(), $data );
		return $wpdb->insert_id;
	}

	public static function update( $id, array $data ) {
		global $wpdb;
		$wpdb->update( self::get_table(), $data, array( 'id' => $id ) );
	}

	public static function delete( $id ) {
		global $wpdb;
		$wpdb->delete( self::get_table(), array( 'id' => $id ) );
	}
}
