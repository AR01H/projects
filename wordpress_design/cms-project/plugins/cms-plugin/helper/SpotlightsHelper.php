<?php
defined( 'ABSPATH' ) || exit;

/**
 * Helper functions for Spotlights admin page.
 */
class AH_Spotlights_Helper {

	/**
	 * Build a URL for the spotlights admin page.
	 */
	public static function url( array $args = array() ): string {
		return esc_url( add_query_arg( array_merge( array( 'page' => 'ah-spotlights' ), $args ), admin_url( 'admin.php' ) ) );
	}
}
