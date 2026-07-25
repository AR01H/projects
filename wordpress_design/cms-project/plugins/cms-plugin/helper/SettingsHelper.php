<?php
defined( 'ABSPATH' ) || exit;

/**
 * Helper functions for Settings admin page.
 */
class AH_Settings_Helper {

	/**
	 * Get the URL for a setting value (handles attachment IDs and raw URLs).
	 */
	public static function image_url( string $value ): string {
		if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return esc_url( $value );
		}

		$img_id = absint( $value );
		if ( ! $img_id ) {
			return '';
		}

		// Try image thumbnail first (works for images/GIFs).
		$url = wp_get_attachment_image_url( $img_id, 'medium' );
		if ( $url ) {
			return esc_url( $url );
		}

		// Fall back to raw attachment URL (works for videos/audio).
		$url = wp_get_attachment_url( $img_id );
		if ( $url ) {
			return esc_url( $url );
		}

		return '';
	}
}
