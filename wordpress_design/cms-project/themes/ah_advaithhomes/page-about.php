<?php
/**
 * Template Name: About Page
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/about.php';

get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'About ' . CLIENT_FULL_TITLE,
	'title'      => 'The UK\'s Buyer\'s Agent -',
	'title_em'   => 'Working Exclusively for You',
	'desc'       => 'We exist to level the playing field. Sellers have agents negotiating for them - so should you. ' . CLIENT_FULL_TITLE . ' is a buyer-only agency: we never list properties, never work for sellers, and never take referral fees from developers. Our only job is to help you buy smarter.',
	'badge'      => '',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'About', '' ] ],
] );

get_template_part( 'components/about/stats-strip', null, [ 'stats' => $data['stats'] ] );
get_template_part( 'components/about/story' );
get_template_part( 'components/team-section' );
get_template_part( 'components/testimonials' );
get_template_part( 'components/cta-section', null, [] );
get_footer();
