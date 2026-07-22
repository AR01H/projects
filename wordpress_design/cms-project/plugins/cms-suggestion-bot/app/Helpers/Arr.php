<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Pure array utility methods only - no WordPress calls, no side effects.
 */
final class Arr {

	private function __construct() {}

	public static function get( array $array, string $key, mixed $default = null ): mixed {
		return array_key_exists( $key, $array ) ? $array[ $key ] : $default;
	}

	/**
	 * @param array<int, array<string, mixed>> $rows
	 * @return array<int, mixed>
	 */
	public static function pluck( array $rows, string $key ): array {
		return array_map( static fn( array $row ) => self::get( $row, $key ), $rows );
	}

	/**
	 * Split an array into fixed-size batches - used by Readers/CacheBuilder
	 * so large sites are processed in bounded chunks instead of all at once.
	 *
	 * @return array<int, array<int, mixed>>
	 */
	public static function chunk( array $items, int $size ): array {
		return $size > 0 ? array_chunk( $items, $size ) : array( $items );
	}
}
