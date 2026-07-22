<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Cache;

use CmsSuggestionBot\Contracts\CacheStorageInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Single responsibility: decide whether a freshly-read record's content
 * hash matches what's already stored, so CacheBuilder can skip unchanged
 * records instead of rewriting them on every regeneration.
 */
final class ChunkHasher {

	/**
	 * @param array<string, mixed> $record
	 */
	public function unchanged( CacheStorageInterface $storage, array $record ): bool {
		$existing = $storage->existingHash( (string) $record['source_type'], (int) $record['source_id'] );

		return null !== $existing && '' !== $existing && $existing === (string) ( $record['content_hash'] ?? '' );
	}
}
