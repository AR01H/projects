<?php
/**
 * Template Name: All News
 *
 * Listing:  /allnews/
 * Detail:   /allnews/?item=ID
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/allnews.php';

if ( $data['view'] === 'detail' ) {
	get_template_part( 'components/page-header', null, [
		'eyebrow'    => esc_html( $data['s_cat'] ),
		'title'      => '',
		'title_em'   => esc_html( $data['s_title'] ),
		'desc'       => $data['s_date'],
		'breadcrumb' => [
			[ 'Home',     home_url( '/' ) ],
			[ 'All News', esc_url( $data['base_url'] ) ],
			[ esc_html( wp_trim_words( $data['s_title'], 6, '…' ) ), '' ],
		],
	] );
	get_template_part( 'components/allnews/detail-view', null, $data );
} else {
	get_template_part( 'components/page-header', null, [
		'eyebrow'    => TXT_STAY_INFORMED,
		'title'      => TXT_ALL,
		'title_em'   => TXT_NEWS,
		'desc'       => TXT_MARKET_UPDATES_PROPERTY_INSIGHTS_AND_BUYING_TIPS_E,
		'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'All News', '' ] ],
	] );
	get_template_part( 'components/allnews/listing-view', null, $data );
}

get_template_part( 'components/cta-section', null, [] );
get_template_part( 'components/scroll-to-top' );
get_footer();
