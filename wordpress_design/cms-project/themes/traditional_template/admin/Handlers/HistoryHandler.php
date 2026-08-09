<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseHandler.php';
require_once dirname(__DIR__) . '/Models/HistoryModel.php';

class TT_History_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request('tt_save_history');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$data = [
			'year' => self::post_text('year'),
			'title' => self::post_text('title'),
			'description' => self::post_textarea('description'),
			'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0,

		];
		
		if ($id > 0) {
			TT_History_Model::update($id, $data);
			self::redirect_success('content', 'historys', 'History updated.');
		} else {
			TT_History_Model::insert($data);
			self::redirect_success('content', 'historys', 'History added.');
		}
	}

	public static function handle_delete() {
		self::verify_request('tt_delete_history');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		if ($id > 0) {
			TT_History_Model::delete($id);
			self::redirect_success('content', 'historys', 'History deleted.');
		}
		self::redirect_error('content', 'historys', 'Invalid ID.');
	}
}
