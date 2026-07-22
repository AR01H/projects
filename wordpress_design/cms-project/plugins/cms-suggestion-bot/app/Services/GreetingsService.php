<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Matches small talk ("hi", "how are you", "thanks") against Configuration
 * -> Greetings - pure in-memory string comparison, no DB query at all, since
 * these phrases are usually too short for FULLTEXT search (MySQL won't even
 * index a 2-character word like "hi") and aren't real site content anyway.
 * Checked first in Bot\AnswerResolver, right after restricted words.
 */
final class GreetingsService {

	public function __construct( private readonly SettingsService $settings ) {}

	public function isEnabled(): bool {
		return (bool) $this->settings->get( 'greetings', 'enabled', true );
	}

	/**
	 * @return array<string, string>
	 */
	public function phrases(): array {
		$phrases = $this->settings->get( 'greetings', 'phrases', array() );

		return is_array( $phrases ) ? $phrases : array();
	}

	/**
	 * Only short messages are checked at all (see MAX_WORDS) - a real
	 * question that happens to contain a greeting word ("thanks, but where's
	 * the contact page") must never get short-circuited into "You're
	 * welcome!" instead of an actual answer. Within that short-message
	 * window, matches when the question IS a configured phrase or is built
	 * entirely around one (e.g. "hi there!", "thanks a lot" both match).
	 */
	private const MAX_WORDS = 5;

	public function match( string $question ): ?string {
		if ( ! $this->isEnabled() ) {
			return null;
		}

		$normalized = $this->normalize( $question );
		if ( '' === $normalized || str_word_count( $normalized ) > self::MAX_WORDS ) {
			return null;
		}

		foreach ( $this->phrases() as $phrase => $response ) {
			$needle = $this->normalize( $phrase );
			if ( '' === $needle ) {
				continue;
			}

			if ( $normalized === $needle || 1 === preg_match( '/\b' . preg_quote( $needle, '/' ) . '\b/u', $normalized ) ) {
				return $response;
			}
		}

		return null;
	}

	private function normalize( string $text ): string {
		$text = strtolower( trim( $text ) );

		return trim( (string) preg_replace( '/[^\p{L}\p{N}\s\']/u', '', $text ) );
	}
}
