<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_knowledge - manually curated + auto-learned question/answer
 * pairs, including the cached "common questions" (see Services\CommonQuestionsService).
 */
final class KnowledgeRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'knowledge';
	}

	public function truncate(): int {
		$count = (int) $this->db()->get_var( "SELECT COUNT(*) FROM {$this->table()}" );
		$this->db()->query( "TRUNCATE TABLE {$this->table()}" );

		return $count;
	}

	public function incrementUsage( int $id ): void {
		$this->db()->query(
			$this->db()->prepare( "UPDATE {$this->table()} SET usage_count = usage_count + 1 WHERE id = %d", $id )
		);
	}

	/**
	 * Records a question Bot\AnswerResolver couldn't answer, so an admin can
	 * review Knowledge Base -> Unanswered and fill in a real answer later
	 * (see the project's "Show me unpublished FAQs" use case). Reuses an
	 * existing unanswered row for the same question instead of duplicating it.
	 */
	public function logUnanswered( string $question ): void {
		$question = trim( $question );
		if ( '' === $question ) {
			return;
		}

		$existing = $this->db()->get_var(
			$this->db()->prepare(
				"SELECT id FROM {$this->table()} WHERE status = 'unanswered' AND question = %s LIMIT 1",
				$question
			)
		);

		if ( $existing ) {
			$this->incrementUsage( (int) $existing );
			return;
		}

		$this->insert( array(
			'question'    => $question,
			'answer'      => '',
			'source'      => 'auto',
			'status'      => 'unanswered',
			'usage_count' => 1,
			'created_at'  => current_time( 'mysql' ),
			'updated_at'  => current_time( 'mysql' ),
		) );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function byStatus( string $status, int $limit = 50, int $offset = 0 ): array {
		return $this->all( array( 'status' => $status ), $limit, $offset );
	}

	/**
	 * FULLTEXT search (qa_idx on question+keywords) with relevance ranking -
	 * a single indexed query instead of a LIKE '%...%' table scan, so lookups
	 * stay fast as the knowledge base grows. Falls back to LIKE only when the
	 * term is too short for MySQL's FULLTEXT minimum word length.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function search( string $term, int $limit = 20 ): array {
		$term = trim( $term );
		if ( '' === $term ) {
			return array();
		}

		if ( mb_strlen( $term ) >= 3 ) {
			$rows = $this->db()->get_results(
				$this->db()->prepare(
					"SELECT *, MATCH(question, keywords) AGAINST (%s IN NATURAL LANGUAGE MODE) AS relevance
					 FROM {$this->table()}
					 WHERE MATCH(question, keywords) AGAINST (%s IN NATURAL LANGUAGE MODE)
					 ORDER BY relevance DESC, priority DESC, usage_count DESC
					 LIMIT %d",
					$term,
					$term,
					$limit
				),
				ARRAY_A
			);
			if ( ! empty( $rows ) ) {
				return $rows;
			}
		}

		// Short terms / no FULLTEXT hits - fall back to a plain substring match.
		$like = '%' . $this->db()->esc_like( $term ) . '%';
		$rows = $this->db()->get_results(
			$this->db()->prepare(
				"SELECT * FROM {$this->table()} WHERE question LIKE %s OR keywords LIKE %s ORDER BY priority DESC, usage_count DESC LIMIT %d",
				$like,
				$like,
				$limit
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function topByUsage( int $limit = 20 ): array {
		$rows = $this->db()->get_results(
			$this->db()->prepare( "SELECT * FROM {$this->table()} ORDER BY usage_count DESC LIMIT %d", $limit ),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}
}
