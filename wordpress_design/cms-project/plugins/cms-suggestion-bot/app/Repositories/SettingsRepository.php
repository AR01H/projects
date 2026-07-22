<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Repositories;

defined( 'ABSPATH' ) || exit;

/**
 * cms_sug_bot_settings - one row per top-level setting group (general,
 * behaviour, cache, api, reader, common_questions, restricted_words,
 * ai_approach). Consumed only by Services\SettingsService.
 */
final class SettingsRepository extends AbstractRepository {

	protected function tableSuffix(): string {
		return 'settings';
	}

	public function getValue( string $key ): ?string {
		$value = $this->db()->get_var(
			$this->db()->prepare( "SELECT setting_value FROM {$this->table()} WHERE setting_key = %s", $key )
		);

		return null === $value ? null : (string) $value;
	}

	public function setValue( string $key, string $value ): void {
		$existing = $this->db()->get_var(
			$this->db()->prepare( "SELECT id FROM {$this->table()} WHERE setting_key = %s", $key )
		);

		$row = array(
			'setting_key'   => $key,
			'setting_value' => $value,
			'updated_at'    => current_time( 'mysql' ),
		);

		if ( $existing ) {
			$this->update( (int) $existing, $row );
		} else {
			$this->insert( $row );
		}
	}

	/**
	 * @return array<string, string>
	 */
	public function allValues(): array {
		$rows = $this->db()->get_results( "SELECT setting_key, setting_value FROM {$this->table()}", ARRAY_A );
		$out  = array();
		foreach ( (array) $rows as $row ) {
			$out[ $row['setting_key'] ] = (string) $row['setting_value'];
		}

		return $out;
	}
}
