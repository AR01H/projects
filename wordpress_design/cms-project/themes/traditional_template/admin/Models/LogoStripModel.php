<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseModel.php';
class TT_Logo_Strip_Model extends TT_Base_Model {
	protected static $table = 'tt_logo_strip';
	public static function insert( array $data ) { return self::insert_record( array('name' => sanitize_text_field($data['name'] ?? ''), 'image_url' => esc_url_raw($data['image_url'] ?? ''), 'link_url' => esc_url_raw($data['link_url'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
	public static function update( $id, array $data ) { return self::update_record( $id, array('name' => sanitize_text_field($data['name'] ?? ''), 'image_url' => esc_url_raw($data['image_url'] ?? ''), 'link_url' => esc_url_raw($data['link_url'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
}
