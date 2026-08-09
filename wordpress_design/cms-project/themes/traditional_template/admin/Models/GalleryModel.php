<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseModel.php';
class TT_Gallery_Model extends TT_Base_Model {
	protected static $table = 'tt_gallery';
	public static function insert( array $data ) { return self::insert_record( array('title' => sanitize_text_field($data['title'] ?? ''), 'image_url' => esc_url_raw($data['image_url'] ?? ''), 'alt' => sanitize_text_field($data['alt'] ?? ''), 'category' => sanitize_text_field($data['category'] ?? ''), 'section' => sanitize_text_field($data['section'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
	public static function update( $id, array $data ) { return self::update_record( $id, array('title' => sanitize_text_field($data['title'] ?? ''), 'image_url' => esc_url_raw($data['image_url'] ?? ''), 'alt' => sanitize_text_field($data['alt'] ?? ''), 'category' => sanitize_text_field($data['category'] ?? ''), 'section' => sanitize_text_field($data['section'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
}
