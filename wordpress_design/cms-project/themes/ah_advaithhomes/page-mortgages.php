<?php
/**
 * Template Name: Mortgages Page
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/mortgages.php';

get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Finance & Mortgages',
	'title'      => 'Understand UK',
	'title_em'   => 'Mortgages',
	'desc'       => 'Independent guides on mortgage rules, eligibility, rates, and the lending process - written to help you borrow with confidence.',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'Mortgages', '' ] ],
] );
get_template_part( 'components/mortgages/article-grid', null, $data );
get_template_part( 'components/cta-section', null, [] );
get_footer();
