<?php
/**
 * Admin Handlers - Base class for all admin action handlers.
 *
 * Provides shared utilities: auth checks, redirects, nonce verification.
 */
defined( 'ABSPATH' ) || exit;

abstract class ADN_Base_Handler {

	const CAPABILITY = 'manage_options';

	/** Check user capability and nonce. Die on failure. */
	protected static function verify_request( string $nonce_action ): void {
		check_admin_referer( $nonce_action );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}
	}

	/** Tab page slug for redirects. */
	protected static function tab_url( string $tab, string $subtab = '' ): string {
		$slug = 'adn-theme-' . $tab;
		$args = array( 'page' => $slug );
		if ( '' !== $subtab ) {
			$args['subtab'] = $subtab;
		}
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/** Redirect back with success message. */
	protected static function redirect_success( string $tab, string $subtab, string $msg ): void {
		wp_safe_redirect( add_query_arg(
			array( 'adn_done' => 1, 'adn_msg' => rawurlencode( $msg ) ),
			self::tab_url( $tab, $subtab )
		) );
		exit;
	}

	/** Redirect back with error message. */
	protected static function redirect_error( string $tab, string $subtab, string $msg ): void {
		wp_safe_redirect( add_query_arg(
			array( 'adn_err' => 1, 'adn_msg' => rawurlencode( $msg ) ),
			self::tab_url( $tab, $subtab )
		) );
		exit;
	}

	/** Sanitize a text field from POST. */
	protected static function post_text( string $key, string $default = '' ): string {
		return sanitize_text_field( wp_unslash( $_POST[ $key ] ?? $default ) );
	}

	/** Sanitize a textarea field from POST. */
	protected static function post_textarea( string $key, string $default = '' ): string {
		return sanitize_textarea_field( wp_unslash( $_POST[ $key ] ?? $default ) );
	}

	/** Sanitize a URL field from POST. */
	protected static function post_url( string $key, string $default = '' ): string {
		return esc_url_raw( wp_unslash( $_POST[ $key ] ?? $default ) );
	}

	/** Sanitize an array of items with icon/label/url. */
	protected static function post_link_items( string $key, int $max = 10 ): array {
		$raw   = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : array();
		$items = array();
		foreach ( array_slice( $raw, 0, $max ) as $item ) {
			if ( ! is_array( $item ) ) { continue; }
			$label = sanitize_text_field( $item['label'] ?? '' );
			if ( '' === $label ) { continue; }
			$items[] = array(
				'icon'  => sanitize_text_field( $item['icon'] ?? '' ),
				'label' => $label,
				'url'   => esc_url_raw( $item['url'] ?? '' ),
			);
		}
		return $items;
	}
}
