<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Cache;

use CmsSuggestionBot\Contracts\CacheStorageInterface;
use CmsSuggestionBot\Readers\ReaderManager;

defined( 'ABSPATH' ) || exit;

/**
 * Orchestrates the Reader -> hash check -> CacheStorage pipeline described in
 * the project's "Cache System" requirement:
 *
 *   WordPress -> Reader -> CacheBuilder -> Knowledge Store -> Bot Answers
 *
 * Processes one content type in bounded batches, skips unchanged records
 * (ChunkHasher), and removes cache entries whose source no longer exists
 * (deleted-content cleanup).
 */
final class CacheBuilder {

	public function __construct(
		private readonly ReaderManager $readers,
		private readonly CacheStorageInterface $storage,
		private readonly ChunkHasher $hasher,
	) {}

	/**
	 * @return array{processed:int, stored:int, skipped:int, removed:int}
	 */
	public function build( string $type, int $batchSize = 50 ): array {
		$reader = $this->readers->find( $type );
		if ( ! $reader || ! $reader->isAvailable() ) {
			return array( 'processed' => 0, 'stored' => 0, 'skipped' => 0, 'removed' => 0 );
		}

		$total     = $reader->count();
		$processed = 0;
		$stored    = 0;
		$skipped   = 0;
		$seen      = array();

		for ( $offset = 0; $offset < $total; $offset += $batchSize ) {
			foreach ( $reader->read( $offset, $batchSize ) as $record ) {
				$seen[] = (int) $record['source_id'];
				++$processed;

				if ( $this->hasher->unchanged( $this->storage, $record ) ) {
					++$skipped;
					continue;
				}

				$this->storage->put( $record );
				++$stored;
			}
		}

		$removed = $this->cleanupDeleted( $type, $seen );

		return array(
			'processed' => $processed,
			'stored'    => $stored,
			'skipped'   => $skipped,
			'removed'   => $removed,
		);
	}

	/**
	 * @return array<string, array{processed:int, stored:int, skipped:int, removed:int}>
	 */
	public function buildAll( int $batchSize = 50 ): array {
		$results = array();
		foreach ( $this->readers->available() as $reader ) {
			$results[ $reader->type() ] = $this->build( $reader->type(), $batchSize );
		}

		return $results;
	}

	/**
	 * Removes cached entries of $type whose source_id wasn't seen in the
	 * latest read pass (i.e. the underlying page/post/file was deleted).
	 *
	 * @param array<int, int> $seenIds
	 */
	private function cleanupDeleted( string $type, array $seenIds ): int {
		$existingIds = $this->storage->sourceIds( $type );
		$stale       = array_diff( $existingIds, $seenIds );

		foreach ( $stale as $source_id ) {
			$this->storage->forget( $type, (int) $source_id );
		}

		return count( $stale );
	}
}
