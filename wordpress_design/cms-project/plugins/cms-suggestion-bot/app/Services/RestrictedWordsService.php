<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Restricted-word list support (Configuration -> Restricted Words). Applied
 * in two places: CacheBuilder-fed content before it's stored (so blocked
 * terms never enter the index) and Bot\AnswerResolver output (so they never
 * appear in an answer either), per Configuration's "mode" setting.
 */
final class RestrictedWordsService {

	public function __construct( private readonly SettingsService $settings ) {}

	public function isEnabled(): bool {
		return (bool) $this->settings->get( 'restricted_words', 'enabled', false );
	}

	/**
	 * @return array<int, string>
	 */
	public function words(): array {
		$words = $this->settings->get( 'restricted_words', 'words', array() );

		return is_array( $words ) ? array_values( array_filter( array_map( 'trim', $words ) ) ) : array();
	}

	public function mode(): string {
		return (string) $this->settings->get( 'restricted_words', 'mode', 'mask' );
	}

	public function contains( string $text ): bool {
		return $this->isEnabled() && Str::containsRestricted( $text, $this->words() );
	}

	/**
	 * Applies the configured mode to $text: 'mask' replaces each restricted
	 * word with asterisks, 'block' returns an empty string outright.
	 */
	public function apply( string $text ): string {
		if ( ! $this->contains( $text ) ) {
			return $text;
		}

		if ( 'block' === $this->mode() ) {
			return '';
		}

		$pattern = implode( '|', array_map( static fn( string $w ) => preg_quote( $w, '/' ), $this->words() ) );

		return (string) preg_replace_callback(
			'/\b(?:' . $pattern . ')\b/iu',
			static fn( array $m ) => str_repeat( '*', mb_strlen( $m[0] ) ),
			$text
		);
	}
}
