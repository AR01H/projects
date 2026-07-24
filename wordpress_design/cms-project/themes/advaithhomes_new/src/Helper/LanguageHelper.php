<?php

namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

class LanguageHelper {

	public static function getAllowedLanguages(): array {
		$langs = array(
			'en' => 'English',
			'te' => 'తెలుగు',
		);
		return apply_filters( 'adn_allowed_languages', $langs );
	}

	public static function getLanguageStrings( string $lang ): array {
		$lang_file = ADN_THEME_DIR . '/languages/' . $lang . '.php';
		if ( file_exists( $lang_file ) ) {
			$strings = array();
			require $lang_file;
			return $strings;
		}
		return array();
	}

	public static function translate( string $title, string $lang = '' ): string {
		if ( '' === $lang ) {
			$lang = self::getCurrentLanguage();
		}
		if ( 'en' === $lang || '' === $lang ) {
			return $title;
		}
		$strings = self::getLanguageStrings( $lang );
		return isset( $strings[ $title ] ) ? $strings[ $title ] : $title;
	}

	public static function getCurrentLanguage(): string {
		if ( isset( $_COOKIE['adn_lang'] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE['adn_lang'] ) );
		}
		$lang = isset( $_GET['lang'] ) ? sanitize_text_field( wp_unslash( $_GET['lang'] ) ) : '';
		if ( '' !== $lang && in_array( $lang, array_keys( self::getAllowedLanguages() ), true ) ) {
			return $lang;
		}
		return 'en';
	}

	public static function setLanguageCookie(): void {
		if ( isset( $_GET['lang'] ) ) {
			$lang = sanitize_text_field( wp_unslash( $_GET['lang'] ) );
			if ( in_array( $lang, array_keys( self::getAllowedLanguages() ), true ) ) {
				setcookie( 'adn_lang', $lang, time() + ( 86400 * 365 ), '/' );
				$_COOKIE['adn_lang'] = $lang;
			}
		}
	}
}
