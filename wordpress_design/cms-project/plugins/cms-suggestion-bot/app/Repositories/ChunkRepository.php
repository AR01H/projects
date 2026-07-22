<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

use CmsSuggestionBot\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_chunks - word-bounded slices of a cache entry's content.
 * Always accessed through CacheRepository, never directly.
 */
final class ChunkRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'chunks';
	}

	public function replaceForCache( int $cache_id, string $content, int $wordsPerChunk = 200 ): void {
		$this->deleteForCache( $cache_id );

		foreach ( Str::chunk( $content, $wordsPerChunk ) as $index => $chunk ) {
			$this->insert( array(
				'cache_id'    => $cache_id,
				'chunk_index' => $index,
				'content'     => $chunk,
				'hash'        => Str::hash( $chunk ),
				'created_at'  => current_time( 'mysql' ),
			) );
		}
	}

	public function deleteForCache( int $cache_id ): void {
		$this->db()->delete( $this->table(), array( 'cache_id' => $cache_id ) );
	}

	public function truncate(): void {
		$this->db()->query( "TRUNCATE TABLE {$this->table()}" );
	}

	/**
	 * FULLTEXT search against content_idx - Bot\AnswerResolver's primary
	 * lookup. One indexed MATCH AGAINST query, joined back to the parent
	 * cache row for title/url/excerpt, ranked by MySQL's own relevance score
	 * so this stays fast regardless of how much content has been cached.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function search( string $term, int $limit = 5 ): array {
		$term = trim( $term );
		if ( '' === $term || mb_strlen( $term ) < 3 ) {
			return array();
		}

		$cache_table = str_replace( 'chunks', 'cache', $this->table() );

		$rows = $this->db()->get_results(
			$this->db()->prepare(
				"SELECT c.*, ch.chunk_index, ch.content AS matched_chunk,
				        MATCH(ch.content) AGAINST (%s IN NATURAL LANGUAGE MODE) AS relevance
				 FROM {$this->table()} ch
				 INNER JOIN {$cache_table} c ON c.id = ch.cache_id
				 WHERE MATCH(ch.content) AGAINST (%s IN NATURAL LANGUAGE MODE)
				   AND c.status = 'active'
				 ORDER BY relevance DESC
				 LIMIT %d",
				$term,
				$term,
				$limit
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Find posts related to a given cache entry by category/tag overlap and
	 * content similarity. Used by Bot\AnswerResolver to return multiple
	 * related article suggestions instead of just the top match.
	 *
	 * @param int    $cache_id  The cache entry to find relatives for.
	 * @param int    $limit     Max related posts to return.
	 * @return array<int, array{id:int, title:string, url:string, excerpt:string, relevance:float}>
	 */
	public function findRelated( int $cache_id, int $limit = 5 ): array {
		global $wpdb;

		$cache_table = str_replace( 'chunks', 'cache', $this->table() );

		// Get the source entry's meta (categories, tags) for matching.
		$origin = $this->db()->get_row(
			$this->db()->prepare(
				"SELECT source_type, title, meta FROM {$cache_table} WHERE id = %d",
				$cache_id
			),
			ARRAY_A
		);

		if ( ! $origin ) {
			return array();
		}

		$meta       = json_decode( (string) ( $origin['meta'] ?? '{}' ), true );
		$categories = $meta['categories'] ?? array();
		$tags       = $meta['tags'] ?? array();
		$skip_type  = $origin['source_type'];

		$results  = array();
		$seen_ids = array( $cache_id );

		// Phase 1: Find cache entries sharing categories or tags via JSON LIKE.
		// For each term, search the meta column for the JSON-encoded string.
		$all_terms = array_merge( $categories, $tags );

		if ( ! empty( $all_terms ) ) {
			$all_terms = array_unique( $all_terms );

			foreach ( $all_terms as $term ) {
				$encoded = wp_json_encode( $term );
				$like    = '%' . $wpdb->esc_like( $encoded ) . '%';

				$rows = $this->db()->get_results(
					$this->db()->prepare(
						"SELECT id, title, url, excerpt, meta
						 FROM {$cache_table}
						 WHERE id NOT IN (" . implode( ',', array_fill( 0, count( $seen_ids ), '%d' ) ) . ")
						   AND source_type != %s
						   AND status = 'active'
						   AND meta LIKE %s
						 LIMIT %d",
						...array_merge( $seen_ids, [$skip_type, $like, $limit * 2] )
					),
					ARRAY_A
				);

				if ( is_array( $rows ) ) {
					foreach ( $rows as $row ) {
						$row_id = (int) $row['id'];
						if ( in_array( $row_id, $seen_ids, true ) ) {
							continue;
						}
						$seen_ids[] = $row_id;

						$row_meta = json_decode( (string) ( $row['meta'] ?? '{}' ), true );
						$row_cats = $row_meta['categories'] ?? array();
						$row_tags = $row_meta['tags'] ?? array();

						$cat_overlap = count( array_intersect( $categories, $row_cats ) );
						$tag_overlap = count( array_intersect( $tags, $row_tags ) );
						$score = ( $cat_overlap * 3 ) + ( $tag_overlap * 1 );

						$results[ $row_id ] = array(
							'id'        => $row_id,
							'title'     => (string) $row['title'],
							'url'       => (string) $row['url'],
							'excerpt'   => (string) $row['excerpt'],
							'relevance' => (float) $score,
						);
					}
				}

				if ( count( $results ) >= $limit ) {
					break;
				}
			}
		}

		// Phase 2: If we don't have enough matches, fill with FULLTEXT
		// content similarity on the origin's title.
		if ( count( $results ) < $limit && '' !== (string) ( $origin['title'] ?? '' ) ) {
			$remaining  = $limit - count( $results );
			$placeholders = implode( ',', array_fill( 0, count( $seen_ids ), '%d' ) );

			$rows = $this->db()->get_results(
				$this->db()->prepare(
					"SELECT c.id, c.title, c.url, c.excerpt,
					        MATCH(c.title, c.excerpt) AGAINST (%s IN NATURAL LANGUAGE MODE) AS relevance
					 FROM {$cache_table} c
					 WHERE MATCH(c.title, c.excerpt) AGAINST (%s IN NATURAL LANGUAGE MODE)
					   AND c.id NOT IN ($placeholders)
					   AND c.source_type != %s
					   AND c.status = 'active'
					 ORDER BY relevance DESC
					 LIMIT %d",
					...array_merge( [(string) $origin['title'], (string) $origin['title']], $seen_ids, [$skip_type, $remaining] )
				),
				ARRAY_A
			);

			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					$row_id = (int) $row['id'];
					if ( ! isset( $results[ $row_id ] ) ) {
						$results[ $row_id ] = array(
							'id'        => $row_id,
							'title'     => (string) $row['title'],
							'url'       => (string) $row['url'],
							'excerpt'   => (string) $row['excerpt'],
							'relevance' => (float) ( $row['relevance'] ?? 0 ),
						);
					}
				}
			}
		}

		// Sort by relevance descending and cap at $limit.
		usort( $results, static fn( array $a, array $b ) => $b['relevance'] <=> $a['relevance'] );

		return array_slice( $results, 0, $limit, true );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function forCache( int $cache_id ): array {
		$rows = $this->db()->get_results(
			$this->db()->prepare(
				"SELECT * FROM {$this->table()} WHERE cache_id = %d ORDER BY chunk_index ASC",
				$cache_id
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}
}
