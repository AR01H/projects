<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Repositories\KnowledgeRepository;

defined( 'ABSPATH' ) || exit;

/**
 * CRUD + search for the Knowledge Base admin page. An administrator can add
 * entries manually here; Bot\AnswerResolver reads through
 * CommonQuestionsService (a cached subset) rather than this service
 * directly, so admin edits and bot lookups stay independent.
 */
final class KnowledgeService {

	public function __construct( private readonly KnowledgeRepository $repository ) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all( int $limit = 50, int $offset = 0 ): array {
		return $this->repository->all( array(), $limit, $offset );
	}

	public function find( int $id ): ?array {
		return $this->repository->find( $id );
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function create( array $data ): int {
		return $this->repository->insert( $this->shape( $data ) + array(
			'usage_count' => 0,
			'created_at'  => current_time( 'mysql' ),
			'updated_at'  => current_time( 'mysql' ),
		) );
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function update( int $id, array $data ): bool {
		return $this->repository->update( $id, $this->shape( $data ) + array(
			'updated_at' => current_time( 'mysql' ),
		) );
	}

	public function delete( int $id ): bool {
		return $this->repository->delete( $id );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function search( string $term ): array {
		return $this->repository->search( $term );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function unanswered( int $limit = 50, int $offset = 0 ): array {
		return $this->repository->byStatus( 'unanswered', $limit, $offset );
	}

	public function countUnanswered(): int {
		return $this->repository->count( array( 'status' => 'unanswered' ) );
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	private function shape( array $data ): array {
		$answer = wp_kses_post( (string) ( $data['answer'] ?? '' ) );

		return array(
			'question' => sanitize_textarea_field( (string) ( $data['question'] ?? '' ) ),
			'answer'   => $answer,
			'category' => sanitize_text_field( (string) ( $data['category'] ?? '' ) ),
			'keywords' => sanitize_text_field( (string) ( $data['keywords'] ?? '' ) ),
			'priority' => (int) ( $data['priority'] ?? 0 ),
			'source'   => sanitize_key( (string) ( $data['source'] ?? 'manual' ) ),
			// An unanswered entry auto-publishes the moment an admin fills in
			// an answer for it - no separate "publish" step needed.
			'status'   => '' !== trim( $answer ) ? 'published' : 'unanswered',
		);
	}
}
