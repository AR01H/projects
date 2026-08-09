<?php
/**
 * admin/Handlers/BaseHandler.php - Centralized base for admin actions.
 */
defined( 'ABSPATH' ) || exit;

abstract class TT_Base_Handler {
	const CAPABILITY = 'manage_options';
	const MENU_SLUG = 'tt-admin';

	protected static function verify_request( string $nonce_action ): void {
		check_admin_referer( $nonce_action );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized' ) );
		}
	}

	protected static function tab_url( string $tab, string $subtab = '' ): string {
		$slug = self::MENU_SLUG . '-' . $tab;
		if ($tab === 'dashboard') {
			$slug = self::MENU_SLUG; // dashboard is main page
		}
		$args = array( 'page' => $slug );
		if ( '' !== $subtab ) {
			$args['subtab'] = $subtab;
		}
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	protected static function redirect_success( string $tab, string $subtab, string $msg ): void {
		wp_safe_redirect( add_query_arg(
			array( 'tt_done' => 1, 'tt_msg' => rawurlencode( $msg ) ),
			self::tab_url( $tab, $subtab )
		) );
		exit;
	}

	protected static function redirect_error( string $tab, string $subtab, string $msg ): void {
		wp_safe_redirect( add_query_arg(
			array( 'tt_err' => 1, 'tt_msg' => rawurlencode( $msg ) ),
			self::tab_url( $tab, $subtab )
		) );
		exit;
	}

	protected static function post_text( string $key, string $default = '' ): string {
		return sanitize_text_field( wp_unslash( $_POST[ $key ] ?? $default ) );
	}

	protected static function post_textarea( string $key, string $default = '' ): string {
		return sanitize_textarea_field( wp_unslash( $_POST[ $key ] ?? $default ) );
	}
}
