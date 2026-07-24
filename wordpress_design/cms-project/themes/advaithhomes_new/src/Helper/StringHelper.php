<?php
namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * String Helper
 *
 * Utility functions for string manipulation, slug generation, and text processing.
 *
 * @package Adn\Theme\Helper
 */

class StringHelper {

	/**
	 * Generate a URL-friendly slug from a string.
	 */
	public static function slug( string $text ): string {
		return sanitize_title( $text );
	}

	/**
	 * Truncate a string to a maximum length, preserving word boundaries.
	 */
	public static function truncate( string $text, int $length = 100, string $suffix = '…' ): string {
		if ( mb_strlen( $text ) <= $length ) {
			return $text;
		}
		$truncated = mb_substr( $text, 0, $length );
		$lastSpace = mb_strrpos( $truncated, ' ' );
		if ( $lastSpace !== false ) {
			$truncated = mb_substr( $truncated, 0, $lastSpace );
		}
		return $truncated . $suffix;
	}

	/**
	 * Strip all HTML tags from a string.
	 */
	public static function stripTags( string $text ): string {
		return wp_strip_all_tags( $text );
	}

	/**
	 * Convert a string to title case.
	 */
	public static function titleCase( string $text ): string {
		return ucwords( strtolower( $text ) );
	}

	/**
	 * Convert a string to snake_case.
	 */
	public static function snakeCase( string $text ): string {
		$text = preg_replace( '/[^a-zA-Z0-9]+/', '_', $text );
		$text = strtolower( trim( $text, '_' ) );
		return $text;
	}

	/**
	 * Convert a string to camelCase.
	 */
	public static function camelCase( string $text ): string {
		$text = self::snakeCase( $text );
		$words = explode( '_', $text );
		$camel = array_shift( $words );
		foreach ( $words as $word ) {
			$camel .= ucfirst( $word );
		}
		return $camel;
	}

	/**
	 * Convert a string to PascalCase.
	 */
	public static function pascalCase( string $text ): string {
		return ucfirst( self::camelCase( $text ) );
	}

	/**
	 * Convert a string to kebab-case.
	 */
	public static function kebabCase( string $text ): string {
		return str_replace( '_', '-', self::snakeCase( $text ) );
	}

	/**
	 * Generate a random string of specified length.
	 */
	public static function random( int $length = 16 ): string {
		return substr( bin2hex( random_bytes( (int) ceil( $length / 2 ) ) ), 0, $length );
	}

	/**
	 * Check if a string contains a substring (case-insensitive).
	 */
	public static function contains( string $haystack, string $needle ): bool {
		return mb_stripos( $haystack, $needle ) !== false;
	}

	/**
	 * Replace multiple occurrences of search strings in order.
	 */
	public static function multiReplace( string $text, array $replacements ): string {
		return str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$text
		);
	}
}
