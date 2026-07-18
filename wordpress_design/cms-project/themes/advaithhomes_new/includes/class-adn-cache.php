<?php
/**
 * Custom filesystem caching utility for Advaith Homes CMS context data.
 * Speeds up page loading by storing parsed page context in JSON files under wp-content/cache/adn-cache/.
 */

defined( 'ABSPATH' ) || exit;

class ADN_Cache {

	/**
	 * Get the absolute directory path where cache files are stored.
	 */
	public static function get_cache_dir(): string {
		$dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/cache/adn-cache' : __DIR__ . '/../cache';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		return $dir;
	}

	public static function is_bypass_active(): bool {
		// Bypass cache for logged-in users (admins editing content) to see changes immediately
		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			return true;
		}

		// Bypass parameters for developers/testing
		if ( isset( $_GET['nocache'] ) || isset( $_GET['bypass_cache'] ) || isset( $_GET['clear_cache'] ) || isset( $_GET['cache_clear'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if caching is enabled globally.
	 */
	public static function is_enabled(): bool {
		// Only apply caching to frontend requests (is_admin() must be false)
		if ( is_admin() ) {
			return false;
		}

		// Check if caching is enabled via Global Settings option (default to enabled)
		return (bool) get_option( 'ah_cache_enabled', 1 );
	}

	/**
	 * Get cached data by key and group.
	 */
	public static function get( string $key, string $group = 'general' ) {
		if ( ! self::is_enabled() ) {
			return false;
		}

		// If a cache clearing parameter is passed, clear cache and return false
		if ( isset( $_GET['clear_cache'] ) || isset( $_GET['cache_clear'] ) ) {
			self::clear_all();
			return false;
		}

		// Bypass reading from cache if user is logged in or query param bypass is active
		if ( self::is_bypass_active() ) {
			return false;
		}

		// Run periodic garbage collection (2% chance) to clean up expired files
		self::maybe_clean_expired_files( 2 );

		$file = self::get_cache_file( $key, $group );
		if ( ! is_file( $file ) ) {
			return false;
		}

		// Perform an extremely fast filesystem metadata check to see if the file has expired
		$ttl = (int) get_option( 'ah_cache_expiry', 3600 );
		if ( ( time() - filemtime( $file ) ) > $ttl ) {
			@unlink( $file );
			return false;
		}

		$raw = @file_get_contents( $file );
		if ( false === $raw || '' === $raw ) {
			return false;
		}

		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) || empty( $data['expires_at'] ) ) {
			return false;
		}

		// Double-check explicit expires_at from JSON payload
		if ( (int) $data['expires_at'] < time() ) {
			@unlink( $file );
			return false;
		}

		return $data['data'] ?? false;
	}

	/**
	 * Set cached data by key and group.
	 */
	public static function set( string $key, $data, string $group = 'general', int $ttl = 3600 ): bool {
		if ( ! self::is_enabled() ) {
			return false;
		}

		// Do not write to cache if query parameters explicitly request bypass/refresh
		if ( isset( $_GET['nocache'] ) || isset( $_GET['bypass_cache'] ) || isset( $_GET['clear_cache'] ) || isset( $_GET['cache_clear'] ) ) {
			return false;
		}

		$file = self::get_cache_file( $key, $group );
		$dir  = dirname( $file );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$payload = array(
			'created_at' => time(),
			'expires_at' => time() + max( 1, $ttl ),
			'data'       => $data,
		);

		return false !== @file_put_contents( $file, wp_json_encode( $payload ), LOCK_EX );
	}

	/**
	 * Clear all cache files under our directory and call plugin cache clearing.
	 */
	public static function clear_all(): bool {
		$dir = self::get_cache_dir();
		self::delete_directory( $dir );

		// Clear the plugin's transients & temp-files as well (if present)
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
			AH_Cache::clear_temp_all();
		}

		return true;
	}

	/**
	 * Get cache file path.
	 */
	private static function get_cache_file( string $key, string $group ): string {
		$hash = md5( $key );
		return self::get_cache_dir() . '/' . sanitize_key( $group ) . '/' . $hash . '.json';
	}

	/**
	 * Run garbage collection to clean up expired files (probability-based, e.g. 2% of requests)
	 */
	public static function maybe_clean_expired_files( int $probability = 1 ): void {
		if ( mt_rand( 1, 100 ) > $probability ) {
			return;
		}

		$dir = self::get_cache_dir();
		if ( ! is_dir( $dir ) ) {
			return;
		}

		// Recursively scan directories for expired .json files
		try {
			$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ) );
			foreach ( $iterator as $file ) {
				if ( $file->isFile() && 'json' === $file->getExtension() ) {
					$path = $file->getRealPath();
					$raw  = @file_get_contents( $path );
					if ( $raw ) {
						$data = json_decode( $raw, true );
						if ( is_array( $data ) && isset( $data['expires_at'] ) && (int) $data['expires_at'] < time() ) {
							@unlink( $path );
						}
					}
				}
			}
		} catch ( Exception $e ) {
			// Fail silently if directory scanning encounters permission issues
		}
	}

	/**
	 * Delete a directory recursively.
	 */
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
