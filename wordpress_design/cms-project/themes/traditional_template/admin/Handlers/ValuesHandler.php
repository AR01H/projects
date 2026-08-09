<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__) . '/Models/ValuesModel.php';
class TT_Values_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request( 'tt_save_value' );
		$redirect = admin_url( 'admin.php?page=' . sanitize_text_field($_POST['return_page']??'') . '&subtab=' . sanitize_text_field($_POST['return_subtab']??'') );
		$id = intval( $_POST['id'] ?? 0 );
		$data = array_map( 'wp_unslash', $_POST );
		if ( $id > 0 ) ValuesModel::update( $id, $data ); else ValuesModel::insert( $data );
		self::redirect_success( $redirect, 'Saved successfully.' );
	}
	public static function handle_delete() {
		self::verify_request( 'tt_delete_value' );
		$redirect = admin_url( 'admin.php?page=' . sanitize_text_field($_GET['return_page']??'') . '&subtab=' . sanitize_text_field($_GET['return_subtab']??'') );
		$id = intval( $_GET['id'] ?? 0 );
		if ( $id > 0 ) ValuesModel::delete( $id );
		self::redirect_success( $redirect, 'Deleted.' );
	}
}
