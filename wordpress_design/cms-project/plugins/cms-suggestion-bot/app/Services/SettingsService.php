<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Repositories\SettingsRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Reads/writes Configuration values. Every group (general, behaviour, cache,
 * api, reader, common_questions, restricted_words, ai_approach, usage_limits)
 * is stored as one JSON row in cms_sug_bot_settings, keyed by group name, and
 * always merged on top of config/defaults.php so a missing or newly-added
 * field never comes back null.
 */
final class SettingsService {

	/** @var array<string, mixed>|null */
	private ?array $cache = null;

	public function __construct( private readonly SettingsRepository $repository ) {}

	/**
	 * @return array<string, mixed> The full settings tree.
	 */
	public function all(): array {
		if ( null !== $this->cache ) {
			return $this->cache;
		}

		$defaults = require CSB_PLUGIN_DIR . '/config/defaults.php';
		$stored   = $this->repository->allValues();

		$merged = $defaults;
		foreach ( $stored as $group => $json ) {
			$decoded = json_decode( $json, true );
			if ( is_array( $decoded ) && isset( $merged[ $group ] ) ) {
				$merged[ $group ] = array_replace_recursive( $merged[ $group ], $decoded );
			}
		}

		$this->cache = $merged;

		return $merged;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function group( string $group ): array {
		$all = $this->all();

		return isset( $all[ $group ] ) && is_array( $all[ $group ] ) ? $all[ $group ] : array();
	}

	public function get( string $group, string $key, mixed $default = null ): mixed {
		$values = $this->group( $group );

		return array_key_exists( $key, $values ) ? $values[ $key ] : $default;
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function save( string $group, array $values ): void {
		$this->repository->setValue( $group, (string) wp_json_encode( $values ) );
		$this->cache = null; // Invalidate in-request cache.
	}
}
