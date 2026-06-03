<?php
/**
 * Template Name: Events & Hire
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => 'Live Juice Stall Hire',
	'heading'    => 'Events & <em>Hire</em>',
	'desc'       => 'Bring The Cane House to your celebration. Live-pressed sugarcane juice - a unique, healthy, and unforgettable experience for your guests.',
	'modifier'   => 'ch-page-hero--events',
] ); ?>

<?php get_template_part( 'components/features-ribbon' ); ?>

<?php get_template_part( 'components/booking-wizard' ); ?>

<?php get_template_part( 'components/events-packages' ); ?>

<?php get_template_part( 'components/gallery-strip', null, [
	'tag'      => 'Events Gallery',
	'title'    => 'The Cane House <span class="accent">at Your Events</span>',
	'body'     => 'A glimpse of the live experience we bring — from intimate gatherings to 500-guest celebrations.',
	'modifier' => 'ch-gstrip--events',
	'id'       => 'gstrip-events',
	'bg'       => 'var(--ch-green-bg)',
	'images' => ch_get_events_gallery(),
] ); ?>

<?php $ew = ch_get_events_why();
get_template_part( 'components/events-why', null, [
	'image' => $ew['image'] ?? '',
	'items' => $ew['items'] ?? [],
] ); ?>

<?php get_template_part( 'components/reviews-events' ); ?>

<?php get_template_part( 'components/contact-section' ); ?>

</main>
<?php get_footer(); ?>
