<?php

defined( 'ABSPATH' ) || exit;

$mvv           = ch_get_about_mvv();
$quality_items = ch_get_about_quality();


// About Page Header
$about_header = CH_About_Data::page_header_info();
$about_origin = CH_About_Data::origin_settings();
$about_origin_milestones = CH_About_Data::origin_milestones();


get_header();
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ──────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/page-hero', null, [
	'tag'        => $about_header['tag'],
	'heading'    => $about_header['heading'],
	'desc'       => $about_header['description'],
	'modifier'   => $about_header['modifier'],
] ); ?>

<!-- ── Why We Started ────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/about-origin',null,[
	'origin' => $about_origin,
	'milestones'=> $about_origin_milestones,
] ); ?>


<?php 
	get_template_part( 'components/mission-vision', null, [ 'mvv' => $mvv ] ); 
?>

<!-- ── About Gallery ─────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/media-gallery', null, [
	'tag'   => 'Our Gallery',
	'title' => 'View <span class="accent">Our Hygiene</span>',
	'body'  => 'A visual journey through our beginnings, our team, and the craft behind every glass.',
	'bg'    => 'var(--accent)',
	'id'    => 'mg-about',
	'items' => ch_get_about_gallery(),
] ); 

get_template_part( 'components/gallery-strip', null, [
	'tag'      => 'Behind the Scenes',
	'title'    => 'Our Equipment, <span class="accent">Our Craft</span>',
	'body'     => 'The machines, the setup, the ingredients - everything that goes into every perfect glass.',
	'modifier' => 'ch-gstrip--about',
	'id'       => 'gstrip-about',
	'bg'       => 'var(--client-color-11)',
	'images'   => ch_get_about_equipment(),
] );
?>


<!-- ── Quality / Promise ─────────────────────────────────────────────────────── -->
<?php
ob_start();
foreach ( $quality_items as $item ) {
	echo '<li>✓ ' . esc_html( is_array( $item ) ? ( $item['text'] ?? '' ) : $item ) . '</li>';
}
$_about_values_extra = '<ul class="values-list">' . ob_get_clean() . '</ul>';

$_promise             = ch_get_about_promise();
$_promise_tags_html   = '';
foreach ( $_promise['tags'] as $tag ) {
	$_promise_tags_html .= '<div class="promise-tag">' . esc_html( $tag ) . '</div>';
}
$_about_values_visual = '<div style="display:flex;align-items:center;justify-content:center;">'
	. '<div class="promise-card">'
	. '<span class="promise-icon">' . esc_html( $_promise['icon'] ?? '🌱' ) . '</span>'
	. '<div class="promise-title">' . esc_html( $_promise['title'] ?? 'Our Promise' ) . '</div>'
	. '<div class="promise-sub">' . esc_html( $_promise['sub'] ?? '' ) . '</div>'
	. '<div class="promise-tags">' . $_promise_tags_html . '</div>'
	. '</div>'
	. '</div>';
unset( $_promise, $_promise_tags_html );

get_template_part( 'components/image-text-split', null, [
	'layout'        => 'image-right',
	'section_class' => 'about-values',
	'inner_class'   => 'values-content',
	'tag'           => 'Why We Do It',
	'title'         => 'What Makes <span class="accent">The Cane House</span> Different?',
	'body'          => 'At The Cane House, we serve freshly pressed sugarcane juice and natural fruit blends that are prepared fresh for every customer. Our drinks offer a refreshing alternative to fizzy drinks and processed juices, bringing a traditional summer favourite enjoyed by millions to the heart of Sutton.',
	'extra_html'    => $_about_values_extra,
	'visual_html'   => $_about_values_visual,
	'content_anim'  => 'fade-left',
	'visual_anim'   => 'fade-right',
] );
unset( $_about_values_extra, $_about_values_visual );
?>

<!-- ── Events preview ────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/events-preview', null, [
	'tag'     => 'Events & Hire',
	'heading' => 'Need Us at Your <span class="accent">Event?</span>',
	'body'    => 'From weddings to corporate events, we bring freshly-pressed sugarcane juice live to your guests.',
] ); ?>

<!-- ── CTA ────────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/cta-section', null, [
	'tag'        => 'Work With Us',
	'heading'    => 'Let\'s Do Something <span class="accent" style="color:var(--client-color-7);">Amazing</span>',
	'body'       => 'Book us for your next event, or take the leap and bring The Cane House to your city with a franchise.',
	'btn_label'  => '🥤 Book an Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
