<?php
namespace VintageSoul\Support;

defined( 'ABSPATH' ) || exit;

final class View {

	public static function component( string $name, array $data = array() ): void {
		self::include_file( VINTAGESOUL_DIR . '/components/' . $name . '.php', VINTAGESOUL_DIR . '/components', $data );
	}

	public static function part( string $name, array $data = array() ): void {
		self::include_file( VINTAGESOUL_DIR . '/template-parts/' . $name . '.php', VINTAGESOUL_DIR . '/template-parts', $data );
	}

	private static function include_file( string $path, string $allowed_base, array $data ): void {
		$base = realpath( $allowed_base );
		$file = realpath( $path );

		if ( ! $base || ! $file || 0 !== strpos( $file, $base ) || ! is_file( $file ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[VintageSoul] View file not found: ' . $path );
			}
			return;
		}

		extract( $data, EXTR_SKIP );
		require $file;
	}
}
