<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_reader - one row per Reader scan run, shown on the Reader admin page.
 */
final class ReaderRunRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'reader';
	}

	public function start( string $reader_type, int $total ): int {
		return $this->insert( array(
			'reader_type' => $reader_type,
			'status'      => 'running',
			'total'       => $total,
			'processed'   => 0,
			'started_at'  => current_time( 'mysql' ),
		) );
	}

	public function progress( int $id, int $processed ): void {
		$this->update( $id, array( 'processed' => $processed ) );
	}

	public function finish( int $id, string $status, string $message = '' ): void {
		$this->update( $id, array(
			'status'      => $status,
			'message'     => $message,
			'finished_at' => current_time( 'mysql' ),
		) );
	}

	public function latest( int $limit = 20 ): array {
		$rows = $this->db()->get_results(
			$this->db()->prepare( "SELECT * FROM {$this->table()} ORDER BY id DESC LIMIT %d", $limit ),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}
}
