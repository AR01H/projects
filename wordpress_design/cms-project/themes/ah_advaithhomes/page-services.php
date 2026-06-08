<?php
/**
 * Template Name: Services Page
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/services.php';

get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'What We Do',
	'title'      => 'Full-Service<br><em>Buyer Representation</em>',
	'title_em'   => 'Services',
	'desc'       => 'From your first search to completion day, we handle every step of the buying process - so you make the right decision at the right price, every time.',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'Services', '' ] ],
] );

get_template_part( 'components/services/services-list', null, [
	'services'       => $data['services'],
	'service_points' => $data['service_points'],
] );
get_template_part( 'components/services/stats-strip', null, [ 'stats' => $data['stats'] ] );
get_template_part( 'components/services/process',     null, [ 'steps' => $data['steps'] ] );
get_template_part( 'components/faq-section' );
get_template_part( 'components/testimonials' );
get_template_part( 'components/cta-section', null, [] );
get_footer();
