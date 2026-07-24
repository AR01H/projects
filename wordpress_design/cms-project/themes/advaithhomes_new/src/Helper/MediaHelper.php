<?php
namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

class MediaHelper {

	public static function resolveUrlType( $value ): array {
		$value = \trim( (string) $value );
		if ( '' === $value ) {
			return [ 'url' => '', 'type' => 'image' ];
		}

		if ( \ctype_digit( $value ) ) {
			$id = (int) $value;
			$mime = \get_post_mime_type( $id );
			$type = ( \is_string( $mime ) && 0 === \strpos( $mime, 'video/' ) ) ? 'video' : 'image';
			$url = 'video' === $type
				? (string) \wp_get_attachment_url( $id )
				: (string) \wp_get_attachment_image_url( $id, 'full' );
			return [ 'url' => $url ?: '', 'type' => $type ];
		}

		$path = (string) ( \wp_parse_url( $value, \PHP_URL_PATH ) ?: $value );
		$ext = \strtolower( (string) \pathinfo( $path, \PATHINFO_EXTENSION ) );
		$type = \in_array( $ext, [ 'mp4', 'webm', 'ogv', 'mov' ], true ) ? 'video' : 'image';
		return [ 'url' => $value, 'type' => $type ];
	}
}
