<?php
namespace VintageSoul\Services;

defined( 'ABSPATH' ) || exit;

final class RoutePageService {

	private const HASH_OPTION = 'vintagesoul_routes_hash';

	public static function sync(): void {
		$routes = RouteService::all();
		$hash   = md5( (string) wp_json_encode( $routes ) );

		if ( get_option( self::HASH_OPTION ) === $hash ) {
			return;
		}

		foreach ( $routes as $key => $route ) {
			$path = (string) ( $route['path'] ?? '' );
			$slug = trim( $path, '/' );

			if ( '' === $slug ) {
				continue;
			}

			self::ensure_page( $key, $slug );
		}

		update_option( self::HASH_OPTION, $hash );
	}

	private static function ensure_page( string $key, string $slug ): void {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing instanceof \WP_Post ) {
			return;
		}

		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => self::title_from_key( $key ),
				'post_name'    => $slug,
				'post_content' => '',
			)
		);
	}

	private static function title_from_key( string $key ): string {
		return ucwords( str_replace( array( '-', '_' ), ' ', $key ) );
	}
}
