<?php
defined( 'ABSPATH' ) || exit;

/**
 * Helper functions for Featured In admin page.
 */
class AH_Featured_In_Helper {

	const OPTION_KEY = 'ah_featured_in_sections';

	/**
	 * Get all featured in sections from wp_options.
	 */
	public static function get_all(): array {
		$raw = get_option( self::OPTION_KEY, '' );
		$dec = $raw ? json_decode( $raw, true ) : array();
		return is_array( $dec ) ? $dec : array();
	}

	/**
	 * Save all featured in sections.
	 */
	public static function save_all( array $sections ): void {
		update_option( self::OPTION_KEY, wp_json_encode( array_values( $sections ) ) );
	}

	/**
	 * Find a section by ID.
	 */
	public static function find( string $id ): ?array {
		foreach ( self::get_all() as $s ) {
			if ( isset( $s['id'] ) && $s['id'] === $id ) {
				return $s;
			}
		}
		return null;
	}

	/**
	 * Build a URL for the featured in admin page.
	 */
	public static function url( array $args = array() ): string {
		return esc_url( add_query_arg( array_merge( array( 'page' => 'ah-featured-in' ), $args ), admin_url( 'admin.php' ) ) );
	}
}
