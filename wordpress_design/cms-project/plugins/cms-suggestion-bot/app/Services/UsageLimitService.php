<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Caps how many questions one visitor can send the bot (Configuration ->
 * Usage Limits) - independent of the app/API rate_limit setting, which
 * governs external API callers instead. Counters live in transients (fast,
 * self-expiring) rather than querying cms_sug_bot_messages on every message;
 * the messages table still records full history for admin review regardless
 * of whether limiting is enabled.
 */
final class UsageLimitService {

	public function __construct( private readonly SettingsService $settings ) {}

	public function isEnabled(): bool {
		return (bool) $this->settings->get( 'usage_limits', 'enabled', false );
	}

	public function limitReachedMessage(): string {
		return (string) $this->settings->get(
			'usage_limits',
			'limit_reached_message',
			"You've reached the question limit for now - please try again later."
		);
	}

	/**
	 * @param string $session_id   Bot\BotEngine conversation session id.
	 * @param string $visitor_key  Stable per-visitor identifier for the daily
	 *                             cap (e.g. a hashed IP or long-lived cookie value).
	 */
	public function isAllowed( string $session_id, string $visitor_key ): bool {
		if ( ! $this->isEnabled() ) {
			return true;
		}

		return $this->sessionCount( $session_id ) < $this->maxPerSession()
			&& $this->dayCount( $visitor_key ) < $this->maxPerDay();
	}

	public function recordMessage( string $session_id, string $visitor_key ): void {
		if ( ! $this->isEnabled() ) {
			return;
		}

		$session_key = $this->sessionTransientKey( $session_id );
		set_transient( $session_key, $this->sessionCount( $session_id ) + 1, HOUR_IN_SECONDS );

		$day_key = $this->dayTransientKey( $visitor_key );
		set_transient( $day_key, $this->dayCount( $visitor_key ) + 1, DAY_IN_SECONDS );
	}

	private function maxPerSession(): int {
		return (int) $this->settings->get( 'usage_limits', 'max_messages_per_session', 20 );
	}

	private function maxPerDay(): int {
		return (int) $this->settings->get( 'usage_limits', 'max_messages_per_day', 50 );
	}

	private function sessionCount( string $session_id ): int {
		return (int) get_transient( $this->sessionTransientKey( $session_id ) );
	}

	private function dayCount( string $visitor_key ): int {
		return (int) get_transient( $this->dayTransientKey( $visitor_key ) );
	}

	private function sessionTransientKey( string $session_id ): string {
		return 'csb_ul_session_' . md5( $session_id );
	}

	private function dayTransientKey( string $visitor_key ): string {
		return 'csb_ul_day_' . md5( $visitor_key ) . '_' . gmdate( 'Y-m-d' );
	}
}
