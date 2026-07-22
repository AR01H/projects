<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

use CmsSuggestionBot\Contracts\CacheStorageInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Default CacheStorageInterface implementation - persists CacheBuilder
 * output to cms_sug_bot_cache (see Database\Schema::cache()).
 */
final class CacheRepository extends AbstractRepository implements CacheStorageInterface {

	public function __construct( private readonly ChunkRepository $chunks ) {}

	protected function tableSuffix(): string {
		return 'cache';
	}

	public function put( array $entry ): void {
		$meta = isset( $entry['meta'] ) ? wp_json_encode( $entry['meta'] ) : null;

		$row = array(
			'source_type'  => (string) $entry['source_type'],
			'source_id'    => (int) $entry['source_id'],
			'title'        => (string) ( $entry['title'] ?? '' ),
			'slug'         => (string) ( $entry['slug'] ?? '' ),
			'url'          => (string) ( $entry['url'] ?? '' ),
			'excerpt'      => (string) ( $entry['excerpt'] ?? '' ),
			'content_hash' => (string) ( $entry['content_hash'] ?? '' ),
			'word_count'   => (int) ( $entry['word_count'] ?? 0 ),
			'meta'         => $meta,
			'status'       => (string) ( $entry['status'] ?? 'active' ),
			'updated_at'   => current_time( 'mysql' ),
		);

		$existing = $this->findBySource( (string) $entry['source_type'], (int) $entry['source_id'] );

		if ( $existing ) {
			$this->update( (int) $existing['id'], $row );
			$cache_id = (int) $existing['id'];
		} else {
			$row['created_at'] = current_time( 'mysql' );
			$cache_id          = $this->insert( $row );
		}

		$this->chunks->replaceForCache( $cache_id, (string) ( $entry['content'] ?? '' ) );
	}

	public function forget( string $source_type, int $source_id ): void {
		$existing = $this->findBySource( $source_type, $source_id );
		if ( ! $existing ) {
			return;
		}

		$this->chunks->deleteForCache( (int) $existing['id'] );
		$this->delete( (int) $existing['id'] );
	}

	public function get( string $source_type, int $source_id ): ?array {
		return $this->findBySource( $source_type, $source_id );
	}

	public function existingHash( string $source_type, int $source_id ): ?string {
		$row = $this->findBySource( $source_type, $source_id );

		return $row ? (string) $row['content_hash'] : null;
	}

	public function sourceIds( string $source_type ): array {
		$ids = $this->db()->get_col(
			$this->db()->prepare( "SELECT source_id FROM {$this->table()} WHERE source_type = %s", $source_type )
		);

		return array_map( 'intval', (array) $ids );
	}

	public function purge( ?string $source_type = null ): int {
		if ( null === $source_type ) {
			$count = (int) $this->db()->get_var( "SELECT COUNT(*) FROM {$this->table()}" );
			$this->db()->query( "TRUNCATE TABLE {$this->table()}" );
			$this->chunks->truncate();

			return $count;
		}

		$ids = $this->db()->get_col(
			$this->db()->prepare( "SELECT id FROM {$this->table()} WHERE source_type = %s", $source_type )
		);
		foreach ( $ids as $id ) {
			$this->chunks->deleteForCache( (int) $id );
		}

		return (int) $this->db()->query(
			$this->db()->prepare( "DELETE FROM {$this->table()} WHERE source_type = %s", $source_type )
		);
	}

	public function stats(): array {
		$rows = $this->db()->get_results(
			"SELECT source_type, COUNT(*) AS total, SUM(word_count) AS words FROM {$this->table()} GROUP BY source_type",
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	private function findBySource( string $source_type, int $source_id ): ?array {
		$row = $this->db()->get_row(
			$this->db()->prepare(
				"SELECT * FROM {$this->table()} WHERE source_type = %s AND source_id = %d",
				$source_type,
				$source_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}
}
