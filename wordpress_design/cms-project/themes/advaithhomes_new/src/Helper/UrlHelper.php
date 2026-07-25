<?php

namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

class UrlHelper {

	public static function prettyPathSlug( string $base_url ): string {
		$raw    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$path   = trim( (string) parse_url( $raw, PHP_URL_PATH ), '/' );
		$prefix = trim( (string) $base_url, '/' ) . '/';
		if ( '' === $path || 0 !== strpos( $path, $prefix ) ) {
			return '';
		}
		return sanitize_title( substr( $path, strlen( $prefix ) ) );
	}

	public static function expertProfileUrl( string $slug ): string {
		$base = defined( 'SITE_EXPERT_URL' ) ? SITE_EXPERT_URL : '/ask-expert/';
		return home_url( rtrim( $base, '/' ) . '/' . trim( $slug, '/' ) . '/' );
	}

	public static function calcPageUrl( string $key ): string {
		$base = defined( 'SITE_TOOLS_URL' ) ? SITE_TOOLS_URL : '/calculators/';
		return home_url( rtrim( $base, '/' ) . '/' . trim( $key, '/' ) . '/' );
	}
}
