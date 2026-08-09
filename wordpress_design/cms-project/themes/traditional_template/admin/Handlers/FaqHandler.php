<?php
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/BaseHandler.php';
require_once dirname(__DIR__) . '/Models/FaqModel.php';

class TT_Faq_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request('tt_save_faq');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$data = [
			'question' => self::post_text('question'),
			'answer' => self::post_textarea('answer'),
			'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0,
			'status' => self::post_text('status', 'active'),
		];
		
		if ($id > 0) {
			TT_Faq_Model::update($id, $data);
			self::redirect_success('content', 'faqs', 'FAQ updated.');
		} else {
			TT_Faq_Model::insert($data);
			self::redirect_success('content', 'faqs', 'FAQ added.');
		}
	}

	public static function handle_delete() {
		self::verify_request('tt_delete_faq');
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		if ($id > 0) {
			TT_Faq_Model::delete($id);
			self::redirect_success('content', 'faqs', 'FAQ deleted.');
		}
		self::redirect_error('content', 'faqs', 'Invalid ID.');
	}
}
