<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class SettingsService {

	private static function settings(): array {
		return (array) ( JsonFileProvider::read( 'data/content/contact-info.json' ) ?? array() );
	}

	public static function tagline_fallback(): string {
		$d = self::settings();
		return (string) ( $d['tagline'] ?? ( $d['site']['tagline_fallback'] ?? '' ) );
	}

	public static function phone(): string {
		$d = self::settings();
		return (string) ( $d['phone'] ?? ( $d['site']['phone'] ?? '' ) );
	}

	public static function whatsapp(): string {
		$d = self::settings();
		return (string) ( $d['whatsapp'] ?? ( $d['site']['whatsapp'] ?? self::phone() ) );
	}

	public static function email(): string {
		$d = self::settings();
		return (string) ( $d['email'] ?? ( $d['site']['email'] ?? '' ) );
	}

	public static function address(): string {
		$d = self::settings();
		return (string) ( $d['address'] ?? ( $d['site']['address'] ?? '' ) );
	}

	public static function website(): string {
		$d = self::settings();
		return (string) ( $d['website'] ?? ( $d['site']['website'] ?? '' ) );
	}

	public static function opening_hours(): string {
		$d = self::settings();
		return (string) ( $d['hours'] ?? ( $d['opening_hours'] ?? ( $d['site']['opening_hours'] ?? '' ) ) );
	}

	public static function logo_fallback( string $context = 'header' ): string {
		$d = self::settings();
		if ( 'footer' === $context && ! empty( $d['logo_footer'] ) ) {
			return (string) $d['logo_footer'];
		}
		return (string) ( $d['logo'] ?? ( $d['site']['logo_fallback'] ?? '' ) );
	}

	public static function socials(): array {
		$d = self::settings();
		if ( ! empty( $d['social_links'] ) && is_array( $d['social_links'] ) ) {
			return array_filter( (array) $d['social_links'] );
		}
		if ( ! empty( $d['socials'] ) && is_array( $d['socials'] ) ) {
			$links = array();
			foreach ( $d['socials'] as $soc ) {
				$icon = (string) ( $soc['icon'] ?? '' );
				$url  = (string) ( $soc['url'] ?? '' );
				if ( '' !== $icon && '' !== $url ) {
					$links[ $icon ] = $url;
				}
			}
			return $links;
		}
		return array_filter( (array) ( $d['social'] ?? array() ) );
	}

	public static function social_links(): array {
		$d = self::settings();
		if ( ! empty( $d['socials'] ) && is_array( $d['socials'] ) ) {
			return (array) $d['socials'];
		}
		return array();
	}

	public static function social_url( string $key, string $default = '' ): string {
		$socials = self::socials();
		return (string) ( $socials[ $key ] ?? $default );
	}

	public static function whatsapp_url(): string {
		$d = self::settings();
		if ( ! empty( $d['whatsapp_url'] ) ) {
			return (string) $d['whatsapp_url'];
		}
		$wa = self::social_url( 'whatsapp', '' );
		if ( '' !== $wa ) {
			return $wa;
		}
		$wa_num = self::whatsapp();
		$digits = preg_replace( '/\D+/', '', $wa_num );
		return '' !== $digits ? 'https://wa.me/' . $digits : '';
	}

	public static function preheader(): array {
		$d = self::settings();
		return array_values( array_filter( array_map( 'strval', (array) ( $d['preheader'] ?? array() ) ) ) );
	}
}
