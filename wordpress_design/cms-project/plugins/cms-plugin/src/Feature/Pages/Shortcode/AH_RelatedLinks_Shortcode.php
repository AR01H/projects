<?php
defined( 'ABSPATH' ) || exit;

/**
 * Related Links Shortcode — [ah_related_links]
 * Extracted from ah-cms.php inline functions.
 */
class AH_RelatedLinks_Shortcode {

	public static function render( $atts ): string {
		$atts = shortcode_atts( [
			'id'        => 0,
			'object'    => 'wp_post',
			'container' => '',
			'title'     => '',
			'class'     => '',
		], $atts, 'ah_related_links' );

		$object_id = (int) $atts['id'] ?: (int) get_the_ID();
		if ( ! $object_id ) return '';

		$groups = self::getRelatedLinks( $object_id, sanitize_key( $atts['object'] ) );
		if ( empty( $groups ) ) return '';

		$filter = trim( (string) $atts['container'] );

		ob_start();
		echo '<div class="ah-related-links ' . esc_attr( $atts['class'] ) . '">';
		if ( $atts['title'] !== '' ) {
			echo '<h3 class="ah-related-links__title">' . esc_html( $atts['title'] ) . '</h3>';
		}
		foreach ( $groups as $group ) {
			if ( $filter !== '' && strcasecmp( $group['container'], $filter ) !== 0 ) continue;
			echo '<div class="ah-related-links__group" data-container="' . esc_attr( $group['container'] ) . '">';
			echo '<h4 class="ah-related-links__heading">' . esc_html( $group['container'] ) . '</h4>';
			echo '<ul class="ah-related-links__list">';
			foreach ( $group['items'] as $item ) {
				$rel = ( $item['target'] === '_blank' ) ? ' rel="noopener noreferrer"' : '';
				printf(
					'<li class="ah-related-links__item ah-related-links__item--%1$s">'
						. '<a href="%2$s" target="%3$s"%4$s>'
						. '<span class="ah-related-links__icon" aria-hidden="true">%5$s</span>'
						. '<span class="ah-related-links__label">%6$s</span>'
						. '</a></li>',
					esc_attr( $item['link_type'] ),
					esc_url( $item['url'] ),
					esc_attr( $item['target'] ),
					$rel,
					esc_html( $item['icon'] ),
					esc_html( $item['label'] )
				);
			}
			echo '</ul></div>';
		}
		echo '</div>';
		return ob_get_clean();
	}

	public static function getRelatedLinks( int $object_id = 0, string $object_type = 'wp_post' ): array {
		if ( ! class_exists( 'AH_Related_Links_Model' ) ) return [];
		$object_id = $object_id ?: (int) get_the_ID();
		if ( ! $object_id ) return [];
		return ( new AH_Related_Links_Model() )->get_grouped( $object_type, $object_id );
	}
}
