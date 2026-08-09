<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__) . '/Models/NavModel.php';
class TT_Nav_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request( 'tt_save_nav' );
		$redirect = admin_url( 'admin.php?page=tt-admin-settings&subtab=nav' );
		$id = intval( $_POST['id'] ?? 0 );
		$data = array('label' => $_POST['label'] ?? '', 'url' => $_POST['url'] ?? '', 'sort_order' => $_POST['sort_order'] ?? 0);
		if ( $id > 0 ) TT_Nav_Model::update( $id, $data ); else TT_Nav_Model::insert( $data );
		self::redirect_success( $redirect, 'Saved.' );
	}
	public static function handle_delete() {
		self::verify_request( 'tt_delete_nav' );
		$redirect = admin_url( 'admin.php?page=tt-admin-settings&subtab=nav' );
		if ( intval($_GET['id']) > 0 ) TT_Nav_Model::delete( intval($_GET['id']) );
		self::redirect_success( $redirect, 'Deleted.' );
	}
}
