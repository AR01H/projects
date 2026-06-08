<?php
defined( 'ABSPATH' ) || exit;
$cat_slug = 'finance-mortgages';
$term     = get_term_by( 'slug', $cat_slug, 'category' );
$paged    = max( 1, absint( $_GET['pg'] ?? 1 ) );
$q        = $term ? new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'cat'            => $term->term_id,
	'posts_per_page' => 12,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
] ) : null;

return [
	'q'        => $q,
	'featured' => ( $q && $q->have_posts() ) ? $q->posts[0] : null,
	'paged'    => $paged,
	'cat_slug' => $cat_slug,
];
