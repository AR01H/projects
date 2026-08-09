<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseHandler.php';
require_once dirname(__DIR__) . '/Models/TeamModel.php';

class TT_Team_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request('tt_save_team');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$data = [
			'name' => self::post_text('name'),
			'role' => self::post_text('role'),
			'bio' => self::post_textarea('bio'),
			'image_url' => self::post_text('image_url'),
			'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0,

		];
		
		if ($id > 0) {
			TT_Team_Model::update($id, $data);
			self::redirect_success('content', 'teams', 'Team updated.');
		} else {
			TT_Team_Model::insert($data);
			self::redirect_success('content', 'teams', 'Team added.');
		}
	}

	public static function handle_delete() {
		self::verify_request('tt_delete_team');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		if ($id > 0) {
			TT_Team_Model::delete($id);
			self::redirect_success('content', 'teams', 'Team deleted.');
		}
		self::redirect_error('content', 'teams', 'Invalid ID.');
	}
}
