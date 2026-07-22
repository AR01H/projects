<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Readers\ReaderManager;
use CmsSuggestionBot\Repositories\ReaderRunRepository;
use CmsSuggestionBot\Logger\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Admin-facing entry point for the Reader screen: what content types are
 * available, and a log of past scan runs. Actual scanning/caching itself is
 * CacheService's job (Reader + CacheBuilder together are one pipeline) -
 * this service exists so the Reader admin page doesn't need to know about
 * CacheBuilder internals, just "what can be read" and "what was read".
 */
final class ReaderService {

	public function __construct(
		private readonly ReaderManager $readers,
		private readonly ReaderRunRepository $runs,
		private readonly Logger $logger,
	) {}

	/**
	 * @return array<int, array{type:string, label:string, available:bool, count:int}>
	 */
	public function sources(): array {
		return array_map(
			static fn( $reader ) => array(
				'type'      => $reader->type(),
				'label'     => $reader->label(),
				'available' => $reader->isAvailable(),
				'count'     => $reader->isAvailable() ? $reader->count() : 0,
			),
			$this->readers->all()
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function recentRuns( int $limit = 20 ): array {
		return $this->runs->latest( $limit );
	}

	public function recordRun( string $type, int $total, int $processed, string $status, string $message = '' ): void {
		$id = $this->runs->start( $type, $total );
		$this->runs->progress( $id, $processed );
		$this->runs->finish( $id, $status, $message );
		$this->logger->info( Logger::CHANNEL_READER, "Reader run for \"{$type}\": {$status}.", array(
			'total'     => $total,
			'processed' => $processed,
		) );
	}
}
