<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__) . '/Models/ProcessStepsModel.php';
class TT_Process_Steps_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request( 'tt_save_process_step' );
		$redirect = admin_url( 'admin.php?page=' . sanitize_text_field($_POST['return_page']??'') . '&subtab=' . sanitize_text_field($_POST['return_subtab']??'') );
		$id = intval( $_POST['id'] ?? 0 );
		$data = array_map( 'wp_unslash', $_POST );
		if ( $id > 0 ) ProcessStepsModel::update( $id, $data ); else ProcessStepsModel::insert( $data );
		self::redirect_success( $redirect, 'Saved successfully.' );
	}
	public static function handle_delete() {
		self::verify_request( 'tt_delete_process_step' );
		$redirect = admin_url( 'admin.php?page=' . sanitize_text_field($_GET['return_page']??'') . '&subtab=' . sanitize_text_field($_GET['return_subtab']??'') );
		$id = intval( $_GET['id'] ?? 0 );
		if ( $id > 0 ) ProcessStepsModel::delete( $id );
		self::redirect_success( $redirect, 'Deleted.' );
	}
}
