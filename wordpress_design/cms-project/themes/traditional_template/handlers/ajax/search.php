<?php
/**
 * handlers/ajax/search.php - Live search for the header search box.
 *
 * Registered in config/ajax.php as 'search_posts'. Nonce already verified
 * by the dispatcher. Returns plain data; the JS renders it with
 * textContent so nothing needs HTML-escaping twice.
 *
 * JS side (assets/js/main.js):
 *   NT.ajax( 'search_posts', { q: 'term' } )
 */

defined( 'ABSPATH' ) || exit;

function nt_ajax_search_posts() {
	$q = sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) );
	if ( mb_strlen( $q ) < 2 ) {
		wp_send_json_success( array( 'results' => array() ) );
	}

	$query = new WP_Query( array(
		'post_type'           => array( 'post', 'page' ),
		'post_status'         => 'publish',
		's'                   => $q,
		'posts_per_page'      => 6,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );

	$results = array();
	foreach ( $query->posts as $p ) {
		$results[] = array(
			'title' => get_the_title( $p ),
			'url'   => get_permalink( $p ),
			'type'  => $p->post_type,
			'date'  => get_the_date( '', $p ),
		);
	}

	wp_send_json_success( array( 'results' => $results ) );
}
