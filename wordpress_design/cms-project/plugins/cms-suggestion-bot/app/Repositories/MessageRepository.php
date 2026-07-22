<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_messages - individual turns within a conversation.
 */
final class MessageRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'messages';
	}

	public function add( int $conversation_id, string $role, string $message, ?string $matched_source = null ): int {
		return $this->insert( array(
			'conversation_id' => $conversation_id,
			'role'            => $role,
			'message'         => $message,
			'matched_source'  => $matched_source,
			'created_at'      => current_time( 'mysql' ),
		) );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function forConversation( int $conversation_id, int $limit = 20 ): array {
		$rows = $this->db()->get_results(
			$this->db()->prepare(
				"SELECT * FROM {$this->table()} WHERE conversation_id = %d ORDER BY id ASC LIMIT %d",
				$conversation_id,
				$limit
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}
}
