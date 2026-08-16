<?php
namespace VintageSoul\Services;

defined( 'ABSPATH' ) || exit;

final class AssetService {

	private const HANDLE_PREFIX = 'vintagesoul-';

	public static function enqueue(): void {
		$config = require VINTAGESOUL_DIR . '/config/assets.php';

		foreach ( (array) ( $config['css'] ?? array() ) as $handle => $path ) {
			self::enqueue_style( (string) $handle, (string) $path );
		}
		foreach ( (array) ( $config['js'] ?? array() ) as $handle => $path ) {
			self::enqueue_script( (string) $handle, (string) $path );
		}
	}

	public static function enqueue_page_assets(): void {
		$key = RouteService::current_key();
		if ( ! $key ) {
			return;
		}

		self::enqueue_style( 'page-' . $key, "assets/css/pages/{$key}.css" );
		self::enqueue_script( 'page-' . $key, "assets/js/pages/{$key}.js" );

		foreach ( RouteService::styles( $key ) as $i => $path ) {
			self::enqueue_style( "page-{$key}-extra-{$i}", (string) $path );
		}
		foreach ( RouteService::scripts( $key ) as $i => $path ) {
			self::enqueue_script( "page-{$key}-extra-{$i}", (string) $path );
		}
	}

	private static function enqueue_style( string $handle, string $path ): void {
		if ( 0 === strpos( $path, 'https://' ) || 0 === strpos( $path, 'http://' ) ) {
			wp_enqueue_style( self::HANDLE_PREFIX . $handle, $path, array(), null );
			return;
		}

		$file = VINTAGESOUL_DIR . '/' . ltrim( $path, '/' );
		if ( ! is_file( $file ) ) {
			return;
		}
		wp_enqueue_style(
			self::HANDLE_PREFIX . $handle,
			VINTAGESOUL_URI . '/' . ltrim( $path, '/' ),
			array(),
			(string) filemtime( $file )
		);
	}

	private static function enqueue_script( string $handle, string $path ): void {
		$file = VINTAGESOUL_DIR . '/' . ltrim( $path, '/' );
		if ( ! is_file( $file ) ) {
			return;
		}
		wp_enqueue_script(
			self::HANDLE_PREFIX . $handle,
			VINTAGESOUL_URI . '/' . ltrim( $path, '/' ),
			array(),
			(string) filemtime( $file ),
			array( 'in_footer' => true )
		);
	}
}
