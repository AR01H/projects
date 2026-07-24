<?php

namespace Adn\Theme\Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Placeholder Resolver — replaces {placeholders} in JSON values
 * with values from industry.json.
 *
 * e.g., "Your {domain} Journey" → "Your Property Journey"
 */
class PlaceholderResolver {

	/** @var array<string, string> Industry terms for replacement. */
	private array $terms = [];

	/**
	 * @param array $industry  The industry.json data.
	 */
	public function __construct( array $industry ) {
		// Build replacement map: {key} => value
		foreach ( $industry as $key => $value ) {
			if ( is_string( $value ) ) {
				$this->terms[ '{' . $key . '}' ] = $value;
			}
		}
	}

	/**
	 * Resolve all placeholders in a value.
	 * Handles strings, arrays, and nested structures.
	 */
	public function resolve( mixed $value ): mixed {
		if ( is_string( $value ) ) {
			return str_replace(
				array_keys( $this->terms ),
				array_values( $this->terms ),
				$value
			);
		}

		if ( is_array( $value ) ) {
			return array_map( [ $this, 'resolve' ], $value );
		}

		return $value;
	}

	/**
	 * Resolve all placeholders in an entire data structure.
	 */
	public function resolveAll( array $data ): array {
		return $this->resolve( $data );
	}
}
