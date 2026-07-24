<?php
defined( 'ABSPATH' ) || exit;

/**
 * Static Page Shortcode — [ah_static_page slug="my-page"]
 * Extracted from ah-cms.php inline functions.
 */
class AH_StaticPage_Shortcode {

	public static function render( $atts ): string {
		$atts = shortcode_atts( [ 'slug' => '' ], $atts, 'ah_static_page' );
		$slug = sanitize_file_name( trim( (string) $atts['slug'] ) );
		if ( '' === $slug ) return '';
		if ( ! class_exists( 'AH_Static_Pages_Model' ) ) return '';
		$html = ( new AH_Static_Pages_Model() )->get_html( $slug );
		if ( '' === $html ) return '';
		return '<div class="ah-static-page-embed" data-slug="' . esc_attr( $slug ) . '">' . $html . '</div>';
	}
}
