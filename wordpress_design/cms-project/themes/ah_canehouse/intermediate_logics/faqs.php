<?php
defined( 'ABSPATH' ) || exit;
$faqs    = ch_get_faqs( '', 200 );
$grouped = [];
foreach ( $faqs as $faq ) {
	$faq   = (array) $faq;
	$topic = $faq['topic'] ?? 'General';
	if ( $topic === '' ) $topic = 'General';
	$grouped[ $topic ][] = $faq;
}
return [
	'grouped'  => $grouped,
	'settings' => ch_get_settings(),
	'hero'     => CH_Shared_Data::section_heading( 'page_hero_faqs' ),
];
