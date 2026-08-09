<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseModel.php';
class TT_Ticker_Model extends TT_Base_Model {
	protected static $table = 'tt_ticker_items';
	public static function insert( array $data ) { return self::insert_record( array('content' => sanitize_text_field($data['content'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
	public static function update( $id, array $data ) { return self::update_record( $id, array('content' => sanitize_text_field($data['content'] ?? ''), 'sort_order' => intval($data['sort_order']??0)) ); }
}
