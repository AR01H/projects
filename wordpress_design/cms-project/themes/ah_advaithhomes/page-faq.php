<?php
/**
 * Template Name: FAQ Page
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/faq.php';

get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Frequently Asked Questions',
	'title'      => 'Your Questions,',
	'title_em'   => 'Answered Honestly',
	'desc'       => 'Everything you need to know about working with a buyer\'s agent - how we work, what we cost, and what you can expect at every step.',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'FAQ', '' ] ],
] );
get_template_part( 'components/faq/faq-groups', null, $data );
get_template_part( 'components/cta-section', null, [] );
get_footer();
