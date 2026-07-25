<?php
namespace Adn\Theme\Shared;

defined( 'ABSPATH' ) || exit;

/**
 * PaginationBuilder - Shared pagination builders.
 *
 * Standardizes pagination array structure across all pages.
 */
class PaginationBuilder {

	/**
	 * Build a pagination array.
	 *
	 * @param int    $current  Current page number (1-based).
	 * @param int    $total    Total number of pages.
	 * @param string $base_url Base URL for pagination links.
	 */
	public static function build( int $current, int $total, string $base_url = '' ): array {
		return array(
			'current'  => max( 1, $current ),
			'total'    => max( 1, $total ),
			'base_url' => $base_url,
		);
	}

	/**
	 * Single-page pagination (no actual pagination needed).
	 */
	public static function single(): array {
		return self::build( 1, 1 );
	}

	/**
	 * Resolve the current page number from request.
	 *
	 * @return int Page number (minimum 1).
	 */
	public static function currentPage(): int {
		return max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Build cache key segment for pagination.
	 */
	public static function cacheSegment( int $paged ): string {
		return '_p' . $paged;
	}
}
