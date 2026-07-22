<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Logger;

use CmsSuggestionBot\Repositories\LogRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Single write path for every log line the plugin produces (reader, cache,
 * api, errors, warnings, performance, cron, manual actions - see the
 * project's "Logging" requirement). Bound as a shared instance in
 * Core\Plugin::registerServices() so every Service/Controller logs through
 * the same object.
 */
final class Logger {

	public const CHANNEL_READER      = 'reader';
	public const CHANNEL_CACHE       = 'cache';
	public const CHANNEL_API         = 'api';
	public const CHANNEL_ERROR       = 'errors';
	public const CHANNEL_WARNING     = 'warnings';
	public const CHANNEL_PERFORMANCE = 'performance';
	public const CHANNEL_CRON        = 'cron';
	public const CHANNEL_MANUAL      = 'manual';

	public function __construct( private readonly LogRepository $repository ) {}

	/**
	 * @param array<string, mixed> $context
	 */
	public function log( string $channel, string $level, string $message, array $context = array() ): void {
		$this->repository->insert( array(
			'channel'    => $channel,
			'level'      => $level,
			'message'    => $message,
			'context'    => empty( $context ) ? null : wp_json_encode( $context ),
			'created_at' => current_time( 'mysql' ),
		) );
	}

	public function info( string $channel, string $message, array $context = array() ): void {
		$this->log( $channel, 'info', $message, $context );
	}

	public function warning( string $channel, string $message, array $context = array() ): void {
		$this->log( $channel, 'warning', $message, $context );
	}

	public function error( string $channel, string $message, array $context = array() ): void {
		$this->log( $channel, 'error', $message, $context );
	}
}
