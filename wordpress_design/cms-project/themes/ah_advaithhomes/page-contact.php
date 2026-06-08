<?php
/**
 * Template Name: Contact Page
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/contact.php';

get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Get in Touch',
	'title'      => 'Talk to a',
	'title_em'   => "Buyer's Agent Today",
	'desc'       => 'Book a free, no-obligation 30-minute consultation. We\'ll listen to your brief, explain how we work, and tell you honestly whether we\'re the right fit.',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'Contact', '' ] ],
] );

get_template_part( 'components/contact/form-layout', null, [
	'settings'   => $data['settings'],
	'preset_enq' => $data['preset_enq'],
] );
get_template_part( 'components/contact/contact-faq', null, [ 'faqs' => $data['faqs'] ] );
get_footer();
