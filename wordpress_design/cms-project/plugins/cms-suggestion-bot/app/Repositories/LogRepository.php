<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_logs - written to only by Logger\Logger.
 */
final class LogRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'logs';
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function recent( int $limit = 100, string $channel = '' ): array {
		if ( '' !== $channel ) {
			$rows = $this->db()->get_results(
				$this->db()->prepare(
					"SELECT * FROM {$this->table()} WHERE channel = %s ORDER BY id DESC LIMIT %d",
					$channel,
					$limit
				),
				ARRAY_A
			);
		} else {
			$rows = $this->db()->get_results(
				$this->db()->prepare( "SELECT * FROM {$this->table()} ORDER BY id DESC LIMIT %d", $limit ),
				ARRAY_A
			);
		}

		return is_array( $rows ) ? $rows : array();
	}

	public function purgeOlderThan( int $days ): int {
		return (int) $this->db()->query(
			$this->db()->prepare(
				"DELETE FROM {$this->table()} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);
	}
}
