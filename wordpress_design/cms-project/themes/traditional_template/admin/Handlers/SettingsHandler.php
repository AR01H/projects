<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__) . '/Models/SettingsModel.php';

class TT_Settings_Handler extends TT_Base_Handler {
	public static function handle_save() {
		self::verify_request( 'tt_save_settings' );
		$redirect = admin_url( 'admin.php?page=' . sanitize_text_field($_POST['return_page'] ?? '') . '&subtab=' . sanitize_text_field($_POST['return_subtab'] ?? '') );
		
		if ( !empty($_POST['settings']) && is_array($_POST['settings']) ) {
			foreach ( $_POST['settings'] as $key => $val ) {
			    // Recursively sanitize array or string
			    $clean_val = is_array($val) ? self::sanitize_array($val) : wp_kses_post( wp_unslash( $val ) );
				TT_Settings_Model::update( sanitize_key($key), $clean_val );
			}
		}
		self::redirect_success( $redirect, 'Settings saved successfully.' );
	}
	
	private static function sanitize_array($arr) {
	    $clean = [];
	    foreach ($arr as $k => $v) {
	        $clean[sanitize_key($k)] = is_array($v) ? self::sanitize_array($v) : wp_kses_post( wp_unslash($v) );
	    }
	    return $clean;
	}
}
