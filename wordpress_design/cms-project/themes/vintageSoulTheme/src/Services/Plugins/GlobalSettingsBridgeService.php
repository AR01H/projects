<?php
namespace VintageSoul\Services\Plugins;

use DateTime;
use DateTimeZone;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

defined( 'ABSPATH' ) || exit;

/**
 * GlobalSettingsBridgeService — Bridges CMS Global Settings (admin.php?page=ah-global-settings)
 * with the theme runtime, providing Timezone synchronization, Image Optimization controls,
 * and high-performance Filesystem / Memory caching.
 */
class GlobalSettingsBridgeService {

	private static array $memory_cache = array();

	/**
	 * Register runtime hooks for Global Settings.
	 */
	public static function register_hooks(): void {
		// Image optimization filters
		add_filter( 'big_image_size_threshold', array( static::class, 'filter_big_image_threshold' ), 10, 1 );
		add_filter( 'image_editor_output_format', array( static::class, 'filter_image_output_format' ), 10, 1 );

		// Cache clear URL trigger for authenticated users
		add_action( 'init', array( static::class, 'handle_cache_clear_request' ), 1 );

		// Image cache-busting - only active when VINTAGESOUL_CACHE_VERSION is
		// defined (see functions.php); CSS/JS already auto-bust via filemtime()
		// in AssetService, so this only needs to cover attachment image URLs.
		if ( defined( 'VINTAGESOUL_CACHE_VERSION' ) ) {
			add_filter( 'wp_get_attachment_url', array( static::class, 'bust_attachment_url' ) );
			add_filter( 'wp_get_attachment_image_src', array( static::class, 'bust_attachment_image_src' ) );
		}
	}

	// ─────────────────────────────────────────────────────────────
	// 1. Timezone Management
	// ─────────────────────────────────────────────────────────────

	/**
	 * Get current site timezone offset as a float.
	 *
	 * @return float Offset in hours (e.g., 0.0, 5.5, -4.0)
	 */
	public static function get_timezone_offset(): float {
		return (float) get_option( 'gmt_offset', 0 );
	}

	/**
	 * Get formatted UTC timezone label (e.g., UTC+05:30, UTC+00).
	 *
	 * @return string
	 */
	public static function get_timezone_string(): string {
		$offset   = self::get_timezone_offset();
		$sign     = $offset >= 0 ? '+' : '-';
		$absolute = abs( $offset );
		$hours    = (int) floor( $absolute );
		$minutes  = (int) round( ( $absolute - $hours ) * 60 );

		if ( 0 === $hours && 0 === $minutes ) {
			return 'UTC+00';
		}

		$label = sprintf( 'UTC%s%02d', $sign, $hours );
		if ( 0 !== $minutes ) {
			$label .= sprintf( ':%02d', $minutes );
		}

		return $label;
	}

	/**
	 * Get current timestamp localized to the CMS configured timezone.
	 *
	 * @return int
	 */
	public static function get_site_time(): int {
		$offset_seconds = (int) ( self::get_timezone_offset() * HOUR_IN_SECONDS );
		return time() + $offset_seconds;
	}

	/**
	 * Format a timestamp or date string according to the configured timezone offset.
	 *
	 * @param int|string|DateTime $datetime
	 * @param string $format
	 * @return string
	 */
	public static function format_datetime( $datetime = 'now', string $format = 'j F Y, g:i A' ): string {
		try {
			if ( 'now' === $datetime || empty( $datetime ) ) {
				$ts = time();
			} elseif ( is_numeric( $datetime ) ) {
				$ts = (int) $datetime;
			} elseif ( $datetime instanceof DateTime ) {
				$ts = $datetime->getTimestamp();
			} else {
				$ts = strtotime( (string) $datetime ) ?: time();
			}

			// Apply GMT offset
			$offset_seconds = (int) ( self::get_timezone_offset() * HOUR_IN_SECONDS );
			$localized_ts   = $ts + $offset_seconds;

			return gmdate( $format, $localized_ts );
		} catch ( Exception $e ) {
			return date( $format );
		}
	}

	// ─────────────────────────────────────────────────────────────
	// 2. Image Optimization Controls
	// ─────────────────────────────────────────────────────────────

	/**
	 * Check if WebP image optimization is disabled.
	 *
	 * @return bool
	 */
	public static function is_image_optimization_disabled(): bool {
		return '1' === (string) get_option( 'ah_disable_optimized_images', '0' );
	}

	/**
	 * Check if WebP image optimization is active.
	 *
	 * @return bool
	 */
	public static function is_image_optimization_enabled(): bool {
		return ! self::is_image_optimization_disabled();
	}

	/**
	 * Filter large image threshold when optimization is disabled.
	 */
	public static function filter_big_image_threshold( $threshold ) {
		return self::is_image_optimization_disabled() ? false : $threshold;
	}

	/**
	 * Filter default image editor output formats.
	 */
	public static function filter_image_output_format( array $formats ): array {
		if ( self::is_image_optimization_disabled() ) {
			return $formats;
		}

		// Prefer WebP output format when optimization is active
		if ( ! isset( $formats['image/jpeg'] ) ) {
			$formats['image/jpeg'] = 'image/webp';
		}
		return $formats;
	}

	// ─────────────────────────────────────────────────────────────
	// 2b. Asset Cache-Busting (VINTAGESOUL_CACHE_VERSION override)
	// ─────────────────────────────────────────────────────────────

	/**
	 * Append ?v=VINTAGESOUL_CACHE_VERSION to an attachment URL so browsers
	 * fetch a fresh copy after a forced cache-version bump.
	 *
	 * @param string $url
	 * @return string
	 */
	public static function bust_attachment_url( $url ) {
		if ( ! is_string( $url ) || '' === $url || false !== strpos( $url, 'v=' ) ) {
			return $url;
		}
		$sep = false === strpos( $url, '?' ) ? '?v=' : '&v=';
		return $url . $sep . VINTAGESOUL_CACHE_VERSION;
	}

	/**
	 * Same as bust_attachment_url() for the [url, width, height, is_intermediate]
	 * tuple wp_get_attachment_image_src() returns.
	 *
	 * @param array|false $image
	 * @return array|false
	 */
	public static function bust_attachment_image_src( $image ) {
		if ( ! is_array( $image ) || empty( $image[0] ) ) {
			return $image;
		}
		$image[0] = self::bust_attachment_url( $image[0] );
		return $image;
	}

	// ─────────────────────────────────────────────────────────────
	// 3. High-Performance Caching Engine
	// ─────────────────────────────────────────────────────────────

	/**
	 * Get the absolute directory path where theme cache files are stored.
	 */
	public static function get_cache_dir(): string {
		$dir = defined( 'WP_CONTENT_DIR' )
			? WP_CONTENT_DIR . '/cache/vst-cache'
			: VINTAGESOUL_DIR . '/cache';

		if ( ! is_dir( $dir ) && function_exists( 'wp_mkdir_p' ) ) {
			wp_mkdir_p( $dir );
		}
		return $dir;
	}

	/**
	 * Check if caching is currently enabled.
	 */
	public static function is_cache_enabled(): bool {
		if ( is_admin() ) {
			return false;
		}

		if ( self::is_bypass_active() ) {
			return false;
		}

		return '1' === (string) get_option( 'ah_cache_enabled', '0' );
	}

	/**
	 * Get default cache expiry in seconds.
	 */
	public static function get_cache_expiry(): int {
		return (int) get_option( 'ah_cache_expiry', 3600 );
	}

	/**
	 * Check if cache bypass is requested by logged-in administrator or query param.
	 */
	public static function is_bypass_active(): bool {
		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			return true;
		}

		if ( isset( $_GET['nocache'] ) || isset( $_GET['bypass_cache'] ) || isset( $_GET['clear_cache'] ) || isset( $_GET['cache_clear'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve cached item by key and group.
	 *
	 * @param string $key
	 * @param string $group
	 * @return mixed
	 */
	public static function get( string $key, string $group = 'general' ) {
		if ( ! self::is_cache_enabled() ) {
			return false;
		}

		$cache_key = $group . ':' . $key;
		if ( array_key_exists( $cache_key, self::$memory_cache ) ) {
			return self::$memory_cache[ $cache_key ];
		}

		$file = self::get_cache_file( $key, $group );
		if ( ! is_file( $file ) ) {
			return false;
		}

		$ttl = self::get_cache_expiry();
		if ( ( time() - filemtime( $file ) ) > $ttl ) {
			@unlink( $file );
			return false;
		}

		$raw = @file_get_contents( $file );
		if ( false === $raw || '' === $raw ) {
			return false;
		}

		$payload = json_decode( $raw, true );
		if ( ! is_array( $payload ) || empty( $payload['expires_at'] ) ) {
			return false;
		}

		if ( (int) $payload['expires_at'] < time() ) {
			@unlink( $file );
			return false;
		}

		$data = $payload['data'] ?? false;
		self::$memory_cache[ $cache_key ] = $data;
		return $data;
	}

	/**
	 * Store data in cache.
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param string $group
	 * @param int|null $ttl
	 * @return bool
	 */
	public static function set( string $key, $data, string $group = 'general', ?int $ttl = null ): bool {
		if ( ! self::is_cache_enabled() ) {
			return false;
		}

		$cache_key = $group . ':' . $key;
		self::$memory_cache[ $cache_key ] = $data;

		$file = self::get_cache_file( $key, $group );
		$dir  = dirname( $file );
		if ( ! is_dir( $dir ) && function_exists( 'wp_mkdir_p' ) ) {
			wp_mkdir_p( $dir );
		}

		$duration = ( null !== $ttl && $ttl > 0 ) ? $ttl : self::get_cache_expiry();
		$payload  = array(
			'created_at' => time(),
			'expires_at' => time() + $duration,
			'data'       => $data,
		);

		return false !== @file_put_contents( $file, wp_json_encode( $payload ), LOCK_EX );
	}

	/**
	 * Delete a specific cached entry.
	 */
	public static function delete( string $key, string $group = 'general' ): bool {
		$cache_key = $group . ':' . $key;
		unset( self::$memory_cache[ $cache_key ] );

		$file = self::get_cache_file( $key, $group );
		if ( is_file( $file ) ) {
			return @unlink( $file );
		}
		return true;
	}

	/**
	 * Flush all theme and plugin caches.
	 *
	 * @return bool
	 */
	public static function clear_all(): bool {
		self::$memory_cache = array();

		$dir = self::get_cache_dir();
		self::delete_directory( $dir );

		// Clear plugin caches if available
		if ( class_exists( 'AH_Cache' ) ) {
			$registry = get_option( 'ah_cache_registry', array() );
			if ( is_array( $registry ) ) {
				foreach ( $registry as $tbl => $keys ) {
					foreach ( (array) $keys as $k ) {
						delete_transient( $k );
					}
				}
			}
			delete_option( 'ah_cache_registry' );
			if ( method_exists( 'AH_Cache', 'clear_temp_all' ) ) {
				\AH_Cache::clear_temp_all();
			}
		}

		return true;
	}

	/**
	 * Clear cache on request if authenticated admin.
	 */
	public static function handle_cache_clear_request(): void {
		if ( isset( $_GET['clear_cache'] ) && current_user_can( 'manage_options' ) ) {
			self::clear_all();
		}
	}

	private static function get_cache_file( string $key, string $group ): string {
		$hash       = md5( $key );
		$safe_group = function_exists( 'sanitize_key' ) ? sanitize_key( $group ) : preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $group ) );
		return self::get_cache_dir() . '/' . ( $safe_group ?: 'general' ) . '/' . $hash . '.json';
	}

	private static function delete_directory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$items = scandir( $dir );
		if ( is_array( $items ) ) {
			foreach ( $items as $item ) {
				if ( '.' === $item || '..' === $item ) {
					continue;
				}
				$path = $dir . '/' . $item;
				if ( is_dir( $path ) ) {
					self::delete_directory( $path );
					@rmdir( $path );
				} else {
					@unlink( $path );
				}
			}
		}
	}
}

