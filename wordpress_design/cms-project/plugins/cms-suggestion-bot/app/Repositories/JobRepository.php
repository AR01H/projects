<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_jobs - one row per admin/cron-triggered job (Generate Cache,
 * Rebuild Cache, ...). See Repositories\QueueRepository for its sub-tasks.
 */
final class JobRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'jobs';
	}

	public function start( string $job_type, array $payload = array() ): int {
		return $this->insert( array(
			'job_type'   => $job_type,
			'status'     => 'running',
			'payload'    => wp_json_encode( $payload ),
			'started_at' => current_time( 'mysql' ),
			'created_at' => current_time( 'mysql' ),
		) );
	}

	public function finish( int $id, string $status, array $result = array() ): void {
		$this->update( $id, array(
			'status'      => $status,
			'result'      => wp_json_encode( $result ),
			'finished_at' => current_time( 'mysql' ),
		) );
	}
}
