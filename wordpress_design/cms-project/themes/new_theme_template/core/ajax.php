<?php
/**
 * core/ajax.php - Generic AJAX dispatcher driven by config/ajax.php.
 *
 * For every registry entry 'foo' this registers wp_ajax_nt_foo (and
 * wp_ajax_nopriv_nt_foo when 'public'). The dispatcher then enforces, in
 * order, BEFORE the callback runs:
 *   1. nonce  - check_ajax_referer( 'nt_ajax_foo', 'nonce' )
 *   2. capability - current_user_can() when configured
 *   3. lazy handler file load (realpath-guarded)
 * So security is a property of the ENGINE, not of each handler.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registration loop - hooked on init by the bootstrap.
 */
function nt_register_ajax_actions() {
	foreach ( nt_config( 'ajax' ) as $action => $def ) {
		$action   = sanitize_key( $action );
		$dispatch = static function () use ( $action, $def ) {
			nt_ajax_dispatch( $action, $def );
		};
		add_action( 'wp_ajax_nt_' . $action, $dispatch );
		if ( ! empty( $def['public'] ) ) {
			add_action( 'wp_ajax_nopriv_nt_' . $action, $dispatch );
		}
	}
}

/**
 * The dispatcher every action runs through.
 */
function nt_ajax_dispatch( $action, $def ) {

	// 1. Nonce (on unless explicitly disabled in the registry).
	if ( ! isset( $def['nonce'] ) || false !== $def['nonce'] ) {
		if ( ! check_ajax_referer( 'nt_ajax_' . $action, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', NT_TEXT_DOMAIN ) ), 403 );
		}
	}

	// 2. Capability.
	if ( ! empty( $def['capability'] ) && ! current_user_can( $def['capability'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', NT_TEXT_DOMAIN ) ), 403 );
	}

	// 3. Lazy-load the handler file, then call it.
	if ( ! empty( $def['file'] ) ) {
		nt_require_theme_file( $def['file'] );
	}
	$callback = $def['callback'] ?? '';
	if ( ! is_callable( $callback ) ) {
		wp_send_json_error( array( 'message' => 'Handler not found for action: ' . $action ), 500 );
	}

	call_user_func( $callback );

	// Callbacks should reply with wp_send_json_*; this is a safety net.
	wp_send_json_error( array( 'message' => 'Handler returned no response.' ), 500 );
}
