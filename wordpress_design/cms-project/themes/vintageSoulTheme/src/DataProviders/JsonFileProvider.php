<?php
namespace VintageSoul\DataProviders;

defined( 'ABSPATH' ) || exit;

final class JsonFileProvider {

	private static array $cache = array();

	public static function read( string $relative_path ): array {
		if ( array_key_exists( $relative_path, self::$cache ) ) {
			return self::$cache[ $relative_path ] ?? array();
		}

		$base = realpath( VINTAGESOUL_DIR );
		$file = realpath( VINTAGESOUL_DIR . '/' . ltrim( $relative_path, '/' ) );

		if ( ! $base || ! $file || 0 !== strpos( $file, $base ) || ! is_file( $file ) ) {
			self::$cache[ $relative_path ] = array();
			return array();
		}

		$contents = file_get_contents( $file );
		$decoded  = is_string( $contents ) ? json_decode( $contents, true ) : null;
		$result   = is_array( $decoded ) ? $decoded : array();

		self::$cache[ $relative_path ] = $result;
		return $result;
	}
}
