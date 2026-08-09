<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseModel.php';
class TT_Drinks_Model extends TT_Base_Model {
	protected static $table = 'tt_drinks';
	public static function insert( array $data ) {
		return self::insert_record( array('name' => sanitize_text_field($data['name']), 'description' => wp_kses_post($data['description']), 'image_url' => esc_url_raw($data['image_url']), 'sort_order' => intval($data['sort_order'] ?? 0)) );
	}
	public static function update( $id, array $data ) {
		return self::update_record( $id, array('name' => sanitize_text_field($data['name']), 'description' => wp_kses_post($data['description']), 'image_url' => esc_url_raw($data['image_url']), 'sort_order' => intval($data['sort_order'] ?? 0)) );
	}
}
