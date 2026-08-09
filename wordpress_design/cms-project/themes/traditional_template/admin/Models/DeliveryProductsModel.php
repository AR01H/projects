<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseModel.php';
class TT_Delivery_Products_Model extends TT_Base_Model {
	protected static $table = 'tt_delivery_products';
	public static function insert( array $data ) { return self::insert_record( array('name' => sanitize_text_field($data['name'] ?? ''), 'description' => sanitize_text_field($data['description'] ?? ''), 'image_url' => esc_url_raw($data['image_url'] ?? ''), 'price' => sanitize_text_field($data['price'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
	public static function update( $id, array $data ) { return self::update_record( $id, array('name' => sanitize_text_field($data['name'] ?? ''), 'description' => sanitize_text_field($data['description'] ?? ''), 'image_url' => esc_url_raw($data['image_url'] ?? ''), 'price' => sanitize_text_field($data['price'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
}
