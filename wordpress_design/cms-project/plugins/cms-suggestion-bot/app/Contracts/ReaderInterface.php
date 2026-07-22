<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * A Reader turns one WordPress content type (pages, posts, products, ...)
 * into a flat list of normalized arrays the CacheBuilder can hash and store.
 * Each Reader is single-purpose: it only knows how to read its own source.
 */
interface ReaderInterface {

	/**
	 * Machine-readable identifier for this content type, e.g. "page", "post".
	 * Used as the `source_type` column in cms_sug_bot_cache.
	 */
	public function type(): string;

	/**
	 * Human-readable label shown in the admin Reader/Cache screens.
	 */
	public function label(): string;

	/**
	 * Whether this reader has anything to read on the current site
	 * (e.g. a "product" reader returns false when WooCommerce is inactive).
	 */
	public function isAvailable(): bool;

	/**
	 * Total number of records this reader would read - used to size progress
	 * bars / batches without reading full content up front.
	 */
	public function count(): int;

	/**
	 * Read one batch of records.
	 *
	 * @return array<int, array<string, mixed>> Normalized records - see
	 *         Models\CacheEntry for the expected shape of each array.
	 */
	public function read( int $offset, int $limit ): array;
}
