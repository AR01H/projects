<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Ajax;

use CmsSuggestionBot\Bot\BotEngine;

defined( 'ABSPATH' ) || exit;

/**
 * Public chat endpoint (front-end widget -> "ask a question"). Works for
 * both logged-in and guest visitors, unlike Ajax\CacheAjaxController which
 * is admin-only.
 */
final class BotAjaxController {

	private const ACTION = 'csb_ask';

	public function __construct( private readonly BotEngine $engine ) {}

	public function hooks(): void {
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'ask' ) );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, array( $this, 'ask' ) );
	}

	public function ask(): void {
		check_ajax_referer( 'csb_chat', 'nonce' );

		$question = isset( $_POST['question'] ) ? sanitize_textarea_field( wp_unslash( $_POST['question'] ) ) : '';
		if ( '' === $question ) {
			wp_send_json_error( array( 'message' => __( 'Please type a question.', 'cms-suggestion-bot' ) ) );
		}

		$session_id = $this->sessionId();
		$visitor    = $this->visitorKey();
		$user_id    = get_current_user_id() ?: null;
		$post_id    = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null;

		$result = $this->engine->ask( $question, $session_id, $visitor, $user_id, $post_id );

		wp_send_json_success( $result );
	}

	private function sessionId(): string {
		$posted = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';

		return '' !== $posted ? $posted : wp_generate_uuid4();
	}

	/**
	 * Stable per-visitor identifier for the daily usage cap - doesn't need to
	 * be reversible, just consistent for the same visitor within a day.
	 */
	private function visitorKey(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';

		return md5( $ip . '|' . $ua );
	}
}
