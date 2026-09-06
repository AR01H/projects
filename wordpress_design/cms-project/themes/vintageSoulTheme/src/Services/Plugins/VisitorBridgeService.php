<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\Services\RouteService;

defined( 'ABSPATH' ) || exit;

/**
 * VisitorBridgeService — Tracks frontend visits, pageviews, and visitor metrics
 * and records them into the CMS Plugin Visitor Logs table ({prefix}ah_visitor_logs),
 * powering the real-time analytics at admin.php?page=ah-visitors.
 */
class VisitorBridgeService {

	private static bool $recorded = false;

	/**
	 * Register hooks to automatically track frontend page visits.
	 */
	public static function register_hooks(): void {
		add_action( 'template_redirect', array( static::class, 'track_visit' ), 10 );
	}

	/**
	 * Track current page request and persist into database.
	 */
	public static function track_visit(): void {
		// Only track frontend GET requests (not admin, cron, ajax, api, or post actions)
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( self::$recorded ) {
			return;
		}

		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$lower_ua = strtolower( $ua );

		// Skip common crawlers and bots
		foreach ( array( 'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'lighthouse', 'headless' ) as $sig ) {
			if ( false !== strpos( $lower_ua, $sig ) ) {
				return;
			}
		}

		$ip = self::get_client_ip();
		$slug = self::resolve_current_slug();
		$url  = self::get_current_url();
		$ref  = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

		// Get or set a visitor session ID
		$session_id = self::get_session_id( $ip, $ua );

		self::record( array(
			'ip_address' => $ip,
			'page_url'   => $url,
			'page_slug'  => $slug,
			'referrer'   => $ref,
			'user_agent' => $ua,
			'session_id' => $session_id,
		) );

		self::$recorded = true;
	}

	/**
	 * Record a visit into {prefix}ah_visitor_logs.
	 * Deduplicates: same session_id + page_slug within the last 5 minutes is skipped.
	 *
	 * @param array<string, mixed> $data
	 * @return bool
	 */
	public static function record( array $data ): bool {
		global $wpdb;

		// 1. If CMS Plugin model exists, use it
		if ( class_exists( '\AH_Visitor_Model' ) ) {
			$model = new \AH_Visitor_Model();
			return (bool) $model->record( $data );
		}

		if ( ! isset( $GLOBALS['wpdb'] ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'ah_visitor_logs';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( ! $table_exists ) {
			return false;
		}

		$session = sanitize_text_field( (string) ( $data['session_id'] ?? '' ) );
		$slug    = sanitize_text_field( (string) ( $data['page_slug'] ?? '' ) );
		$ip      = sanitize_text_field( (string) ( $data['ip_address'] ?? '' ) );
		$url     = esc_url_raw( (string) ( $data['page_url'] ?? '' ) );
		$ref     = esc_url_raw( (string) ( $data['referrer'] ?? '' ) );
		$ua      = sanitize_text_field( (string) ( $data['user_agent'] ?? '' ) );

		// Dedup: check if same session + slug visited within 5 minutes
		if ( '' !== $session && '' !== $slug ) {
			$recent = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM `{$table}` WHERE session_id = %s AND page_slug = %s AND visited_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) LIMIT 1",
				$session,
				$slug
			) );
			if ( $recent ) {
				return false;
			}
		}

		$inserted = $wpdb->insert(
			$table,
			array(
				'ip_address' => substr( $ip, 0, 45 ),
				'page_url'   => substr( $url, 0, 700 ),
				'page_slug'  => substr( $slug, 0, 300 ),
				'referrer'   => substr( $ref, 0, 700 ),
				'user_agent' => substr( $ua, 0, 500 ),
				'session_id' => substr( $session, 0, 64 ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (bool) $inserted;
	}

	/**
	 * Resolve client IP address with proxy / cloudflare headers support.
	 *
	 * @return string
	 */
	public static function get_client_ip(): string {
		$keys = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CLIENT_IP',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
				$ip = trim( $ip_list[0] );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '127.0.0.1';
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

		return '' !== $path ? $path : 'home';
	}

	/**
	 * Get current full canonical URL.
	 *
	 * @return string
	 */
	public static function get_current_url(): string {
		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		return home_url( $req_uri );
	}

	/**
	 * Generate or read a consistent visitor session token.
	 *
	 * @param string $ip
	 * @param string $ua
	 * @return string
	 */
	public static function get_session_id( string $ip, string $ua ): string {
		$cookie_name = 'vst_vid';
		if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
			$vid = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
			if ( preg_match( '/^[a-f0-9]{32,64}$/i', $vid ) ) {
				return $vid;
			}
		}

		$daily_hash = md5( $ip . '|' . $ua . '|' . gmdate( 'Y-m-d' ) );

		if ( ! headers_sent() && function_exists( 'setcookie' ) ) {
			@setcookie( $cookie_name, $daily_hash, time() + ( 86400 * 30 ), '/' );
		}

		return $daily_hash;
	}

	/**
	 * Get total visits count from database.
	 *
	 * @return int
	 */
	public static function get_total_visits(): int {
		if ( class_exists( '\AH_Visitor_Model' ) ) {
			return ( new \AH_Visitor_Model() )->total();
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ah_visitor_logs';
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
	}

	/**
	 * Get total unique visitors count.
	 *
	 * @return int
	 */
	public static function get_unique_visitors(): int {
		if ( class_exists( '\AH_Visitor_Model' ) ) {
			return ( new \AH_Visitor_Model() )->total_unique();
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ah_visitor_logs';
		return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT ip_address) FROM `{$table}`" );
	}
}
