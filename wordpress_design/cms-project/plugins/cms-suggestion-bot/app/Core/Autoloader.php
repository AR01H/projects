<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Zero-dependency PSR-4 autoloader.
 *
 * Used as a fallback when the plugin is deployed without `composer install`
 * (e.g. copied straight into wp-content/plugins). Maps the CmsSuggestionBot\
 * namespace root onto the /app directory, one sub-namespace level per folder.
 */
final class Autoloader {

	private static string $prefix   = '';
	private static string $base_dir = '';

	public static function register( string $namespace_prefix, string $base_dir ): void {
		self::$prefix   = rtrim( $namespace_prefix, '\\' ) . '\\';
		self::$base_dir = rtrim( $base_dir, '/\\' ) . '/';

		spl_autoload_register( array( self::class, 'load' ) );
	}

	public static function load( string $class ): void {
		if ( 0 !== strncmp( self::$prefix, $class, strlen( self::$prefix ) ) ) {
			return; // Not our namespace.
		}

		$relative = substr( $class, strlen( self::$prefix ) );
		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
		$file     = self::$base_dir . $relative . '.php';

		if ( is_file( $file ) ) {
			require $file;
		}
	}
}
