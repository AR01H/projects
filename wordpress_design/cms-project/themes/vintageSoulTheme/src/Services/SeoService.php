<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class SeoService {

	public static function meta_description( string $override = '' ): string {
		if ( '' !== trim( $override ) ) {
			return $override;
		}
		$config = JsonFileProvider::read( 'config/seo.json' );
		return (string) ( $config['default_meta_description'] ?? '' );
	}

	public static function title_separator(): string {
		$config = JsonFileProvider::read( 'config/seo.json' );
		return (string) ( $config['title_separator'] ?? '-' );
	}

	public static function og_default_image(): string {
		$config = JsonFileProvider::read( 'config/seo.json' );
		return (string) ( $config['og_default_image'] ?? '' );
	}

	public static function render_meta_description(): void {
		$description = self::meta_description( is_singular() ? get_the_excerpt() : '' );
		if ( '' === $description ) {
			return;
		}
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $description ) ) );
	}

	public static function filter_canonical_url( string $canonical_url, \WP_Post $post ): string {
		if ( 'page' !== $post->post_type ) {
			return $canonical_url;
		}
		$key = RouteService::key_for_slug( $post->post_name );
		if ( ! $key ) {
			return $canonical_url;
		}
		$primary = RouteService::url( $key );
		return '' !== $primary ? $primary : $canonical_url;
	}
}
