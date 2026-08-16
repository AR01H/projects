<?php
namespace VintageSoul\Support;

defined( 'ABSPATH' ) || exit;

final class UrlHelper {

	public static function resolve( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		if ( '#' === $url[0] || preg_match( '#^https?://#i', $url ) || str_starts_with( $url, 'mailto:' ) || str_starts_with( $url, 'tel:' ) ) {
			return $url;
		}
		return home_url( '/' . ltrim( $url, '/' ) );
	}
}
