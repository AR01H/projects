<?php
/**
 * core/redirects.php - Redirect engine.
 *
 * 1. Rule table: config/redirects.php (path => destination + status).
 * 2. Coming-soon gate: NT_COMING_SOON flag in config/theme.php.
 *
 * Runs at template_redirect priority 1, and never touches admin, AJAX,
 * REST or cron requests.
 */

defined( 'ABSPATH' ) || exit;

function nt_handle_redirects() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	$path = nt_request_path();

	// 1. Rule table.
	$rules = nt_config( 'redirects' );
	if ( '' !== $path && isset( $rules[ $path ] ) ) {
		$rule   = $rules[ $path ];
		$to     = (string) ( $rule['to'] ?? '' );
		$status = (int) ( $rule['status'] ?? 301 );
		if ( '' !== $to ) {
			$dest = preg_match( '#^https?://#i', $to ) ? $to : home_url( $to );
			// wp_safe_redirect only allows same-host + 'allowed_redirect_hosts'.
			wp_safe_redirect( $dest, in_array( $status, array( 301, 302, 307, 308 ), true ) ? $status : 301 );
			exit;
		}
	}

	// 2. Coming-soon gate (admins bypass; the landing page itself is allowed).
	if ( defined( 'NT_COMING_SOON' ) && true === NT_COMING_SOON ) {
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( is_page( NT_COMING_SOON_SLUG ) || NT_COMING_SOON_SLUG === $path ) {
			return;
		}
		wp_safe_redirect( home_url( '/' . NT_COMING_SOON_SLUG . '/' ), 302 );
		exit;
	}
}
