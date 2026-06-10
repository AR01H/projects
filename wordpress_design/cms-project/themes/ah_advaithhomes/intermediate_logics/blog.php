<?php
/**
 * Blog / Insights data (mockup #4).
 * Filters by PARENT TERM group (?group=slug) using the shared helper, so the
 * tabs (All / Buying / Selling / …) show only that group's posts - the same
 * grouping used by the guides hub and the homepage audience cards.
 */
defined( 'ABSPATH' ) || exit;

$active_group = sanitize_title( $_GET['group'] ?? '' );
$paged        = max( 1, absint( $_GET['pg'] ?? ( get_query_var( 'paged' ) ?: 1 ) ) );

$q_args = [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 9,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
];

/* Restrict to the selected parent-term group's categories. */
$group_cat_ids = $active_group ? ah_parent_term_cat_ids( $active_group ) : [];
if ( $active_group ) {
	if ( $group_cat_ids ) {
		$q_args['category__in'] = $group_cat_ids;
	} else {
		$q_args['post__in'] = [ 0 ]; // unknown group → no results
	}
}

/* Featured insight (page 1 only): a flagged post within the active scope, else newest. */
$featured_post = null;
if ( 1 === $paged ) {
	$feat_args = [
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'meta_key'       => '_ah_is_featured',
		'meta_value'     => '1',
		'orderby'        => 'date',
		'order'          => 'DESC',
	];
	if ( $group_cat_ids ) {
		$feat_args['category__in'] = $group_cat_ids;
	}
	$feat = get_posts( $feat_args );
	if ( ! $feat ) {
		unset( $feat_args['meta_key'], $feat_args['meta_value'] );
		$feat = get_posts( $feat_args );
	}
	$featured_post = $feat ? $feat[0] : null;
	if ( $featured_post ) {
		$q_args['post__not_in'] = [ $featured_post->ID ];
	}
}

return [
	'blog_query'   => new WP_Query( $q_args ),
	'parent_terms' => ah_get_parent_terms(),
	'active_group' => $active_group,
	'featured'     => $featured_post,
	'paged'        => $paged,
	'base_url'     => get_permalink(),
];
