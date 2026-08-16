<?php
namespace VintageSoul\Support;

defined( 'ABSPATH' ) || exit;

final class ImageHelper {

	public static function from_attachment( int $attachment_id, string $size = 'vintagesoul-card' ): ?array {
		$src = wp_get_attachment_image_src( $attachment_id, $size );
		if ( ! $src ) {
			return null;
		}
		return array(
			'src'    => $src[0],
			'width'  => (int) $src[1],
			'height' => (int) $src[2],
			'alt'    => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
		);
	}

	public static function srcset( int $attachment_id, string $size = 'vintagesoul-card' ): string {
		$srcset = wp_get_attachment_image_srcset( $attachment_id, $size );
		return $srcset ?: '';
	}
}
