<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__) . '/Models/DrinksModel.php';
class TT_Drinks_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request( 'tt_save_drink' );
		$redirect = admin_url( 'admin.php?page=tt-admin-content&subtab=drinkslist' );
		$id = intval( $_POST['id'] ?? 0 );
		$data = array(
			'name' => $_POST['name'] ?? '',
			'description' => $_POST['description'] ?? '',
			'image_url' => $_POST['image_url'] ?? '',
			'sort_order' => $_POST['sort_order'] ?? 0,
		);
		if ( $id > 0 ) TT_Drinks_Model::update( $id, $data );
		else TT_Drinks_Model::insert( $data );
		self::redirect_success( $redirect, 'Drink saved successfully.' );
	}
	public static function handle_delete() {
		self::verify_request( 'tt_delete_drink' );
		$redirect = admin_url( 'admin.php?page=tt-admin-content&subtab=drinkslist' );
		$id = intval( $_GET['id'] ?? 0 );
		if ( $id > 0 ) TT_Drinks_Model::delete( $id );
		self::redirect_success( $redirect, 'Drink deleted.' );
	}
}
