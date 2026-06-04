<?php

defined( 'ABSPATH' ) || exit;

// Load data from JSON files in real_data/json
$about_header =  CH_About_Data::page_header_info();
$about_values = ch_get_about_quality();
$about_origin = CH_About_Data::origin_settings();
$about_origin_milestones = CH_About_Data::origin_milestones();
$mvv           = ch_get_about_mvv();

get_header(); ?>

<main class="ch-main" id="main-content">

<!-- ── Hero ──────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/page-hero', null, [
	'tag'        => $about_header['tag'] ?? '',
	'heading'    => $about_header['heading'] ?? '',
	'desc'       => $about_header['description'] ?? '',
	'modifier'   => $about_header['modifier'] ?? '',
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
<?php get_template_part('components/quality-promise', null, [ 'values' => $about_values ] ); ?>

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
