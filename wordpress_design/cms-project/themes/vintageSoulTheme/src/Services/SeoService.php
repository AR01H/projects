<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * SeoService — reads from data/content/seo.json.
 *
 * JSON structure:
 *   { "global": { ... }, "pages": { "home": { "title": "", "description": "", ... } } }
 *
 * Usage:
 *   SeoService::page_title('events');          // page-specific title
 *   SeoService::page_description('events');    // page-specific description
 *   SeoService::render_page_seo('events');     // output all meta tags at once
 */
final class SeoService {

	/** @var array<string,mixed>|null */
	private static ?array $data = null;

	private static function data(): array {
		if ( null === self::$data ) {
			$d = JsonFileProvider::read( 'data/content/seo.json' );
			self::$data = is_array( $d ) ? $d : array();
		}
		return self::$data;
	}

	private static function global( string $key, string $default = '' ): string {
		$g = self::data()['global'] ?? array();
		return (string) ( $g[ $key ] ?? $default );
	}

	private static function page( string $page_key, string $field, string $default = '' ): string {
		$p = self::data()['pages'][ $page_key ] ?? array();
		return (string) ( $p[ $field ] ?? $default );
	}

	/** Detect current page key from WordPress context */
	public static function current_page_key(): string {
		if ( is_front_page() || is_home() ) {
			return 'home';
		}
		global $post;
		if ( $post instanceof \WP_Post ) {
			$slug = $post->post_name;
			// Map slug directly to page key
			$known = array( 'about', 'history', 'events', 'franchise', 'contact', 'blog' );
			if ( in_array( $slug, $known, true ) ) {
				return $slug;
			}
		}
		if ( is_404() ) {
			return '404';
		}
		return 'home';
	}

	/** Get page title (falls back to global site name) */
	public static function page_title( string $page_key = '' ): string {
		$key   = '' !== $page_key ? $page_key : self::current_page_key();
		$title = self::page( $key, 'title' );
		if ( '' !== $title ) {
			return $title;
		}
		return self::global( 'site_name', get_bloginfo( 'name' ) );
	}

	/** Get page description */
	public static function page_description( string $page_key = '' ): string {
		$key  = '' !== $page_key ? $page_key : self::current_page_key();
		$desc = self::page( $key, 'description' );
		if ( '' !== $desc ) {
			return $desc;
		}
		return self::global( 'default_description' );
	}

	/** Get page keywords */
	public static function page_keywords( string $page_key = '' ): string {
		$key = '' !== $page_key ? $page_key : self::current_page_key();
		return self::page( $key, 'keywords' );
	}

	/** Get OG title */
	public static function og_title( string $page_key = '' ): string {
		$key = '' !== $page_key ? $page_key : self::current_page_key();
		$og  = self::page( $key, 'og_title' );
		return '' !== $og ? $og : self::page_title( $key );
	}

	/** Get OG description */
	public static function og_description( string $page_key = '' ): string {
		$key = '' !== $page_key ? $page_key : self::current_page_key();
		$og  = self::page( $key, 'og_description' );
		return '' !== $og ? $og : self::page_description( $key );
	}

	/** Get OG image URL */
	public static function og_image_url(): string {
		$img = self::global( 'og_image' );
		return '' !== $img ? UrlHelper::resolve( $img ) : '';
	}

	/** Render all canonical SEO meta tags for current (or specified) page */
	public static function render_page_seo( string $page_key = '' ): void {
		$key         = '' !== $page_key ? $page_key : self::current_page_key();
		$title       = self::page_title( $key );
		$description = self::page_description( $key );
		$keywords    = self::page_keywords( $key );
		$og_title    = self::og_title( $key );
		$og_desc     = self::og_description( $key );
		$og_image    = self::og_image_url();
		$site_name   = self::global( 'site_name', get_bloginfo( 'name' ) );
		$locale      = self::global( 'locale', 'en_GB' );
		$twitter     = self::global( 'twitter_handle' );
		$page_url    = esc_url( home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) ) );

		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
		if ( '' !== $keywords ) {
			echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '">' . "\n";
		}
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
		echo '<meta property="og:url" content="' . $page_url . '">' . "\n";
		if ( '' !== $og_image ) {
			echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
		}
		if ( '' !== $twitter ) {
			echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
			echo '<meta name="twitter:site" content="' . esc_attr( $twitter ) . '">' . "\n";
			echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";
			echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
			if ( '' !== $og_image ) {
				echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
			}
		}
	}

	// ───── Legacy helpers (kept for backwards compatibility) ──────────────────

	public static function meta_description( string $override = '' ): string {
		if ( '' !== trim( $override ) ) {
			return $override;
		}
		return self::page_description();
	}

	public static function title_separator(): string {
		return self::global( 'separator', ' | ' );
	}

	public static function og_default_image(): string {
		return self::og_image_url();
	}

	public static function render_meta_description(): void {
		$description = self::page_description();
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

