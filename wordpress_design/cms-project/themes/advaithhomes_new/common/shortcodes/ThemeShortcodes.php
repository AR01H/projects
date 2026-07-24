<?php
/**
 * Theme Shortcodes
 *
 * All shortcode callback functions live here.
 *
 * @package Adn\Theme\Common\Shortcodes
 */
defined( 'ABSPATH' ) || exit;

/**
 * Return calculator cards that are explicitly assigned to a parent term slug.
 */
function adn_get_parent_term_calculator_cards( $parent_slug, $limit = 0 ) {
	$parent_slug = sanitize_key( (string) $parent_slug );
	if ( '' === $parent_slug || ! function_exists( 'adn_calculators' ) ) {
		return array();
	}

	$all_tools = adn_calculators();
	$meta_all  = get_option( 'adn_calculators_meta', array() );
	$items     = array();
	$parent_slug_lc = strtolower( $parent_slug );

	foreach ( $all_tools as $ckey => $creg ) {
		$cmeta = isset( $meta_all[ $ckey ] ) && is_array( $meta_all[ $ckey ] ) ? $meta_all[ $ckey ] : array();
		if ( array_key_exists( 'enabled', $cmeta ) && empty( $cmeta['enabled'] ) ) {
			continue;
		}
		if ( ! empty( $cmeta['hidden_from_listing'] ) ) {
			continue;
		}

		$_pt_list = ! empty( $cmeta['parent_terms'] ) && is_array( $cmeta['parent_terms'] ) ? $cmeta['parent_terms'] : array();
		if ( empty( $_pt_list ) ) {
			continue;
		}

		$_pt_lc = array_map( 'strtolower', array_map( 'trim', $_pt_list ) );
		if ( ! in_array( $parent_slug_lc, $_pt_lc, true ) ) {
			continue;
		}

		$thumb = '';
		if ( ! empty( $cmeta['thumbnail_id'] ) ) {
			$t = wp_get_attachment_image_url( (int) $cmeta['thumbnail_id'], 'thumbnail' );
			$thumb = $t ? (string) $t : '';
		}

		$desc = '';
		if ( ! empty( $cmeta['desc'] ) ) {
			$desc = wp_strip_all_tags( (string) $cmeta['desc'] );
		} elseif ( ! empty( $cmeta['description'] ) ) {
			$desc = wp_strip_all_tags( (string) $cmeta['description'] );
		} elseif ( ! empty( $creg['description'] ) ) {
			$desc = wp_strip_all_tags( (string) $creg['description'] );
		}
		if ( $desc !== '' ) {
			$desc = wp_trim_words( $desc, 12 );
		}

		$items[] = array(
			'key'       => sanitize_key( $ckey ),
			'icon'      => ! empty( $cmeta['icon'] ) ? (string) $cmeta['icon'] : ( ! empty( $creg['icon'] ) ? (string) $creg['icon'] : '🧮' ),
			'label'     => ! empty( $cmeta['label'] ) ? (string) $cmeta['label'] : ( ! empty( $creg['title'] ) ? (string) $creg['title'] : $ckey ),
			'desc'      => $desc,
			'url'       => ! empty( $cmeta['card_url'] ) ? (string) $cmeta['card_url'] : adn_calc_page_url( $ckey ),
			'thumbnail' => $thumb,
			'highlight' => ! empty( $cmeta['highlight'] ) ? (string) $cmeta['highlight'] : '',
		);

		if ( $limit > 0 && count( $items ) >= $limit ) {
			break;
		}
	}

	return $items;
}

/**
 * [adn_cat_calculators slug="buying"]
 * Renders a grid of calculator cards for the parent term only.
 */
function adn_shortcode_cat_calculators( $atts ) {
	$atts = shortcode_atts( array( 'slug' => '' ), $atts, 'adn_cat_calculators' );
	$slug = sanitize_key( $atts['slug'] );
	if ( ! $slug ) {
		return '';
	}

	$items = adn_get_parent_term_calculator_cards( $slug );
	if ( empty( $items ) ) {
		return '';
	}

	$heading = '';
	ob_start();
	echo '<div class="tool-grid tool-grid--7col">';
	foreach ( $items as $card ) {
		adn_component( 'cards/tool_card', array( 'card' => array(
			'icon' => $card['icon'],
			'name' => $card['label'],
			'desc' => $card['desc'] ?? '',
			'url'  => $card['url'],
		) ) );
	}
	echo '</div>';
	return ob_get_clean();
}

/**
 * [adn_cookie_preferences]
 * Embeds the cookie preferences toggle form inline in page content.
 */
function adn_shortcode_cookie_preferences( $atts ) {
	ob_start();
	?>
	<div class="adn-cookie-prefs-embed">
		<div data-adn-cookie-prefs="embed">
			<noscript><?php esc_html_e( 'Enable JavaScript to manage your cookie preferences.', ADN_TEXT_DOMAIN ); ?></noscript>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
