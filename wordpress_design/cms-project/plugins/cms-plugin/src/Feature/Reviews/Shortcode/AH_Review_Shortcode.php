<?php
defined( 'ABSPATH' ) || exit;

/**
 * Single Review Shortcode — [ah_review id="1"]
 * Renders nothing if the id is missing/unknown, or the review's Status is Inactive.
 */
class AH_Review_Shortcode {

	public static function render( $atts ): string {
		$atts = shortcode_atts( [
			'id' => 0,
		], $atts, 'ah_review' );

		$id = (int) $atts['id'];
		if ( ! $id || ! class_exists( 'AH_Reviews_Model' ) ) return '';

		$item = ( new AH_Reviews_Model() )->find( $id );
		if ( ! $item || 'active' !== $item->status ) return '';

		return AH_Reviews_Model::render_review( $item );
	}
}
