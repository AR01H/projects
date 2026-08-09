<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__) . '/Models/EventsFeaturesModel.php';
class TT_Events_Features_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request( 'tt_save_event_feature' );
		$redirect = admin_url( 'admin.php?page=tt-admin-content&subtab=eventsfeatures' );
		$id = intval( $_POST['id'] ?? 0 );
		$data = array('label' => $_POST['label'] ?? '', 'icon_url' => $_POST['icon_url'] ?? '', 'sort_order' => $_POST['sort_order'] ?? 0);
		if ( $id > 0 ) TT_Events_Features_Model::update( $id, $data ); else TT_Events_Features_Model::insert( $data );
		self::redirect_success( $redirect, 'Saved.' );
	}
	public static function handle_delete() {
		self::verify_request( 'tt_delete_event_feature' );
		$redirect = admin_url( 'admin.php?page=tt-admin-content&subtab=eventsfeatures' );
		if ( intval($_GET['id']) > 0 ) TT_Events_Features_Model::delete( intval($_GET['id']) );
		self::redirect_success( $redirect, 'Deleted.' );
	}
}
