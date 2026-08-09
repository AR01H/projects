<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseHandler.php';
require_once dirname(__DIR__) . '/Models/ReviewModel.php';

class TT_Review_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request('tt_save_review');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$data = [
			'reviewer_name' => self::post_text('reviewer_name'),
			'review_text' => self::post_textarea('review_text'),
			'source' => self::post_text('source'),
			'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0,

		];
		
		if ($id > 0) {
			TT_Review_Model::update($id, $data);
			self::redirect_success('content', 'reviews', 'Review updated.');
		} else {
			TT_Review_Model::insert($data);
			self::redirect_success('content', 'reviews', 'Review added.');
		}
	}

	public static function handle_delete() {
		self::verify_request('tt_delete_review');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		if ($id > 0) {
			TT_Review_Model::delete($id);
			self::redirect_success('content', 'reviews', 'Review deleted.');
		}
		self::redirect_error('content', 'reviews', 'Invalid ID.');
	}
}
