<?php
namespace Ah\Cms\Admin\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract base for AJAX handlers.
 * Provides standard nonce verification, capability check, and response methods.
 *
 * Usage: Extend this class and implement the abstract methods.
 */
abstract class AbstractAjaxHandler {

	/** Nonce action name for verification. */
	abstract protected function nonce_action(): string;

	/** Required capability (default: 'manage_options'). */
	protected function required_capability(): string { return 'manage_options'; }

	/**
	 * Handle the AJAX request.
	 * @return array{success: bool, data?: mixed, message?: string}
	 */
	abstract protected function handle(): array;

	// ── Public API ──────────────────────────────────────────────

	public function dispatch(): void {
		$this->verify_nonce();
		$this->verify_capability();

		$result = $this->handle();

		if ( $result['success'] ?? false ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ?? 'Error.' ) );
		}
	}

	protected function verify_nonce(): void {
		if ( ! check_ajax_referer( $this->nonce_action(), 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ) );
		}
	}

	protected function verify_capability(): void {
		if ( ! current_user_can( $this->required_capability() ) ) {
			wp_send_json_error( array( 'message' => 'Access denied.' ) );
		}
	}

	/** Get a POST value with default. */
	protected function post( string $key, $default = '' ) {
		return $_POST[ $key ] ?? $default;
	}

	/** Get a POST integer. */
	protected function post_int( string $key, int $default = 0 ): int {
		return (int) ( $_POST[ $key ] ?? $default );
	}

	/** Get a POST string, sanitized. */
	protected function post_str( string $key, string $default = '' ): string {
		return sanitize_text_field( wp_unslash( $_POST[ $key ] ?? $default ) );
	}

	/** Get a POST string, unsanitized (for HTML/JSON). */
	protected function post_raw( string $key, string $default = '' ): string {
		return wp_unslash( $_POST[ $key ] ?? $default );
	}

	/** Get a POST boolean (checkbox). */
	protected function post_bool( string $key ): bool {
		return ! empty( $_POST[ $key ] );
	}
}
