<?php
defined( 'ABSPATH' ) || exit;

/**
 * Single Resource Shortcode — [ah_resource id="1"]
 * Extracted from ah-cms.php inline functions.
 */
class AH_Resource_Shortcode {

	public static function render( $atts ): string {
		$atts = shortcode_atts( [
			'id'         => 0,
			'show_title' => '0',
			'show_desc'  => '0',
			'class'      => '',
		], $atts, 'ah_resource' );

		$id = (int) $atts['id'];
		if ( ! $id || ! class_exists( 'AH_Resources_Model' ) ) return '';

		$item = ( new AH_Resources_Model() )->find( $id );
		if ( ! $item || $item->status !== 'active' ) return '';

		return AH_Resources_Model::render_resource( $item, [
			'show_title' => ! empty( $atts['show_title'] ) && $atts['show_title'] !== '0',
			'show_desc'  => ! empty( $atts['show_desc'] ) && $atts['show_desc'] !== '0',
			'class'      => sanitize_html_class( $atts['class'] ),
		] );
	}
}
