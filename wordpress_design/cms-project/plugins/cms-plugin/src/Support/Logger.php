<?php

namespace Ah\Cms\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Structured logger for the CMS plugin.
 * Logs to file and optionally to WP error log.
 */
class Logger {

	private static string $logDir = '';

	/**
	 * Initialize the logger.
	 */
	public static function init(): void {
		self::$logDir = WP_CONTENT_DIR . '/logs/cms-plugin';
		if ( ! is_dir( self::$logDir ) ) {
			wp_mkdir_p( self::$logDir );
		}
	}

	/**
	 * Log an info message.
	 */
	public static function info( string $message, array $context = [] ): void {
		self::log( 'info', $message, $context );
	}

	/**
	 * Log a warning message.
	 */
	public static function warning( string $message, array $context = [] ): void {
		self::log( 'warning', $message, $context );
	}

	/**
	 * Log an error message.
	 */
	public static function error( string $message, array $context = [] ): void {
		self::log( 'error', $message, $context );
	}

	/**
	 * Log a debug message.
	 */
	public static function debug( string $message, array $context = [] ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}
		self::log( 'debug', $message, $context );
	}

	/**
	 * Write a log entry.
	 */
	private static function log( string $level, string $message, array $context ): void {
		if ( empty( self::$logDir ) ) {
			self::init();
		}

		$timestamp = current_time( 'mysql' );
		$line = "[{$timestamp}] [{$level}] {$message}";

		if ( $context ) {
			$line .= ' ' . wp_json_encode( $context );
		}

		$line .= "\n";

		$file = self::$logDir . '/' . gmdate( 'Y-m-d' ) . '.log';
		@file_put_contents( $file, $line, FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		// Also log to WP error log for errors
		if ( 'error' === $level ) {
			error_log( 'AH_CMS: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
