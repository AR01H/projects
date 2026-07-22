<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Readers;

use CmsSuggestionBot\Contracts\ReaderInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Registry of every ReaderInterface implementation. Adding a new content
 * type (products, custom post types, ...) later means writing one new
 * Reader class and adding it to Core\Plugin::registerServices() - nothing
 * else in the pipeline needs to change.
 */
final class ReaderManager {

	/** @var array<int, ReaderInterface> */
	private array $readers;

	/**
	 * @param array<int, ReaderInterface> $readers
	 */
	public function __construct( array $readers ) {
		$this->readers = $readers;
	}

	/**
	 * @return array<int, ReaderInterface> Only readers whose content type exists on this site.
	 */
	public function available(): array {
		return array_values( array_filter( $this->readers, static fn( ReaderInterface $r ) => $r->isAvailable() ) );
	}

	public function find( string $type ): ?ReaderInterface {
		foreach ( $this->readers as $reader ) {
			if ( $reader->type() === $type ) {
				return $reader;
			}
		}

		return null;
	}

	/**
	 * @return array<int, ReaderInterface>
	 */
	public function all(): array {
		return $this->readers;
	}
}
