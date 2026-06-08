<?php
defined( 'ABSPATH' ) || exit;
$settings      = ah_get_settings();
$home          = ah_get_home_settings();
$news_items    = ah_get_news_bar_items();
$trust_signals = ah_get_trust_signals();
$process_steps = ah_get_process_steps();
$site_stats    = ah_get_site_stats();
$services      = ah_get_services( 8 );
$team          = ah_get_team( 6 );
$reviews       = ah_get_reviews( 6 );
$faqs          = ah_get_faqs( 8 );
$properties    = ah_get_properties( 6 );
$static_pages  = ah_get_static_pages();
$file_links    = ah_get_file_links( 10 );
$builder_pages = ah_get_builder_pages( 10 );
$forms         = ah_get_forms_summary();
$recent_posts  = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC' ] );
$recent_pages  = get_posts( [ 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 6, 'orderby' => 'modified', 'order' => 'DESC' ] );

return [
	'settings'      => $settings,
	'home'          => $home,
	'news_items'    => $news_items,
	'trust_signals' => $trust_signals,
	'process_steps' => $process_steps,
	'site_stats'    => $site_stats,
	'services'      => $services,
	'team'          => $team,
	'reviews'       => $reviews,
	'faqs'          => $faqs,
	'properties'    => $properties,
	'static_pages'  => $static_pages,
	'file_links'    => $file_links,
	'builder_pages' => $builder_pages,
	'forms'         => $forms,
	'recent_posts'  => $recent_posts,
	'recent_pages'  => $recent_pages,
	'summary_cards' => [
		[ 'label' => 'Published Posts', 'value' => (int) wp_count_posts( 'post' )->publish, 'note' => 'Blog, news, guides' ],
		[ 'label' => 'Services',        'value' => count( $services ),      'note' => 'Active service records' ],
		[ 'label' => 'FAQs',            'value' => count( $faqs ),          'note' => 'Questions ready to show' ],
		[ 'label' => 'Team Members',    'value' => count( $team ),          'note' => 'People content' ],
		[ 'label' => 'Reviews',         'value' => count( $reviews ),       'note' => 'Proof and testimonials' ],
		[ 'label' => 'Static Pages',    'value' => count( $static_pages ),  'note' => 'Theme HTML pages' ],
		[ 'label' => 'File Links',      'value' => count( $file_links ),    'note' => 'Downloads and resources' ],
		[ 'label' => 'Builder Pages',   'value' => count( $builder_pages ), 'note' => 'Custom drag-drop pages' ],
		[ 'label' => 'Forms',           'value' => count( $forms ),         'note' => 'Reusable form builder items' ],
	],
];
