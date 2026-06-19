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

	/* ── Cover banner image ─────────────────────────────────────── */
	$banner_image_id  = isset( $expert['banner_image_id'] ) ? (int) $expert['banner_image_id'] : 0;
	$banner_image_url = '';
	if ( $banner_image_id > 0 ) {
		$_bu = wp_get_attachment_image_url( $banner_image_id, 'full' );
		if ( $_bu ) { $banner_image_url = (string) $_bu; }
	}

	/* ── Profile banner stats → remapped for point_marque (is_icon mode) ── */
	// point_marque is_icon mode uses: icon · label (bold) · note (subtitle)
	// We store:                        icon · value       · label
	$banner_items = array();
	if ( ! empty( $expert['banner_json'] ) ) {
		$_bd = json_decode( $expert['banner_json'], true );
		if ( is_array( $_bd ) ) {
			foreach ( $_bd as $_bi ) {
				if ( ! is_array( $_bi ) ) { continue; }
				$_bv = isset( $_bi['value'] ) ? (string) $_bi['value'] : '';
				$_bl = isset( $_bi['label'] ) ? (string) $_bi['label'] : '';
				if ( '' === $_bv && '' === $_bl ) { continue; }
				$banner_items[] = array(
					'icon'  => isset( $_bi['icon'] ) ? (string) $_bi['icon'] : '',
					'label' => $_bv,  // bold main text in marquee
					'note'  => $_bl,  // subtitle in marquee
				);
			}
		}
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
		'bullets'          => $bullets,
		'banner_image_url' => $banner_image_url,
		'banner_items'     => $banner_items,
		'client_images'    => $client_images,
		'mega_html'     => isset( $expert['mega_html'] )     ? (string) $expert['mega_html']     : '',
		'hero'          => array(
			'title'       => $name,
			'description' => $title,
			'bg_icon'     => '👤',
		),
		'breadcrumb'    => array(
			array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
			array( 'label' => SITE_EXPERT_LABEL,  'url' => home_url( SITE_EXPERT_URL ) ),
			array( 'label' => $name,                                    'url' => null ),
		),
		'chrome'        => $chrome,
		'contact_nonce' => wp_create_nonce( 'adn_expert_contact' ),
		'ajax_url'      => admin_url( 'admin-ajax.php' ),
	);
}
