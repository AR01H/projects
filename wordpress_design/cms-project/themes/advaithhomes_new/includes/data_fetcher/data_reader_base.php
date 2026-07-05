<?php
defined( 'ABSPATH' ) || exit;

/**
 * ADN_Data_Reader
 *
 * Shared base for the CSV / JSON / HTML readers. Its only job is to turn a
 * short data name (e.g. "faqs") into a safe absolute path inside data/{type}/.
 *
 * Security: the name is stripped to [a-z0-9_-] and the resolved path is
 * realpath-contained inside the data folder, so a value like "../../wp-config"
 * can never escape the data directory (path-traversal proof).
 */
abstract class ADN_Data_Reader {

	/**
	 * @param string $subdir data sub-folder: 'csv' | 'json' | 'html' | 'pdfs'
	 * @param string $name   bare file name without extension
	 * @param string $ext    file extension without the dot
	 * @return string Absolute path, or '' when the name is unsafe / file missing.
	 */

	protected static function resolve( $subdir, $name, $ext ) {
		// If a custom data folder is defined, prepend it to the subdirectory path
		$custom = ( defined( 'DATA_FILES' ) && DATA_FILES ) ? trim( DATA_FILES, '/' ) . '/' : '';
		$subdir = $custom . ltrim( $subdir, '/' );

		$name = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $name );
		if ( '' === $name ) {
			return '';
		}

		$base_dir = ADN_THEME_DIR . '/data/' . $subdir;
		$base = realpath( $base_dir );
		if ( ! $base ) {
			return '';
		}

		$path = realpath( $base . '/' . $name . '.' . $ext );
		if ( ! $path || 0 !== stripos( $path, $base ) || ! is_file( $path ) ) {
			return '';
		}

		return $path;
	}

	/**
	 * Public URL for a data file (used for PDFs / downloads). '' when missing.
	 */
	protected static function resolve_url( $subdir, $name, $ext ) {
		$custom = ( defined( 'DATA_FILES' ) && DATA_FILES ) ? trim( DATA_FILES, '/' ) . '/' : '';
		$subdir = $custom . ltrim( $subdir, '/' );

		$name = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $name );
		if ( '' === $name || ! self::resolve( $subdir, $name, $ext ) ) {
			return '';
		}
		return ADN_THEME_URI . '/data/' . $subdir . '/' . $name . '.' . $ext;
	}


}
