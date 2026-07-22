<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Minimal service container: lazy singleton resolution via factory closures.
 *
 * Not a full DI framework by design - the plugin favours explicit factory
 * bindings (registered once in Plugin::registerServices()) over autowiring,
 * so the object graph stays easy to read and predictable for future edits.
 */
final class Container {

	/** @var array<string, callable> */
	private array $factories = array();

	/** @var array<string, mixed> */
	private array $instances = array();

	public function bind( string $id, callable $factory ): void {
		$this->factories[ $id ] = $factory;
		unset( $this->instances[ $id ] );
	}

	public function get( string $id ): mixed {
		if ( array_key_exists( $id, $this->instances ) ) {
			return $this->instances[ $id ];
		}

		if ( ! isset( $this->factories[ $id ] ) ) {
			throw new \RuntimeException( sprintf( 'Container: no binding registered for "%s".', $id ) );
		}

		$instance                = ( $this->factories[ $id ] )( $this );
		$this->instances[ $id ] = $instance;

		return $instance;
	}

	public function has( string $id ): bool {
		return isset( $this->factories[ $id ] );
	}
}
