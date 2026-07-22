<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

use CmsSuggestionBot\Services\SettingsService;

defined( 'ABSPATH' ) || exit;

/**
 * Configuration submenu - one form per settings group (General, Behaviour,
 * Cache, API, Reader, Common Questions, Restricted Words, AI Approach,
 * Usage Limits), each posting straight back to SettingsService::save().
 * This class only prepares/saves data; templates/admin/configuration.php
 * renders the markup.
 */
final class ConfigurationPage {

	public function __construct( private readonly SettingsService $settings ) {}

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		$notice = $this->maybeSave();

		$settings = $this->settings->all();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';

		include CSB_PLUGIN_DIR . '/templates/admin/configuration.php';
	}

	private function maybeSave(): string {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['csb_config_group'] ) ) {
			return '';
		}

		check_admin_referer( 'csb_save_configuration' );

		$group = sanitize_key( wp_unslash( $_POST['csb_config_group'] ) );
		$raw   = isset( $_POST['csb'] ) ? (array) wp_unslash( $_POST['csb'] ) : array();

		$this->settings->save( $group, $this->sanitizeGroup( $group, $raw ) );

		return __( 'Settings saved.', 'cms-suggestion-bot' );
	}

	/**
	 * @param array<string, mixed> $raw
	 * @return array<string, mixed>
	 */
	private function sanitizeGroup( string $group, array $raw ): array {
		return match ( $group ) {
			'restricted_words' => array(
				'enabled' => ! empty( $raw['enabled'] ),
				'mode'    => in_array( $raw['mode'] ?? '', array( 'mask', 'block' ), true ) ? $raw['mode'] : 'mask',
				'words'   => array_values( array_filter( array_map(
					'sanitize_text_field',
					preg_split( '/\r\n|\r|\n/', (string) ( $raw['words_text'] ?? '' ) ) ?: array()
				) ) ),
			),
			'ai_approach' => array(
				'enabled'         => ! empty( $raw['enabled'] ),
				'active_provider' => sanitize_key( (string) ( $raw['active_provider'] ?? '' ) ),
				'providers'       => $this->sanitizeProviders( (array) ( $raw['providers'] ?? array() ) ),
			),
			'usage_limits' => array(
				'enabled'                  => ! empty( $raw['enabled'] ),
				'max_messages_per_session' => max( 1, (int) ( $raw['max_messages_per_session'] ?? 20 ) ),
				'max_messages_per_day'     => max( 1, (int) ( $raw['max_messages_per_day'] ?? 50 ) ),
				'limit_reached_message'    => sanitize_text_field( (string) ( $raw['limit_reached_message'] ?? '' ) ),
			),
			'common_questions' => array(
				'enabled'     => ! empty( $raw['enabled'] ),
				'cache_ttl'   => max( 60, (int) ( $raw['cache_ttl'] ?? DAY_IN_SECONDS ) ),
				'max_entries' => max( 1, (int) ( $raw['max_entries'] ?? 200 ) ),
			),
			'greetings' => array(
				'enabled' => ! empty( $raw['enabled'] ),
				'phrases' => $this->sanitizeGreetingPhrases( (array) ( $raw['phrases'] ?? array() ) ),
			),
			default => array_map(
				static fn( $v ) => is_array( $v ) ? array_map( 'sanitize_text_field', $v ) : ( is_bool( $v ) ? $v : sanitize_text_field( (string) $v ) ),
				$raw
			),
		};
	}

	/**
	 * Only updates responses for phrases that already exist (from
	 * config/greetings.php or a prior save) - this form edits responses,
	 * it doesn't add/remove trigger phrases.
	 *
	 * @param array<string, string> $raw
	 * @return array<string, string>
	 */
	private function sanitizeGreetingPhrases( array $raw ): array {
		$existing = $this->settings->get( 'greetings', 'phrases', array() );
		$out      = is_array( $existing ) ? $existing : array();

		foreach ( $out as $phrase => $response ) {
			if ( array_key_exists( $phrase, $raw ) ) {
				$out[ $phrase ] = sanitize_text_field( (string) $raw[ $phrase ] );
			}
		}

		return $out;
	}

	/**
	 * @param array<string, array<string, mixed>> $raw
	 * @return array<string, array<string, mixed>>
	 */
	private function sanitizeProviders( array $raw ): array {
		$existing = $this->settings->get( 'ai_approach', 'providers', array() );
		$out      = is_array( $existing ) ? $existing : array();

		foreach ( $raw as $id => $fields ) {
			$id = sanitize_key( (string) $id );
			if ( ! isset( $out[ $id ] ) || ! is_array( $fields ) ) {
				continue;
			}
			$out[ $id ]['api_key'] = sanitize_text_field( (string) ( $fields['api_key'] ?? '' ) );
			$out[ $id ]['model']   = sanitize_text_field( (string) ( $fields['model'] ?? '' ) );
			if ( isset( $fields['endpoint'] ) ) {
				$out[ $id ]['endpoint'] = esc_url_raw( (string) $fields['endpoint'] );
			}
		}

		return $out;
	}
}
