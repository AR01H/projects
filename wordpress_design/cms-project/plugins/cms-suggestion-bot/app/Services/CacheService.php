<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Cache\CacheBuilder;
use CmsSuggestionBot\Contracts\CacheStorageInterface;
use CmsSuggestionBot\Logger\Logger;
use CmsSuggestionBot\Readers\ReaderManager;

defined( 'ABSPATH' ) || exit;

/**
 * Admin-facing entry point for every "Admin Tools -> Cache" action (Generate,
 * Destroy, Rebuild) and the Dashboard's cache statistics. Controllers/Ajax
 * handlers call this - never CacheBuilder or CacheStorageInterface directly.
 */
final class CacheService {

	public function __construct(
		private readonly CacheBuilder $builder,
		private readonly CacheStorageInterface $storage,
		private readonly ReaderManager $readers,
		private readonly ReaderService $readerService,
		private readonly SettingsService $settings,
		private readonly Logger $logger,
	) {}

	public function settingEnabled( string $key ): bool {
		return (bool) $this->settings->get( 'cache', $key, false );
	}

	/**
	 * Generate cache for a specific set of source types (Admin Tools ->
	 * Generate Cache lets the admin pick pages/posts/files/... individually).
	 *
	 * @param array<int, string> $types
	 * @return array<string, array{processed:int, stored:int, skipped:int, removed:int}>
	 */
	public function generate( array $types ): array {
		$batch   = (int) $this->settings->get( 'reader', 'batch_size', 50 );
		$results = array();

		foreach ( $types as $type ) {
			$reader = $this->readers->find( $type );
			$total  = $reader ? $reader->count() : 0;

			$results[ $type ] = $this->builder->build( $type, $batch );
			$this->logger->info( Logger::CHANNEL_CACHE, "Generated cache for \"{$type}\".", $results[ $type ] );
			$this->readerService->recordRun( $type, $total, $results[ $type ]['processed'], 'completed' );
		}

		return $results;
	}

	/**
	 * Generate cache for every available reader ("Everything").
	 *
	 * @return array<string, array{processed:int, stored:int, skipped:int, removed:int}>
	 */
	public function generateAll(): array {
		return $this->generate( array_map(
			static fn( $reader ) => $reader->type(),
			$this->readers->available()
		) );
	}

	public function destroy( ?string $type = null ): int {
		$count = $this->storage->purge( $type );
		$this->logger->info( Logger::CHANNEL_CACHE, 'Destroyed cache.', array( 'type' => $type, 'count' => $count ) );

		return $count;
	}

	/**
	 * @return array<string, array{processed:int, stored:int, skipped:int, removed:int}>
	 */
	public function rebuildAll(): array {
		$this->destroy();

		return $this->generateAll();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function stats(): array {
		return $this->storage->stats();
	}
}
