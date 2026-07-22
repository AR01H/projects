<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Applied only to the small number of classes that must have exactly one
 * instance for the lifetime of a request (the main Plugin class and the
 * Logger). Everything else is resolved through Core\Container instead.
 */
trait Singleton {

	private static ?self $instance = null;

	private function __construct() {}

	public static function instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __clone() {}

	public function __wakeup() {
		throw new \RuntimeException( 'Cannot unserialize a singleton.' );
	}
}
