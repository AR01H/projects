<?php
/**
 * intermediate/page_ask_expert_logical.php
 *
 * Builds the context array for pages/page-ask-expert.php.
 *
 * Priority: live DB experts (AH_Expert_DB) → JSON fallback when DB is empty.
 * The JSON data is still used for hero, stats, sidebar and cant_find_cta regardless.
 */

defined( 'ABSPATH' ) || exit;

function adn_ask_expert_get_context() {
	$data   = function_exists( 'adn_service_ask_expert_data' ) ? adn_service_ask_expert_data() : array();
	$chrome = function_exists( 'adn_service_site_chrome' )     ? adn_service_site_chrome()     : array();

	/* ── Try to load live experts from the DB ─────────────────────── */
	$db_experts  = array();
	$use_db      = false;
	if ( class_exists( 'AH_Expert_DB' ) ) {
		$db_rows = AH_Expert_DB::get_all( 'active' );
		if ( ! empty( $db_rows ) ) {
			$use_db = true;
			foreach ( $db_rows as $row ) {
				$photo_id  = isset( $row['photo_id'] ) ? (int) $row['photo_id'] : 0;
				$photo_url = ( $photo_id > 0 ) ? wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '';
				if ( ! $photo_url ) { $photo_url = ''; }

				$bullets_raw = isset( $row['bullets'] ) ? $row['bullets'] : '';
				$bullets     = array();
				if ( '' !== $bullets_raw ) {
					$dec = json_decode( $bullets_raw, true );
					if ( is_array( $dec ) ) { $bullets = $dec; }
				}

				$slug    = isset( $row['expert_slug'] ) ? (string) $row['expert_slug'] : '';
				$profile_url = $slug ? home_url( '/?ah_expert=' . rawurlencode( $slug ) ) : home_url( '/ask-an-expert/' );

				$db_experts[] = array(
					'slug'          => $slug,
					'photo_url'     => $photo_url,
					'avatar'        => '👤',
					'name'          => isset( $row['name'] )          ? (string) $row['name']          : '',
					'title'         => isset( $row['title'] )         ? (string) $row['title']         : '',
					'category'      => isset( $row['category'] )      ? (string) $row['category']      : '',
					'rating'        => isset( $row['rating'] )        ? (float)  $row['rating']        : 0.0,
					'reviews_count' => isset( $row['reviews_count'] ) ? (int)    $row['reviews_count'] : 0,
					'reviews'       => isset( $row['reviews_count'] ) ? (int)    $row['reviews_count'] : 0,
					'description'   => isset( $row['bio'] )           ? (string) $row['bio']           : '',
					'location'      => isset( $row['location'] )      ? (string) $row['location']      : '',
					'phone'         => isset( $row['phone'] )         ? (string) $row['phone']         : '',
					'email'         => isset( $row['email'] )         ? (string) $row['email']         : '',
					'tags'          => array_slice( $bullets, 0, 3 ),
					'bullets'       => $bullets,
					'url'           => $profile_url,
				);
			}
		}
	}

	/* ── Experts: DB or JSON fallback ─────────────────────────────── */
	if ( $use_db ) {
		$experts = $db_experts;
	} else {
		// JSON experts: map to consistent shape (no slug/photo_url).
		$json_experts = isset( $data['experts'] ) ? (array) $data['experts'] : array();
		$experts      = array();
		foreach ( $json_experts as $_e ) {
			$_e          = (array) $_e;
			$experts[] = array(
				'slug'          => '',
				'photo_url'     => '',
				'avatar'        => isset( $_e['avatar'] ) ? (string) $_e['avatar'] : '👤',
				'name'          => isset( $_e['name'] )    ? (string) $_e['name']    : '',
				'title'         => isset( $_e['title'] )   ? (string) $_e['title']   : '',
				'category'      => isset( $_e['category'] ) ? (string) $_e['category'] : '',
				'rating'        => isset( $_e['rating'] )  ? (float) $_e['rating']   : 0.0,
				'reviews_count' => isset( $_e['reviews'] ) ? (int) $_e['reviews']   : 0,
				'reviews'       => isset( $_e['reviews'] ) ? (int) $_e['reviews']   : 0,
				'description'   => isset( $_e['description'] ) ? (string) $_e['description'] : '',
				'location'      => isset( $_e['location'] ) ? (string) $_e['location'] : '',
				'phone'         => '',
				'email'         => '',
				'tags'          => isset( $_e['tags'] ) && is_array( $_e['tags'] ) ? $_e['tags'] : array(),
				'bullets'       => isset( $_e['tags'] ) && is_array( $_e['tags'] ) ? $_e['tags'] : array(),
				'url'           => isset( $_e['url'] ) ? (string) $_e['url'] : '#',
			);
		}
	}

	/* ── Categories: combine JSON base with DB-derived keys ────────── */
	$base_cats = isset( $data['categories'] ) ? (array) $data['categories'] : array();

	if ( $use_db ) {
		// Derive distinct categories from DB experts.
		$db_cat_keys = array();
		foreach ( $db_experts as $_de ) {
			$_ck = isset( $_de['category'] ) ? (string) $_de['category'] : '';
			if ( '' !== $_ck ) { $db_cat_keys[ $_ck ] = true; }
		}

		// Build merged categories: always start with "All".
		$merged_cats = array(
			array( 'key' => 'all', 'label' => __( 'All Experts', ADN_TEXT_DOMAIN ), 'active' => true ),
		);
		// Add DB categories not already in the base JSON list.
		$existing_keys = array( 'all' );
		foreach ( $base_cats as $_bc ) {
			$_bc  = (array) $_bc;
			$_bck = isset( $_bc['key'] ) ? (string) $_bc['key'] : '';
			if ( 'all' === $_bck ) { continue; }
			if ( isset( $db_cat_keys[ $_bck ] ) || in_array( $_bck, array_keys( $db_cat_keys ), true ) ) {
				$merged_cats[]    = $_bc;
				$existing_keys[]  = $_bck;
			}
		}
		// Add any DB categories that weren't in JSON.
		foreach ( array_keys( $db_cat_keys ) as $_dck ) {
			if ( ! in_array( $_dck, $existing_keys, true ) ) {
				$merged_cats[] = array(
					'key'   => $_dck,
					'label' => ucwords( str_replace( array( '-', '_' ), ' ', $_dck ) ),
				);
			}
		}
		$categories = $merged_cats;
	} else {
		$categories = $base_cats;
	}

	/* ── Contact nonce for AJAX form ──────────────────────────────── */
	$ajax_url      = admin_url( 'admin-ajax.php' );
	$contact_nonce = wp_create_nonce( 'adn_expert_contact' );

	return array(
		'meta'          => isset( $data['meta'] )          ? (array) $data['meta']          : array(),
		'breadcrumb'    => isset( $data['breadcrumb'] )    ? (array) $data['breadcrumb']    : array(),
		'hero'          => isset( $data['hero'] )          ? (array) $data['hero']          : array(),
		'stats'         => isset( $data['stats'] )         ? (array) $data['stats']         : array(),
		'categories'    => $categories,
		'experts'       => $experts,
		'sidebar'       => isset( $data['sidebar'] )       ? (array) $data['sidebar']       : array(),
		'cant_find_cta' => isset( $data['cant_find_cta'] ) ? (array) $data['cant_find_cta'] : array(),
		'chrome'        => $chrome,
		'ajax_url'      => $ajax_url,
		'contact_nonce' => $contact_nonce,
	);
}
