<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__) . '/Models/BaseModel.php';

class TT_Forms_Handler extends TT_Base_Handler {
	public static function handle_save_form() {
		self::verify_request( 'tt_save_form' );
		global $wpdb;
		$form_id = sanitize_text_field($_POST['form_id'] ?? '');
		$redirect = admin_url( 'admin.php?page=tt-forms&subtab=list&edit=' . urlencode($form_id) );
		if (!$form_id) self::redirect_error($redirect, 'Missing form ID');
		
		$wpdb->update($wpdb->prefix . 'tt_forms', [
			'form_label' => sanitize_text_field($_POST['form_label']??''),
			'submit_text' => sanitize_text_field($_POST['submit_text']??'Submit')
		], ['id' => $form_id]);
		self::redirect_success( $redirect, 'Form saved.' );
	}

	public static function handle_save_field() {
		self::verify_request( 'tt_save_form_field' );
		global $wpdb;
		$id = intval($_POST['id'] ?? 0);
		$form_id = sanitize_text_field($_POST['form_id'] ?? '');
		$redirect = admin_url( 'admin.php?page=tt-forms&subtab=list&edit=' . urlencode($form_id) );
		
		$data = [
			'form_id' => $form_id,
			'step_id' => intval($_POST['step_id'] ?? 0),
			'field_id' => sanitize_text_field($_POST['field_id'] ?? ''),
			'name' => sanitize_text_field($_POST['name'] ?? ''),
			'type' => sanitize_text_field($_POST['type'] ?? 'text'),
			'label' => sanitize_text_field($_POST['label'] ?? ''),
			'placeholder' => sanitize_text_field($_POST['placeholder'] ?? ''),
			'options' => wp_unslash($_POST['options'] ?? ''), // JSON string
			'is_required' => isset($_POST['is_required']) ? 1 : 0,
			'is_multi_select' => isset($_POST['is_multi_select']) ? 1 : 0,
			'width' => sanitize_text_field($_POST['width'] ?? 'full'),
			'sort_order' => intval($_POST['sort_order'] ?? 0)
		];

		if ($id > 0) {
			$wpdb->update($wpdb->prefix . 'tt_form_fields', $data, ['id' => $id]);
		} else {
			$wpdb->insert($wpdb->prefix . 'tt_form_fields', $data);
		}
		self::redirect_success( $redirect, 'Field saved.' );
	}

	public static function handle_delete_field() {
		self::verify_request( 'tt_delete_form_field' );
		global $wpdb;
		$id = intval($_GET['id'] ?? 0);
		$form_id = sanitize_text_field($_GET['form_id'] ?? '');
		$redirect = admin_url( 'admin.php?page=tt-forms&subtab=list&edit=' . urlencode($form_id) );
		if ($id > 0) {
			$wpdb->delete($wpdb->prefix . 'tt_form_fields', ['id' => $id]);
		}
		self::redirect_success( $redirect, 'Field deleted.' );
	}
}
