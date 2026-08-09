<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseModel.php';
class TT_Events_Features_Model extends TT_Base_Model {
	protected static $table = 'tt_events_features';
	public static function insert( array $data ) {
		return self::insert_record( array('label' => sanitize_text_field($data['label']), 'icon_url' => esc_url_raw($data['icon_url']), 'sort_order' => intval($data['sort_order'] ?? 0)) );
	}
	public static function update( $id, array $data ) {
		return self::update_record( $id, array('label' => sanitize_text_field($data['label']), 'icon_url' => esc_url_raw($data['icon_url']), 'sort_order' => intval($data['sort_order'] ?? 0)) );
	}
}
