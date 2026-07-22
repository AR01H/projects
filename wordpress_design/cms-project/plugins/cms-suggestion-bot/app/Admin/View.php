<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Tiny render helper so admin page templates share markup (stat cards,
 * tables, notices) instead of each one hand-rolling its own HTML. Every
 * admin template should render repeated UI through here rather than copying
 * markup between templates/admin/*.php files.
 */
final class View {

	private function __construct() {}

	/**
	 * Includes templates/admin/partials/{name}.php with $vars extracted into
	 * scope - mirrors how Admin\Pages\* classes pass data to their own templates.
	 *
	 * @param array<string, mixed> $vars
	 */
	public static function partial( string $name, array $vars = array() ): void {
		$file = CSB_PLUGIN_DIR . '/templates/admin/partials/' . $name . '.php';
		if ( ! is_file( $file ) ) {
			return;
		}

		extract( $vars ); // phpcs:ignore WordPress.PHP.DontExtract
		include $file;
	}

	/**
	 * @param array<int, array{title:string, value:string, sub?:string}> $cards
	 */
	public static function cardGrid( array $cards ): void {
		self::partial( 'card-grid', array( 'cards' => $cards ) );
	}

	/**
	 * @param array<int, string>              $headers
	 * @param array<int, array<int, string>>  $rows
	 */
	public static function table( array $headers, array $rows, string $empty_text = '' ): void {
		self::partial( 'table', array( 'headers' => $headers, 'rows' => $rows, 'empty_text' => $empty_text ) );
	}

	public static function notice( string $message, string $type = 'success' ): void {
		if ( '' === $message ) {
			return;
		}
		self::partial( 'notice', array( 'message' => $message, 'type' => $type ) );
	}
}
