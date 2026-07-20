<?php
/**
 * handlers/rest/posts.php - Paged post listing for the News page.
 *
 * Registered in config/rest.php as route 'posts'. Args (page, per_page,
 * search) are already validated/sanitized by the args schema there.
 *
 * JS side (assets/js/pages/news.js):
 *   NT.rest( 'posts', { page: 2, search: 'x' } )
 */

defined( 'ABSPATH' ) || exit;

function nt_rest_posts( WP_REST_Request $request ) {
	$page     = max( 1, (int) $request['page'] );
	$per_page = min( 24, max( 1, (int) $request['per_page'] ) );
	$search   = (string) $request['search'];

	$query = new WP_Query( array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'paged'               => $page,
		'posts_per_page'      => $per_page,
		's'                   => $search,
		'ignore_sticky_posts' => true,
	) );

	$items = array();
	foreach ( $query->posts as $p ) {
		$items[] = array(
			'id'      => (int) $p->ID,
			'title'   => get_the_title( $p ),
			'url'     => get_permalink( $p ),
			'date'    => get_the_date( '', $p ),
			'excerpt' => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $p ) ), 20, '…' ),
			'thumb'   => (string) get_the_post_thumbnail_url( $p, 'nt-card' ),
		);
	}

	return rest_ensure_response( array(
		'items'       => $items,
		'page'        => $page,
		'total'       => (int) $query->found_posts,
		'total_pages' => (int) $query->max_num_pages,
	) );
}
