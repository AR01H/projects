<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_conversations - one row per chat session (see Bot\BotEngine
 * conversation memory).
 */
final class ConversationRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'conversations';
	}

	public function findBySession( string $session_id ): ?array {
		$row = $this->db()->get_row(
			$this->db()->prepare( "SELECT * FROM {$this->table()} WHERE session_id = %s", $session_id ),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	public function findOrCreate( string $session_id, ?int $user_id = null ): array {
		$existing = $this->findBySession( $session_id );
		if ( $existing ) {
			return $existing;
		}

		$id = $this->insert( array(
			'session_id' => $session_id,
			'user_id'    => $user_id,
			'started_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		) );

		return (array) $this->find( $id );
	}

	public function touch( int $id ): void {
		$this->update( $id, array( 'updated_at' => current_time( 'mysql' ) ) );
	}
}
