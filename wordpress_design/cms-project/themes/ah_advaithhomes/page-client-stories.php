<?php
/**
 * Template Name: Client Stories
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/client-stories.php';

get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Client Stories',
	'title'      => 'Real Results for',
	'title_em'   => 'Real Buyers',
	'desc'       => sprintf(
		"We let our clients do the talking. Here's what over %s buyers have said about working with %s.",
		esc_html( $data['client_stat'] ),
		esc_html( CLIENT_FULL_TITLE )
	),
	'badge'      => '',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'Client Stories', '' ] ],
] );
get_template_part( 'components/client-stories/rating-summary', null, $data );
get_template_part( 'components/carousels/review-carousel' );
get_template_part( 'components/client-stories/reviews-grid', null, [ 'reviews' => $data['reviews'] ] );
get_template_part( 'components/cta-section', null, [] );
get_footer();
