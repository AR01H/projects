<?php
namespace VintageSoul\Services;

use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

final class RouteService {

	private static ?array $routes = null;

	private static function routes(): array {
		if ( null === self::$routes ) {
			self::$routes = require VINTAGESOUL_DIR . '/config/routes.php';
		}
		return self::$routes;
	}

	private static function route( string $key ): array {
		return (array) ( self::routes()[ $key ] ?? array() );
	}

	public static function all(): array {
		return self::routes();
	}

	public static function path( string $key ): string {
		return (string) ( self::route( $key )['path'] ?? '' );
	}

	public static function url( string $key ): string {
		$path = self::path( $key );
		return '' !== $path ? UrlHelper::resolve( $path ) : '';
	}

	public static function alternates( string $key ): array {
		return (array) ( self::route( $key )['alternates'] ?? array() );
	}

	public static function styles( string $key ): array {
		return (array) ( self::route( $key )['styles'] ?? array() );
	}

	public static function scripts( string $key ): array {
		return (array) ( self::route( $key )['scripts'] ?? array() );
	}

	private static function route_matches_slug( array $route, string $slug ): bool {
		$path = trim( (string) ( $route['path'] ?? '' ), '/' );
		if ( '' !== $path && $path === $slug ) {
			return true;
		}
		foreach ( (array) ( $route['alternates'] ?? array() ) as $alternate ) {
			if ( trim( (string) $alternate, '/' ) === $slug ) {
				return true;
			}
		}
		return false;
	}

	public static function key_for_slug( string $slug ): ?string {
		if ( '' === $slug ) {
			return null;
		}
		foreach ( self::routes() as $key => $route ) {
			if ( self::route_matches_slug( $route, $slug ) ) {
				return $key;
			}
		}
		return null;
	}

	public static function key_for_current_page(): ?string {

		$current_slug = get_post_field( 'post_name', get_queried_object_id() );
		return $current_slug ? self::key_for_slug( $current_slug ) : null;
	}

	public static function current_key(): ?string {
		if ( is_front_page() ) {
			return 'home';
		}
		return self::key_for_current_page();
	}

	public static function add_body_class( array $classes ): array {
		$key = self::current_key();
		if ( $key ) {
			$classes[] = 'page-' . sanitize_html_class( $key );
		}
		return $classes;
	}
}
