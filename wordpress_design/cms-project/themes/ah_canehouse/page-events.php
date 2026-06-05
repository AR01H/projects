<?php
/**
 * Template Name: Events & Hire
 */
defined( 'ABSPATH' ) || exit;
get_header();

$ew = class_exists( 'CH_Hire_Data' ) ? CH_Hire_Data::events_why() : [];
$gallery_items =  ch_get_events_media_gallery();

?>

<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => 'Live Juice Stall Hire',
	'heading'    => 'Events & <em>Hire</em>',
	'desc'       => 'Bring The Cane House to your celebration. Live-pressed sugarcane juice - a unique, and unforgettable experience for your guests.',
	'modifier'   => 'ch-page-hero--events',
	'btn1_label' => 'Hire Us',
	'btn1_url'   => '#booking',
	'btn1_icon'  => '🌿',
] ); ?>

<?php get_template_part( 'components/features-ribbon' ); ?>

<?php get_template_part( 'components/event-typesection' ); ?>

<?php get_template_part( 'components/media-gallery', null, [
	'tag'   => 'Events Gallery',
	'title' => 'The Cane House <span class="accent">at Your Events</span>',
	'body'  => 'A glimpse of the live experience we bring - from intimate gatherings to 500-guest celebrations.',
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
