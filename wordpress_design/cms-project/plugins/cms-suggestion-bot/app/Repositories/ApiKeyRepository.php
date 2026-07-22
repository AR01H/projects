<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_api_keys - keys issued for the future public API (app/API).
 */
final class ApiKeyRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'api_keys';
	}

	public function findByKey( string $api_key ): ?array {
		$row = $this->db()->get_row(
			$this->db()->prepare( "SELECT * FROM {$this->table()} WHERE api_key = %s AND is_active = 1", $api_key ),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	public function touchLastUsed( int $id ): void {
		$this->update( $id, array( 'last_used_at' => current_time( 'mysql' ) ) );
	}
}
