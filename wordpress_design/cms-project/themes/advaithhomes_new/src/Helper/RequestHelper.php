<?php
namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

class RequestHelper {

	public static function get( string $key = '', string $default = '' ): string {
		if ( '' === $key ) {
			return $default;
		}
		if ( isset( $_REQUEST[ $key ] ) ) {
			return \sanitize_text_field( \wp_unslash( $_REQUEST[ $key ] ) );
		}
		return $default;
	}

	public static function getJson( string $key = '', string $default = '' ): mixed {
		$data = self::getJsonBody();
		if ( '' === $key ) {
			return $default;
		}
		if ( \is_array( $data ) && isset( $data[ $key ] ) ) {
			return $data[ $key ];
		}
		return $default;
	}

	public static function getJsonBody(): array {
		static $data = null;
		if ( null === $data ) {
			$raw = \file_get_contents( 'php://input' );
			if ( ! empty( $raw ) ) {
				$decoded = \json_decode( $raw, true );
				$data = ( \json_last_error() === \JSON_ERROR_NONE && \is_array( $decoded ) ) ? $decoded : [];
			} else {
				$data = [];
			}
		}
		return $data;
	}
}
