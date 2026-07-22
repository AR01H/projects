<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_queue - individual units of work belonging to a Job, processed
 * in bounded batches so large sites never regenerate everything in one request.
 */
final class QueueRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'queue';
	}

	public function push( int $job_id, string $task_type, array $payload = array() ): int {
		return $this->insert( array(
			'job_id'     => $job_id,
			'task_type'  => $task_type,
			'payload'    => wp_json_encode( $payload ),
			'status'     => 'pending',
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		) );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function nextBatch( int $limit = 50 ): array {
		$rows = $this->db()->get_results(
			$this->db()->prepare(
				"SELECT * FROM {$this->table()} WHERE status = 'pending' ORDER BY id ASC LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	public function markDone( int $id ): void {
		$this->update( $id, array( 'status' => 'done', 'updated_at' => current_time( 'mysql' ) ) );
	}

	public function markFailed( int $id ): void {
		$this->db()->query(
			$this->db()->prepare(
				"UPDATE {$this->table()} SET status = 'failed', attempts = attempts + 1, updated_at = %s WHERE id = %d",
				current_time( 'mysql' ),
				$id
			)
		);
	}
}
