<?php
/**
 * Site-Wide Notices Management
 * Handle storing/retrieving notice data from database
 */
defined( 'ABSPATH' ) || exit;

class AH_Notice_Helper {

	const OPTION_KEY = 'ah_important_notice';

	/**
	 * Get the current active notice
	 */
	public static function get_notice(): array {
		$stored = get_option( self::OPTION_KEY, '' );
		if ( ! empty( $stored ) ) {
			$notice = json_decode( $stored, true );
			if ( is_array( $notice ) ) {
				return $notice;
			}
		}
		return self::get_defaults();
	}

	/**
	 * Save notice data
	 */
	public static function save_notice( array $data ): bool {
		$notice = [
			'enabled'        => ! empty( $data['enabled'] ),
			'id'             => sanitize_key( $data['id'] ?? 'default' ),
			'title'          => sanitize_text_field( wp_unslash( $data['title'] ?? 'Important Update' ) ),
			'message'        => sanitize_text_field( wp_unslash( $data['message'] ?? '' ) ),
			'image'          => esc_url_raw( wp_unslash( $data['image'] ?? '' ) ),
			'button_label'   => sanitize_text_field( wp_unslash( $data['button_label'] ?? '' ) ),
			'button_url'     => esc_url_raw( wp_unslash( $data['button_url'] ?? '' ) ),
		];

		return update_option( self::OPTION_KEY, wp_json_encode( $notice ) );
	}

	/**
	 * Get default notice structure
	 */
	public static function get_defaults(): array {
		return [
			'enabled'        => false,
			'id'             => 'default',
			'title'          => 'Important Update',
			'message'        => '',
			'image'          => '',
			'button_label'   => '',
			'button_url'     => '',
		];
	}

	/**
	 * Disable notice
	 */
	public static function disable(): bool {
		$notice = self::get_notice();
		$notice['enabled'] = false;
		return update_option( self::OPTION_KEY, wp_json_encode( $notice ) );
	}

	/**
	 * Clear notice completely
	 */
	public static function clear(): bool {
		return delete_option( self::OPTION_KEY );
	}
}
