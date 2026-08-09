<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseModel.php';
class TT_Settings_Model {
	public static function get( string $key ) {
		global $wpdb;
		$val = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM {$wpdb->prefix}tt_settings WHERE setting_key = %s", $key ) );
		if ( ! $val ) return '';
		$decoded = json_decode($val, true);
		return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $val;
	}
	public static function update( string $key, $value ) {
		global $wpdb;
		$val_str = is_array($value) ? wp_json_encode($value) : (string) $value;
		return $wpdb->replace( $wpdb->prefix . 'tt_settings', array( 'setting_key' => $key, 'setting_value' => $val_str ), array( '%s', '%s' ) );
	}
}
