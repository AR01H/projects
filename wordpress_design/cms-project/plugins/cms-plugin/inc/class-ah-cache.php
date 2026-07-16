<?php
/**
 * Custom caching engine using WordPress Transients.
 * Mimics Drupal-like cache_get / cache_set functions.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'CACHE_PERMANENT' ) ) {
	define( 'CACHE_PERMANENT', 0 );
}

class AH_Cache {

	/**
	 * Check if caching is enabled via Global Settings.
	 * Only applies to frontend queries unless explicitly bypassed.
	 */
	public static function is_enabled(): bool {
		if ( is_admin() ) {
			return false; // Do not cache admin queries.
		}
		return (bool) get_option( 'ah_cache_enabled', 0 );
	}

	/**
	 * Get default cache expiration in seconds.
	 */
	public static function get_default_expire(): int {
		return (int) get_option( 'ah_cache_expiry', 3600 );
	}

	/**
	 * Get an item from the cache.
	 */
	public static function get( $cid, $table = 'cache' ) {
		if ( ! self::is_enabled() ) {
			return false;
		}
		$transient_name = self::build_transient_name( $cid, $table );
		return get_transient( $transient_name );
	}

	/**
	 * Set an item in the cache.
	 * Expire can be a unix timestamp (time() + seconds) or a duration in seconds.
	 * If it's larger than 10 years in seconds, we assume it's a timestamp.
	 */
	public static function set( $cid, $data, $table = 'cache', $expire = CACHE_PERMANENT, $headers = null ) {
		if ( ! self::is_enabled() ) {
			return false;
		}

		$transient_name = self::build_transient_name( $cid, $table );
		
		if ( $expire === CACHE_PERMANENT || $expire === 0 ) {
			$expire_seconds = self::get_default_expire();
		} else if ( $expire > 315360000 ) { // It's likely a timestamp (time() + X)
			$expire_seconds = max( 1, $expire - time() );
		} else {
			$expire_seconds = $expire;
		}

		// Keep track of this key in an option array for clearing by table.
		self::register_key( $transient_name, $table );

		return set_transient( $transient_name, $data, $expire_seconds );
	}

	/**
	 * Clear cache items.
	 */
	public static function clear_all( $cid = null, $table = null, $wildcard = false ) {
		if ( null === $cid && null === $table ) {
			// Clear EVERYTHING we track
			$registry = get_option( 'ah_cache_registry', array() );
			foreach ( $registry as $tbl => $keys ) {
				foreach ( $keys as $k ) {
					delete_transient( $k );
				}
			}
			delete_option( 'ah_cache_registry' );
			self::clear_temp_all();
			return true;
		}

		if ( null !== $table && null === $cid && $wildcard ) {
			// Clear entire table
			$registry = get_option( 'ah_cache_registry', array() );
			if ( isset( $registry[ $table ] ) ) {
				foreach ( $registry[ $table ] as $k ) {
					delete_transient( $k );
				}
				unset( $registry[ $table ] );
				update_option( 'ah_cache_registry', $registry );
			}
			self::clear_temp_all( null, $table, true );
			return true;
		}

		if ( null !== $cid ) {
			// Clear specific CID
			$t_table = $table ?: 'cache';
			$transient_name = self::build_transient_name( $cid, $t_table );
			delete_transient( $transient_name );
			
			// Remove from registry
			$registry = get_option( 'ah_cache_registry', array() );
			if ( isset( $registry[ $t_table ] ) ) {
				$registry[ $t_table ] = array_diff( $registry[ $t_table ], array( $transient_name ) );
				update_option( 'ah_cache_registry', $registry );
			}
			self::clear_temp_all( $cid, $t_table );
			return true;
		}

		return false;
	}

	/**
	 * Get an item from the temp-file cache.
	 */
	public static function temp_get( $cid, $table = 'cache' ) {
		if ( ! self::is_enabled() ) {
			return false;
		}
		$path = self::build_temp_path( $cid, $table );
		if ( ! is_file( $path ) ) {
			return false;
		}
		$raw = @file_get_contents( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( false === $raw || '' === $raw ) {
			return false;
		}
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) || empty( $data['expires_at'] ) ) {
			return false;
		}
		if ( (int) $data['expires_at'] < time() ) {
			@unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return false;
		}
		return $data['data'] ?? false;
	}

	/**
	 * Set an item in the temp-file cache.
	 */
	public static function temp_set( $cid, $data, $table = 'cache', $expire = CACHE_PERMANENT ) {
		if ( ! self::is_enabled() ) {
			return false;
		}

		if ( $expire === CACHE_PERMANENT || $expire === 0 ) {
			$expire_seconds = self::get_default_expire();
		} else if ( $expire > 315360000 ) {
			$expire_seconds = max( 1, $expire - time() );
		} else {
			$expire_seconds = $expire;
		}

		$path = self::build_temp_path( $cid, $table );
		$dir  = dirname( $path );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$payload = array(
			'created_at' => time(),
			'expires_at' => time() + max( 1, $expire_seconds ),
			'data'       => $data,
		);
		return false !== @file_put_contents( $path, wp_json_encode( $payload ), LOCK_EX ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Clear the temp-file cache.
	 */
	public static function clear_temp_all( $cid = null, $table = null, $wildcard = false ) {
		$base = self::temp_base_dir();
		if ( '' === $base || ! is_dir( $base ) ) {
			return false;
		}

		if ( null === $cid && null === $table ) {
			self::delete_temp_files( $base . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.json' );
			return true;
		}

		if ( null !== $table && null === $cid && $wildcard ) {
			self::delete_temp_files( $base . DIRECTORY_SEPARATOR . $table . DIRECTORY_SEPARATOR . '*.json' );
			return true;
		}

		if ( null !== $cid ) {
			$path = self::build_temp_path( $cid, $table ?: 'cache' );
			if ( is_file( $path ) ) {
				@unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
			return true;
		}

		return false;
	}

	private static function delete_temp_files( string $pattern ): void {
		$files = glob( $pattern );
		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
		}
	}

	private static function temp_base_dir(): string {
		$site_key = function_exists( 'home_url' ) ? (string) home_url( '/' ) : 'site';
		$site_key = function_exists( 'wp_parse_url' ) ? (string) ( wp_parse_url( $site_key, PHP_URL_HOST ) ?: $site_key ) : $site_key;
		$site_key = sanitize_key( $site_key );
		return rtrim( sys_get_temp_dir(), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'adn-home-fragments' . DIRECTORY_SEPARATOR . $site_key;
	}

	private static function build_temp_path( $cid, $table ): string {
		$dir = self::temp_base_dir() . DIRECTORY_SEPARATOR . sanitize_key( (string) $table );
		return $dir . DIRECTORY_SEPARATOR . substr( md5( (string) $table . '_' . (string) $cid ), 0, 40 ) . '.json';
	}

	/**
	 * Generate a standardized cache key from variable arguments.
	 */
	public static function key_gen( ...$args ): string {
		$key_parts = array();
		foreach ( $args as $arg ) {
			if ( is_array( $arg ) || is_object( $arg ) ) {
				$key_parts[] = md5( wp_json_encode( $arg ) );
			} else {
				$key_parts[] = (string) $arg;
			}
		}
		// Hash the final string to ensure it fits in the WP transient name limit (45 chars)
		return md5( implode( '_', $key_parts ) );
	}

	private static function build_transient_name( $cid, $table ): string {
		// Transient names can be max 45 chars in WP.
		// We use a prefix + md5 of the table and cid.
		return 'ahc_' . substr( md5( $table . '_' . $cid ), 0, 40 );
	}

	private static function register_key( $transient_name, $table ) {
		$registry = get_option( 'ah_cache_registry', array() );
		if ( ! isset( $registry[ $table ] ) ) {
			$registry[ $table ] = array();
		}
		if ( ! in_array( $transient_name, $registry[ $table ], true ) ) {
			$registry[ $table ][] = $transient_name;
			update_option( 'ah_cache_registry', $registry );
		}
	}
}

// ── Global Helper Functions ──

if ( ! function_exists( 'cache_get' ) ) {
	function cache_get( $cid, $table = 'cache' ) {
		return AH_Cache::get( $cid, $table );
	}
}

if ( ! function_exists( 'cache_set' ) ) {
	function cache_set( $cid, $data, $table = 'cache', $expire = CACHE_PERMANENT, $headers = null ) {
		return AH_Cache::set( $cid, $data, $table, $expire, $headers );
	}
}

if ( ! function_exists( 'cache_clear_all' ) ) {
	function cache_clear_all( $cid = null, $table = null, $wildcard = false ) {
		return AH_Cache::clear_all( $cid, $table, $wildcard );
	}
}

if ( ! function_exists( 'cache_key_gen' ) ) {
	function cache_key_gen( ...$args ) {
		return AH_Cache::key_gen( ...$args );
	}
}
