<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/csv_reader.php';
require_once __DIR__ . '/json_reader.php';
require_once __DIR__ . '/html_reader.php';

/**
 * ADN_Real_Loader
 *
 * One front door for reading the theme's flat-file data. Mirrors the
 * CH_Real_Loader API (csv / kv / json / kv_json) and adds html() + pdf_url().
 *
 * Files live in:
 *   data/csv/{name}.csv
 *   data/json/{name}.json
 *   data/html/{name}.html
 *   data/pdfs/{name}.pdf
 *
 * Usage:
 *   $faqs   = ADN_Real_Loader::csv( 'faqs' );            // rows
 *   $config = ADN_Real_Loader::kv( 'home-settings' );    // key => value (CSV)
 *   $buying = ADN_Real_Loader::json( 'buying_details' );  // decoded JSON
 *   $intro  = ADN_Real_Loader::html( 'about_intro' );     // HTML fragment
 *   $broch  = ADN_Real_Loader::pdf_url( 'brochure' );     // public URL
 */
class ADN_Real_Loader {

	/** Row-style CSV → array of associative rows. */
	public static function csv( $name ) {
		return ADN_CSV_Reader::rows( $name );
	}

	/** Two-column "key,value" CSV → [ key => value ]. */
	public static function kv( $name ) {
		return ADN_CSV_Reader::kv( $name );
	}

	/** JSON file → associative array (array or object). */
	public static function json( $name ) {
		return ADN_JSON_Reader::data( $name );
	}

	/** JSON object → [ key => value ]. */
	public static function kv_json( $name ) {
		return ADN_JSON_Reader::kv( $name );
	}

	/** Raw HTML fragment (trusted theme content). */
	public static function html( $name ) {
		return ADN_HTML_Reader::get( $name );
	}

	/** wp_kses_post-filtered HTML fragment. */
	public static function html_safe( $name ) {
		return ADN_HTML_Reader::safe( $name );
	}

	/** Public URL to a PDF in data/pdfs/, or '' when missing. */
	public static function pdf_url( $name ) {
		return ADN_Data_Reader_PDF::url( $name );
	}
}

/**
 * Tiny concrete reader so pdf_url() can reuse the base's safe URL resolver
 * (the base class is abstract).
 */
class ADN_Data_Reader_PDF extends ADN_Data_Reader {
	public static function url( $name ) {
		return self::resolve_url( 'pdfs', $name, 'pdf' );
	}
}
