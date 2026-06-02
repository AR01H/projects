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
	'images'   => [
		[ 'src' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Wedding Setup',       'desc' => 'Live press for 300+ guests' ],
		[ 'src' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Mehndi Night',        'desc' => 'Traditional Asian celebrations' ],
		[ 'src' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Corporate Event',     'desc' => 'Office & team events across the UK' ],
		[ 'src' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Eid Festival',        'desc' => 'Community gatherings & festivals' ],
		[ 'src' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Birthday Party',      'desc' => 'Milestone celebrations' ],
		[ 'src' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Community Festival',  'desc' => 'Street fairs & open-air events' ],
	],
] ); ?>

<?php get_template_part( 'components/events-why' ); ?>

<?php get_template_part( 'components/reviews-events' ); ?>

<?php get_template_part( 'components/contact-section' ); ?>

</main>
<?php get_footer(); ?>
