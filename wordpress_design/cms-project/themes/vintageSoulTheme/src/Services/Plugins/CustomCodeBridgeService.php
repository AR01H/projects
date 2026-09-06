<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\Services\RouteService;

defined( 'ABSPATH' ) || exit;

/**
 * CustomCodeBridgeService — Bridges CMS Custom Code Manager (admin.php?page=ah-custom-code)
 * with the theme head and footer rendering pipeline.
 *
 * Supports:
 * - Global CSS (loads in <head>)
 * - Global JS (loads in <footer>)
 * - Per-page CSS (loads in <head> for matched page slug)
 * - Per-page JS (loads in <footer> for matched page slug)
 */
class CustomCodeBridgeService {

	private static array $slug_cache = array();
	private static bool $global_css_injected = false;
	private static bool $global_js_injected = false;
	private static bool $slug_css_injected = false;
	private static bool $slug_js_injected = false;

	/**
	 * Register theme hooks for custom code injection.
	 */
	public static function register_hooks(): void {
		add_action( 'wp_head', array( static::class, 'inject_head_code' ), 999 );
		add_action( 'wp_footer', array( static::class, 'inject_footer_code' ), 999 );
	}

	/**
	 * Inject Global & Per-Page CSS into <head>.
	 */
	public static function inject_head_code(): void {
		if ( is_admin() ) {
			return;
		}

		// 1. Inject Global CSS
		if ( ! self::$global_css_injected && self::is_global_active() ) {
			$global_css = self::get_global_css();
			if ( '' !== $global_css ) {
				echo "\n<!-- AH CMS Custom Code: Global CSS -->\n";
				echo "<style id=\"ah-global-styles\">\n" . $global_css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				self::$global_css_injected = true;
			}
		}

		// 2. Inject Per-Page CSS
		if ( ! self::$slug_css_injected ) {
			$slug = self::resolve_current_slug();
			if ( '' !== $slug ) {
				$rule = self::get_per_page_rule( $slug );
				if ( $rule && ! empty( $rule->is_active ) ) {
					$css = trim( (string) ( $rule->css ?? '' ) );
					if ( '' !== $css ) {
						echo "\n<!-- AH CMS Custom Code: Per-Page CSS for [{$slug}] -->\n";
						echo "<style id=\"ah-custom-css-" . esc_attr( $slug ) . "\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						self::$slug_css_injected = true;
					}
				}
			}
		}
	}

	/**
	 * Inject Global & Per-Page JS into <footer>.
	 */
	public static function inject_footer_code(): void {
		if ( is_admin() ) {
			return;
		}

		// 1. Inject Global JS
		if ( ! self::$global_js_injected && self::is_global_active() ) {
			$global_js = self::get_global_js();
			if ( '' !== $global_js ) {
				echo "\n<!-- AH CMS Custom Code: Global JS -->\n";
				echo "<script id=\"ah-global-scripts\">\n" . $global_js . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				self::$global_js_injected = true;
			}
		}

		// 2. Inject Per-Page JS
		if ( ! self::$slug_js_injected ) {
			$slug = self::resolve_current_slug();
			if ( '' !== $slug ) {
				$rule = self::get_per_page_rule( $slug );
				if ( $rule && ! empty( $rule->is_active ) ) {
					$js = trim( (string) ( $rule->js ?? '' ) );
					if ( '' !== $js ) {
						echo "\n<!-- AH CMS Custom Code: Per-Page JS for [{$slug}] -->\n";
						echo "<script id=\"ah-custom-js-" . esc_attr( $slug ) . "\">\n" . $js . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						self::$slug_js_injected = true;
					}
				}
			}
		}
	}

	/**
	 * Check if Global CSS / JS injection is active.
	 *
	 * @return bool
	 */
	public static function is_global_active(): bool {
		return (bool) get_option( 'ah_global_styles_active', 0 );
	}

	/**
	 * Get Global CSS from option table.
	 *
	 * @return string
	 */
	public static function get_global_css(): string {
		return trim( (string) get_option( 'ah_global_styles_css', '' ) );
	}

	/**
	 * Get Global JS from option table.
	 *
	 * @return string
	 */
	public static function get_global_js(): string {
		return trim( (string) get_option( 'ah_global_styles_js', '' ) );
	}

	/**
	 * Get per-page rule by slug with request-level caching.
	 *
	 * @param string $slug
	 * @return object|null
	 */
	public static function get_per_page_rule( string $slug ): ?object {
		$clean_slug = strtolower( preg_replace( '/[^a-z0-9_\-]/', '', sanitize_title( $slug ) ) );
		if ( '' === $clean_slug ) {
			return null;
		}

		if ( array_key_exists( $clean_slug, self::$slug_cache ) ) {
			return self::$slug_cache[ $clean_slug ];
		}

		$row = null;

		if ( isset( $GLOBALS['wpdb'] ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ah_custom_code';
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $table_exists ) {
				$row = $wpdb->get_row( $wpdb->prepare(
					"SELECT * FROM `{$table}` WHERE slug = %s AND is_active = 1 LIMIT 1",
					$clean_slug
				) );
			}
		}

		self::$slug_cache[ $clean_slug ] = $row ? (object) $row : null;
		return self::$slug_cache[ $clean_slug ];
	}

	/**
	 * Resolve current page slug cleanly.
	 *
	 * @return string
	 */
	public static function resolve_current_slug(): string {
		if ( is_front_page() || is_home() ) {
			return 'home';
		}

		$key = RouteService::key_for_current_page();
		if ( $key ) {
			return $key;
		}

		if ( is_singular() ) {
			$slug = (string) get_post_field( 'post_name', get_the_ID() );
			if ( '' !== $slug ) {
				return $slug;
			}
		}

		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		$path    = trim( (string) wp_parse_url( $req_uri, PHP_URL_PATH ), '/' );
		$segs    = explode( '/', $path );

		return ! empty( $segs[0] ) ? sanitize_title( $segs[0] ) : 'home';
	}
}
