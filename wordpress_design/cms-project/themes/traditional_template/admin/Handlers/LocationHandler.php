<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseHandler.php';
require_once dirname(__DIR__) . '/Models/LocationModel.php';

class TT_Location_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request('tt_save_location');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$data = [
			'name' => self::post_text('name'),
			'address' => self::post_textarea('address'),
			'phone' => self::post_text('phone'),
			'email' => self::post_text('email'),
			'map_url' => self::post_text('map_url'),
			'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0,

		];
		
		if ($id > 0) {
			TT_Location_Model::update($id, $data);
			self::redirect_success('content', 'locations', 'Location updated.');
		} else {
			TT_Location_Model::insert($data);
			self::redirect_success('content', 'locations', 'Location added.');
		}
	}

	public static function handle_delete() {
		self::verify_request('tt_delete_location');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		if ($id > 0) {
			TT_Location_Model::delete($id);
			self::redirect_success('content', 'locations', 'Location deleted.');
		}
		self::redirect_error('content', 'locations', 'Invalid ID.');
	}
}
