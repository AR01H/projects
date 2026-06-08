<?php
/**
 * Template Name: Blog Listing
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/blog.php';

get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Insights & Expertise',
	'title'      => 'The ' . CLIENT_FULL_TITLE,
	'title_em'   => 'Blog',
	'desc'       => 'Practical advice from buyer\'s agents - market insights, step-by-step guides, and everything you need to buy smarter.',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'Blog', '' ] ],
] );
get_template_part( 'components/blog/category-filter', null, [
	'wp_cats'    => $data['wp_cats'],
	'active_cat' => $data['active_cat'],
] );
get_template_part( 'components/blog/post-grid', null, [
	'blog_query' => $data['blog_query'],
	'active_cat' => $data['active_cat'],
	'paged'      => $data['paged'],
] );
get_template_part( 'components/blog/newsletter' );
get_footer();
