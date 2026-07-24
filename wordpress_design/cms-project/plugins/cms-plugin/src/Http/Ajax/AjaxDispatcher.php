<?php

namespace Ah\Cms\Http\Ajax;

defined( 'ABSPATH' ) || exit;

/**
 * AJAX Dispatcher — routes AJAX actions to feature controllers.
 * Centralizes nonce verification and capability checks.
 */
class AjaxDispatcher {

	/**
	 * Verify AJAX request security and permissions.
	 *
	 * @param string $action  The AJAX action name for nonce verification.
	 * @param string $capability  Required user capability.
	 * @return bool  True if valid, sends JSON error and returns false otherwise.
	 */
	public static function verify( string $action, string $capability = 'manage_options' ): bool {
		if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed.' ] );
			return false;
		}
		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error( [ 'message' => 'Access denied.' ] );
			return false;
		}
		return true;
	}

	/**
	 * Verify AJAX request for public (non-admin) endpoints.
	 */
	public static function verifyPublic( string $action ): bool {
		if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed.' ] );
			return false;
		}
		return true;
	}
}
