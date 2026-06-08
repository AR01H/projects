<?php
defined( 'ABSPATH' ) || exit;
$all_faqs    = ah_get_faqs( 100 );
$topic_icons = [
	'buying'  => '🏠',
	'finance' => '💰',
	'legal'   => '⚖️',
	'process' => '📋',
	'contact' => '📞',
	'general' => '❓',
];
$grouped = [];
foreach ( $all_faqs as $faq ) {
	$topic             = $faq->topic ?? 'General';
	$grouped[ $topic ][] = $faq;
}
return [
	'grouped'     => $grouped,
	'topic_icons' => $topic_icons,
];
