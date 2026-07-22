<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Where CacheBuilder output is persisted. The default implementation writes
 * to the cms_sug_bot_cache / cms_sug_bot_chunks tables, but swapping this
 * binding in Core\Plugin::registerServices() is enough to redirect the whole
 * cache pipeline elsewhere (e.g. object cache, disk, a remote store) without
 * touching Reader or CacheBuilder code.
 */
interface CacheStorageInterface {

	/**
	 * @param array<string, mixed> $entry Normalized record, see Models\CacheEntry.
	 */
	public function put( array $entry ): void;

	public function forget( string $source_type, int $source_id ): void;

	/**
	 * @return array<string, mixed>|null
	 */
	public function get( string $source_type, int $source_id ): ?array;

	public function existingHash( string $source_type, int $source_id ): ?string;

	/**
	 * All source_ids currently cached for a type - used to detect and clean
	 * up entries whose underlying content has since been deleted.
	 *
	 * @return array<int, int>
	 */
	public function sourceIds( string $source_type ): array;

	public function purge( ?string $source_type = null ): int;

	public function stats(): array;
}
