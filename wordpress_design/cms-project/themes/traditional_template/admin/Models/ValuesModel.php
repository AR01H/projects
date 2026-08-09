<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseModel.php';
class TT_Values_Model extends TT_Base_Model {
	protected static $table = 'tt_values';
	public static function insert( array $data ) { return self::insert_record( array('title' => sanitize_text_field($data['title'] ?? ''), 'description' => sanitize_text_field($data['description'] ?? ''), 'icon_url' => esc_url_raw($data['icon_url'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
	public static function update( $id, array $data ) { return self::update_record( $id, array('title' => sanitize_text_field($data['title'] ?? ''), 'description' => sanitize_text_field($data['description'] ?? ''), 'icon_url' => esc_url_raw($data['icon_url'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
}
