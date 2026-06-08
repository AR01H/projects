<?php
/**
 * Template Name: News Listing
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/news.php';

get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Latest Updates',
	'title'      => 'News &amp;',
	'title_em'   => 'Announcements',
	'desc'       => 'Stay up to date with the latest news, market updates, and announcements from ' . CLIENT_FULL_TITLE . '.',
	'badge'      => $data['all_count'] ? $data['all_count'] . ' items' : '',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'News', '' ] ],
] );
get_template_part( 'components/news/news-list', null, $data );
get_template_part( 'components/cta-section', null, [] );
get_footer();
