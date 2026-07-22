<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Repositories\KnowledgeRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Frequently-asked questions get answered straight from
 * cms_sug_bot_knowledge (via a WP object-cache-backed lookup) instead of
 * re-running Reader/Cache matching on every request - see Configuration ->
 * Common Questions Cache. Bot\AnswerResolver checks here first.
 */
final class CommonQuestionsService {

	private const CACHE_GROUP = 'csb_common_questions';

	public function __construct(
		private readonly KnowledgeRepository $repository,
		private readonly SettingsService $settings,
	) {}

	public function isEnabled(): bool {
		return (bool) $this->settings->get( 'common_questions', 'enabled', true );
	}

	/**
	 * Cached list of the top N knowledge entries by usage, refreshed every
	 * cache_ttl seconds (Configuration -> Common Questions Cache).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function top(): array {
		if ( ! $this->isEnabled() ) {
			return array();
		}

		$max    = (int) $this->settings->get( 'common_questions', 'max_entries', 200 );
		$ttl    = (int) $this->settings->get( 'common_questions', 'cache_ttl', DAY_IN_SECONDS );
		$cached = wp_cache_get( 'top', self::CACHE_GROUP );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$rows = $this->repository->topByUsage( $max );
		wp_cache_set( 'top', $rows, self::CACHE_GROUP, $ttl );

		return $rows;
	}

	/**
	 * Best keyword match against the cached common-questions list.
	 *
	 * @return array<string, mixed>|null
	 */
	public function match( string $question ): ?array {
		$needle = strtolower( trim( $question ) );
		if ( '' === $needle ) {
			return null;
		}

		foreach ( $this->top() as $entry ) {
			$haystack = strtolower( (string) $entry['question'] );
			if ( $haystack === $needle || str_contains( $haystack, $needle ) || str_contains( $needle, $haystack ) ) {
				$this->repository->incrementUsage( (int) $entry['id'] );

				return $entry;
			}
		}

		return null;
	}

	public function invalidate(): void {
		wp_cache_delete( 'top', self::CACHE_GROUP );
	}
}
