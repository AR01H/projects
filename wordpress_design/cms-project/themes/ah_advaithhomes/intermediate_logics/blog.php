<?php
defined( 'ABSPATH' ) || exit;
$active_cat = sanitize_text_field( $_GET['category'] ?? '' );
$paged      = max( 1, get_query_var( 'paged' ) ?: ( get_query_var( 'page' ) ?: 1 ) );

$q_args = [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
];
if ( $active_cat ) {
	$term = get_term_by( 'slug', $active_cat, 'category' );
	if ( $term ) {
		$q_args['cat'] = $term->term_id;
	}
}

return [
	'blog_query' => new WP_Query( $q_args ),
	'wp_cats'    => get_categories( [ 'hide_empty' => true ] ),
	'active_cat' => $active_cat,
	'paged'      => $paged,
];
