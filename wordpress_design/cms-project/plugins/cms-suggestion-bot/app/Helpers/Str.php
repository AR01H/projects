<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Pure string utility methods only - no WordPress calls, no side effects.
 */
final class Str {

	private function __construct() {}

	public static function wordCount( string $text ): int {
		return str_word_count( wp_strip_all_tags( $text ) );
	}

	public static function excerpt( string $text, int $words = 40 ): string {
		return wp_trim_words( wp_strip_all_tags( $text ), $words, '…' );
	}

	/**
	 * Stable content hash used for change detection (Cache\ChunkHasher and
	 * CacheStorageInterface::existingHash()) - two reads of unchanged content
	 * always produce the same hash, so unchanged records are skipped on
	 * incremental cache regeneration.
	 */
	public static function hash( string $content ): string {
		return hash( 'sha256', $content );
	}

	/**
	 * Break long text into chunks for the chunk-level cache table
	 * (cms_sug_bot_chunks), packed up to ~$wordsPerChunk words at a time -
	 * but only ever cut on paragraph/sentence boundaries, never mid-sentence.
	 * A blind fixed-word-count slice would regularly split a sentence in
	 * half, which weakens both FULLTEXT relevance ranking and any future
	 * AI/RAG context quality - packing whole sentences keeps each chunk a
	 * coherent, independently meaningful unit.
	 *
	 * @return array<int, string>
	 */
	public static function chunk( string $text, int $wordsPerChunk = 200 ): array {
		$sentences = self::sentences( $text );
		if ( empty( $sentences ) ) {
			return array();
		}

		$wordsPerChunk = max( 1, $wordsPerChunk );
		$chunks        = array();
		$buffer        = array();
		$buffer_words  = 0;

		foreach ( $sentences as $sentence ) {
			$sentence_words = str_word_count( $sentence );

			// Flush the current buffer before adding a sentence that would push
			// it over the target - unless the buffer is still empty, in which
			// case a single very long sentence just becomes its own chunk.
			if ( $buffer_words > 0 && ( $buffer_words + $sentence_words ) > $wordsPerChunk ) {
				$chunks[]     = implode( ' ', $buffer );
				$buffer       = array();
				$buffer_words = 0;
			}

			$buffer[]      = $sentence;
			$buffer_words += $sentence_words;
		}

		if ( ! empty( $buffer ) ) {
			$chunks[] = implode( ' ', $buffer );
		}

		return $chunks;
	}

	/**
	 * Splits HTML/plain text into sentences, preserving paragraph breaks as
	 * sentence boundaries so two paragraphs never get glued into one run-on
	 * "sentence" by the regex below.
	 *
	 * @return array<int, string>
	 */
	private static function sentences( string $text ): array {
		// Turn block-level closing tags into paragraph breaks before stripping
		// tags, so paragraph structure survives into the plain-text pass.
		$text = (string) preg_replace(
			'#</(p|div|li|h[1-6]|br)\s*/?>|<br\s*/?>#i',
			"\n\n",
			$text
		);
		$text = trim( wp_strip_all_tags( $text ) );
		if ( '' === $text ) {
			return array();
		}

		$paragraphs = preg_split( '/\n\s*\n/', $text ) ?: array();
		$sentences  = array();

		foreach ( $paragraphs as $paragraph ) {
			$paragraph = trim( (string) preg_replace( '/\s+/', ' ', $paragraph ) );
			if ( '' === $paragraph ) {
				continue;
			}

			// Split after sentence-ending punctuation followed by whitespace +
			// a capital/opening-quote - good enough without a full NLP tokenizer.
			$parts = preg_split( '/(?<=[.!?])\s+(?=[A-Z0-9"\'\x{2018}\x{201C}])/u', $paragraph ) ?: array( $paragraph );
			foreach ( $parts as $part ) {
				$part = trim( $part );
				if ( '' !== $part ) {
					$sentences[] = $part;
				}
			}
		}

		return $sentences;
	}

	/**
	 * Case-insensitive whole-word match against a restricted-word list (see
	 * Services\RestrictedWordsService). Used to keep both indexed content and
	 * bot answers from surfacing blocked terms.
	 *
	 * @param array<int, string> $restricted
	 */
	public static function containsRestricted( string $text, array $restricted ): bool {
		if ( empty( $restricted ) ) {
			return false;
		}

		$pattern = implode( '|', array_map(
			static fn( string $w ) => preg_quote( trim( $w ), '/' ),
			array_filter( $restricted, static fn( string $w ) => '' !== trim( $w ) )
		) );

		if ( '' === $pattern ) {
			return false;
		}

		return 1 === preg_match( '/\b(?:' . $pattern . ')\b/iu', $text );
	}
}
