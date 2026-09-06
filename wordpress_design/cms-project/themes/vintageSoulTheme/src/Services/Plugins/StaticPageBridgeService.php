<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * StaticPageBridgeService — Bridges CMS Static HTML Pages Manager (admin.php?page=ah-static-pages)
 * with the theme template, routing, and shortcode system.
 */
class StaticPageBridgeService {

	private static array $cache = array();

	/**
	 * Register theme hooks for static pages.
	 */
	public static function register_hooks(): void {
		if ( ! shortcode_exists( 'ah_static_page' ) ) {
			add_shortcode( 'ah_static_page', array( static::class, 'shortcode_handler' ) );
		}
	}

	/**
	 * Get the raw HTML content for a static page by slug.
	 *
	 * 1. AH_Static_Pages_Model (CMS Plugin database model)
	 * 2. Direct database query on table {prefix}ah_static_pages
	 *
	 * @param string $slug
	 * @return string
	 */
	public static function get_html( string $slug ): string {
		$clean_slug = strtolower( preg_replace( '/[^a-z0-9_\-]/', '', sanitize_title( $slug ) ) );
		if ( '' === $clean_slug ) {
			return '';
		}

		if ( array_key_exists( $clean_slug, self::$cache ) ) {
			return self::$cache[ $clean_slug ];
		}

		$html = '';

		// 1. Primary Source: CMS Plugin AH_Static_Pages_Model
		if ( class_exists( '\AH_Static_Pages_Model' ) ) {
			$model = new \AH_Static_Pages_Model();
			$html  = (string) $model->get_html( $clean_slug );
		}

		// 2. Direct DB Query Fallback
		if ( '' === $html && isset( $GLOBALS['wpdb'] ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ah_static_pages';
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $table_exists ) {
				$db_html = $wpdb->get_var( $wpdb->prepare( "SELECT html FROM `{$table}` WHERE slug = %s AND status = 'active' LIMIT 1", $clean_slug ) );
				if ( $db_html ) {
					$html = (string) $db_html;
				}
			}
		}

		self::$cache[ $clean_slug ] = $html;
		return $html;
	}

	/**
	 * Get full static page record (id, slug, title, html, page_id) by slug.
	 *
	 * @param string $slug
	 * @return object|null
	 */
	public static function get_page( string $slug ): ?object {
		$clean_slug = strtolower( preg_replace( '/[^a-z0-9_\-]/', '', sanitize_title( $slug ) ) );
		if ( '' === $clean_slug ) {
			return null;
		}

		if ( class_exists( '\AH_Static_Pages_Model' ) ) {
			$model = new \AH_Static_Pages_Model();
			$row   = $model->get_by_slug( $clean_slug );
			if ( $row ) {
				return (object) $row;
			}
		}

		if ( isset( $GLOBALS['wpdb'] ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ah_static_pages';
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $table_exists ) {
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE slug = %s LIMIT 1", $clean_slug ) );
				if ( $row ) {
					return (object) $row;
				}
			}
		}

		return null;
	}

	/**
	 * Check if a static page exists in the database.
	 *
	 * @param string $slug
	 * @return bool
	 */
	public static function exists( string $slug ): bool {
		return '' !== self::get_html( $slug );
	}

	/**
	 * Retrieve all static pages.
	 *
	 * @return array<int, object>
	 */
	public static function all(): array {
		if ( class_exists( '\AH_Static_Pages_Model' ) ) {
			return ( new \AH_Static_Pages_Model() )->all();
		}

		if ( isset( $GLOBALS['wpdb'] ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ah_static_pages';
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $table_exists ) {
				return $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY slug ASC" ) ?: array();
			}
		}

		return array();
	}

	/**
	 * Shortcode handler: [ah_static_page slug="..."]
	 *
	 * @param array<string, mixed> $atts
	 * @return string
	 */
	public static function shortcode_handler( array $atts = array() ): string {
		$atts = shortcode_atts( array( 'slug' => '' ), $atts, 'ah_static_page' );
		$slug = (string) $atts['slug'];
		if ( '' === $slug ) {
			return '';
		}

		return self::get_html( $slug );
	}
}
