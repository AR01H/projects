<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class SettingsService {

	private static function settings(): array {
		return JsonFileProvider::read( 'config/settings.json' );
	}

	public static function tagline_fallback(): string {
		return (string) ( self::settings()['site']['tagline_fallback'] ?? '' );
	}

	public static function phone(): string {
		return (string) ( self::settings()['site']['phone'] ?? '' );
	}

	public static function email(): string {
		return (string) ( self::settings()['site']['email'] ?? '' );
	}

	public static function address(): string {
		return (string) ( self::settings()['site']['address'] ?? '' );
	}

	public static function logo_fallback( string $context = 'header' ): string {
		$site = self::settings()['site'] ?? array();
		if ( 'footer' === $context && ! empty( $site['logo_fallback_footer'] ) ) {
			return (string) $site['logo_fallback_footer'];
		}
		return (string) ( $site['logo_fallback'] ?? '' );
	}

	public static function socials(): array {
		return array_filter( (array) ( self::settings()['social'] ?? array() ) );
	}

	public static function social_url( string $key, string $default = '' ): string {
		$socials = self::socials();
		return (string) ( $socials[ $key ] ?? $default );
	}

	public static function whatsapp_url(): string {
		$wa = self::social_url( 'whatsapp', '' );
		if ( '' !== $wa ) {
			return $wa;
		}
		$phone = self::phone();
		$digits = preg_replace( '/\D+/', '', $phone );
		return '' !== $digits ? 'https://wa.me/' . $digits : '';
	}

	public static function preheader(): array {
		return array_values( array_filter( array_map( 'strval', (array) ( self::settings()['preheader'] ?? array() ) ) ) );
	}
}
