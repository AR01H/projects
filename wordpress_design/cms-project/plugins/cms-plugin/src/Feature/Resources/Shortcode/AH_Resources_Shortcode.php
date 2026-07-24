<?php
defined( 'ABSPATH' ) || exit;

/**
 * Resources Grid Shortcode — [ah_resources context="category" type="youtube" limit="3"]
 * Extracted from ah-cms.php inline functions.
 */
class AH_Resources_Shortcode {

	public static function render( $atts ): string {
		$atts = shortcode_atts( [
			'context'    => '',
			'type'       => '',
			'limit'      => '0',
			'show_title' => '0',
			'show_desc'  => '0',
			'class'      => '',
			'columns'    => '2',
		], $atts, 'ah_resources' );

		if ( ! class_exists( 'AH_Resources_Model' ) ) return '';

		$items = ( new AH_Resources_Model() )->get_active(
			sanitize_key( $atts['context'] ),
			sanitize_key( $atts['type'] ),
			(int) $atts['limit']
		);

		if ( empty( $items ) ) return '';

		$opts = [
			'show_title' => ! empty( $atts['show_title'] ) && $atts['show_title'] !== '0',
			'show_desc'  => ! empty( $atts['show_desc'] ) && $atts['show_desc'] !== '0',
		];

		$cols     = max( 1, min( 4, (int) $atts['columns'] ) );
		$wrap_cls = 'ah-resources-grid ah-resources-grid--cols-' . $cols;
		if ( $atts['class'] ) {
			$wrap_cls .= ' ' . sanitize_html_class( $atts['class'] );
		}

		ob_start();
		echo '<div class="' . esc_attr( $wrap_cls ) . '" style="display:grid;grid-template-columns:repeat(' . esc_attr( (string) $cols ) . ',1fr);gap:20px;">';
		foreach ( $items as $item ) {
			echo AH_Resources_Model::render_resource( $item, $opts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
		return ob_get_clean();
	}
}
