<?php
/**
 * intermediate/page_expert_single_logical.php
 *
 * Builds the context array for pages/page-expert-single.php.
 * Served via the template_redirect hook for ?ah_expert=SLUG.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build context for a single expert profile page.
 *
 * @param string $slug The expert_slug value from the URL.
 * @return array|null  Null if expert not found or inactive.
 */
function adn_expert_single_get_context( $slug ) {
	if ( ! class_exists( 'AH_Expert_DB' ) ) { return null; }

	$slug   = sanitize_key( $slug );
	$expert = AH_Expert_DB::get( $slug );
	if ( ! $expert || 'active' !== $expert['status'] ) { return null; }

	/* ── Photo ─────────────────────────────────────────────────── */
	$photo_id  = isset( $expert['photo_id'] ) ? (int) $expert['photo_id'] : 0;
	$photo_url = '';
	if ( $photo_id > 0 ) {
		$big = wp_get_attachment_image_url( $photo_id, 'large' );
		if ( $big ) { $photo_url = $big; }
	}

	/* ── Bullets ────────────────────────────────────────────────── */
	$bullets = array();
	if ( ! empty( $expert['bullets'] ) ) {
		$dec = json_decode( $expert['bullets'], true );
		if ( is_array( $dec ) ) { $bullets = $dec; }
	}

	/* ── Client images ──────────────────────────────────────────── */
	$client_images = array();
	if ( ! empty( $expert['client_images'] ) ) {
		$dec = json_decode( $expert['client_images'], true );
		if ( is_array( $dec ) ) {
			foreach ( $dec as $ci ) {
				if ( ! is_array( $ci ) ) { continue; }
				$ci_id  = isset( $ci['image_id'] ) ? (int) $ci['image_id'] : 0;
				$ci_url = '';
				if ( $ci_id > 0 ) {
					$u = wp_get_attachment_image_url( $ci_id, 'medium_large' );
					if ( $u ) { $ci_url = $u; }
				}
				$client_images[] = array(
					'url'     => $ci_url,
					'caption' => isset( $ci['caption'] ) ? (string) $ci['caption'] : '',
				);
			}
		}
	}

	/* ── Supporting data ────────────────────────────────────────── */
	$name         = isset( $expert['name'] )  ? (string) $expert['name']  : '';
	$title        = isset( $expert['title'] ) ? (string) $expert['title'] : '';
	$chrome       = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

	return array(
		'slug'          => $slug,
		'name'          => $name,
		'title'         => $title,
		'category'      => isset( $expert['category'] )      ? (string) $expert['category']      : '',
		'photo_url'     => $photo_url,
		'bio'           => isset( $expert['bio'] )           ? (string) $expert['bio']           : '',
		'rating'        => isset( $expert['rating'] )        ? (float)  $expert['rating']        : 0.0,
		'reviews_count' => isset( $expert['reviews_count'] ) ? (int)    $expert['reviews_count'] : 0,
		'location'      => isset( $expert['location'] )      ? (string) $expert['location']      : '',
		'phone'         => isset( $expert['phone'] )         ? (string) $expert['phone']         : '',
		'email'         => isset( $expert['email'] )         ? (string) $expert['email']         : '',
		'bullets'       => $bullets,
		'client_images' => $client_images,
		'mega_html'     => isset( $expert['mega_html'] )     ? (string) $expert['mega_html']     : '',
		'hero'          => array(
			'title'       => $name,
			'description' => $title,
			'bg_icon'     => '👤',
		),
		'breadcrumb'    => array(
			array( 'label' => __( 'Home', ADN_TEXT_DOMAIN ),           'url' => '/' ),
			array( 'label' => __( 'Ask an Expert', ADN_TEXT_DOMAIN ),  'url' => home_url( '/ask-an-expert/' ) ),
			array( 'label' => $name,                                    'url' => null ),
		),
		'chrome'        => $chrome,
		'contact_nonce' => wp_create_nonce( 'adn_expert_contact' ),
		'ajax_url'      => admin_url( 'admin-ajax.php' ),
	);
}
