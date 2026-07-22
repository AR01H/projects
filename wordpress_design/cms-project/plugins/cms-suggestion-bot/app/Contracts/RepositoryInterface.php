<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Baseline CRUD contract every Repository implements. Repositories are the
 * only classes allowed to run raw SQL against $wpdb - Services and
 * Controllers must go through one of these instead.
 */
interface RepositoryInterface {

	/** @return array<string, mixed>|null */
	public function find( int $id ): ?array;

	/**
	 * @param array<string, mixed> $where
	 * @return array<int, array<string, mixed>>
	 */
	public function all( array $where = array(), int $limit = 0, int $offset = 0 ): array;

	/**
	 * @param array<string, mixed> $data
	 * @return int Insert ID.
	 */
	public function insert( array $data ): int;

	/**
	 * @param array<string, mixed> $data
	 */
	public function update( int $id, array $data ): bool;

	public function delete( int $id ): bool;

	/**
	 * @param array<string, mixed> $where
	 */
	public function count( array $where = array() ): int;
}
