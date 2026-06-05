<?php
/**
 * Template Name: Events & Hire
 */
defined( 'ABSPATH' ) || exit;
get_header();

$ew            = class_exists( 'CH_Hire_Data' ) ? CH_Hire_Data::events_why() : [];
$gallery_items = ch_get_events_media_gallery();
$_hero         = CH_Shared_Data::section_heading( 'page_hero_events' );
$_gallery      = CH_Shared_Data::section_heading( 'gallery_events' );

?>

<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => $_hero['tag']     ?? '',
	'heading'    => $_hero['heading'] ?? '',
	'desc'       => $_hero['desc']    ?? '',
	'modifier'   => 'ch-page-hero--events',
	'btn1_label' => 'Hire Us',
	'btn1_url'   => '#booking',
	'btn1_icon'  => '🌿',
] ); ?>

<?php get_template_part( 'components/features-ribbon' ); ?>

<?php get_template_part( 'components/event-typesection' ); ?>

<?php get_template_part( 'components/media-gallery', null, [
	'tag'   => $_gallery['tag']   ?? '',
	'title' => $_gallery['title'] ?? '',
	'body'  => $_gallery['body']  ?? '',
	'bg'    => 'var(--client-color-6)',
	'id'    => 'mg-events',
	'items' => $gallery_items
] ); ?>

<?php 
	get_template_part( 'components/events-why', null, [
	'items' => $ew['items'],
] ); ?>

<?php get_template_part( 'components/reviews-events' ); ?>

<?php get_template_part( 'components/booking-wizard' ); ?>

</main>
<?php get_footer(); ?>
