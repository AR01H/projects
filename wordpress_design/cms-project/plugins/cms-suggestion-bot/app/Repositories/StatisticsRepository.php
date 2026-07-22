<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_statistics - point-in-time metric snapshots shown on the Dashboard.
 */
final class StatisticsRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'statistics';
	}

	public function record( string $key, mixed $value ): void {
		$this->insert( array(
			'stat_key'    => $key,
			'stat_value'  => is_scalar( $value ) ? (string) $value : wp_json_encode( $value ),
			'recorded_at' => current_time( 'mysql' ),
		) );
	}

	public function latest( string $key ): ?string {
		$value = $this->db()->get_var(
			$this->db()->prepare(
				"SELECT stat_value FROM {$this->table()} WHERE stat_key = %s ORDER BY id DESC LIMIT 1",
				$key
			)
		);

		return null === $value ? null : (string) $value;
	}
}
