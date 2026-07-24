<?php

namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

class UrlHelper {

	public static function prettyPathSlug( string $base_url ): string {
		$path = wp_parse_url( $base_url, PHP_URL_PATH );
		if ( ! $path || '/' === $path ) {
			return '';
		}
		$slug = trim( $path, '/' );
		$slug = preg_replace( '/-guides?$/', '', $slug );
		return ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
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
