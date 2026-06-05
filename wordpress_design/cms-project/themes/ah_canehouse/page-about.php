<?php

defined( 'ABSPATH' ) || exit;

// Load data from JSON files in real_data/json
$about_header            = CH_About_Data::page_header_info();
$about_values            = ch_get_about_quality();
$about_origin            = CH_About_Data::origin_settings();
$about_origin_milestones = CH_About_Data::origin_milestones();
$mvv                     = ch_get_about_mvv();
$_gallery_about          = CH_Shared_Data::section_heading( 'gallery_about' );
$_gallery_strip          = CH_Shared_Data::section_heading( 'gallery_strip_about' );
$_events_preview         = CH_Shared_Data::section_heading( 'events_preview' );
$_cta                    = CH_Shared_Data::section_heading( 'cta_about' );

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
	'tag'   => $_gallery_about['tag']   ?? '',
	'title' => $_gallery_about['title'] ?? '',
	'body'  => $_gallery_about['body']  ?? '',
	'bg'    => 'var(--accent)',
	'id'    => 'mg-about',
	'items' => ch_get_about_gallery(),
] );

get_template_part( 'components/gallery-strip', null, [
	'tag'      => $_gallery_strip['tag']   ?? '',
	'title'    => $_gallery_strip['title'] ?? '',
	'body'     => $_gallery_strip['body']  ?? '',
	'modifier' => 'ch-gstrip--about',
	'id'       => 'gstrip-about',
	'bg'       => 'var(--client-color-11)',
	'images'   => ch_get_equipment_gallery(),
] );
?>


<!-- ── Quality / Promise ─────────────────────────────────────────────────────── -->
<?php get_template_part('components/quality-promise', null, [ 'values' => $about_values ] ); ?>

<!-- ── Events preview ────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/events-preview', null, [
	'tag'     => $_events_preview['tag']     ?? '',
	'heading' => $_events_preview['heading'] ?? '',
	'body'    => $_events_preview['body']    ?? '',
] ); ?>

<!-- ── CTA ────────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/cta-section', null, [
	'tag'        => $_cta['tag']     ?? '',
	'heading'    => $_cta['heading'] ?? '',
	'body'       => $_cta['body']    ?? '',
	'btn_label'  => '🥤 Book an Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
