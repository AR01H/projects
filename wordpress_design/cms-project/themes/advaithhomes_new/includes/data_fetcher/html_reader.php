<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/data_reader_base.php';

/**
 * ADN_HTML_Reader - reads HTML fragments from data/html/{name}.html.
 *
 *   get($name)  - raw markup (TRUSTED theme content - echo as-is)
 *   safe($name) - markup filtered through wp_kses_post (use for any markup
 *                 that may include user/editor input)
 *   exists($name)
 */
class ADN_HTML_Reader extends ADN_Data_Reader {

	/**
	 * Raw HTML string. '' when absent.
	 *
	 * NOTE: this returns markup unescaped. Only store theme-authored files in
	 * data/html/. For anything less trusted, use safe() instead.
	 */
	public static function get( $name ) {
		$path = self::resolve( 'html', $name, 'html' );
		if ( '' === $path ) {
			return '';
		}
		$content = file_get_contents( $path );
		return false === $content ? '' : $content;
	}

	/**
	 * HTML string filtered through wp_kses_post (safe to echo). '' when absent.
	 */
	public static function safe( $name ) {
		$html = self::get( $name );
		return '' === $html ? '' : wp_kses_post( $html );
	}

	/** True when data/html/{name}.html exists. */
	public static function exists( $name ) {
		return '' !== self::resolve( 'html', $name, 'html' );
	}
}
