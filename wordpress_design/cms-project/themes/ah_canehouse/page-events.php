<?php
/**
 * Template Name: Events & Hire
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/events.php';
?>
<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => $data['hero']['tag']     ?? '',
	'heading'    => $data['hero']['heading'] ?? '',
	'desc'       => $data['hero']['desc']    ?? '',
	'modifier'   => 'ch-page-hero--events',
	'btn1_label' => 'Hire Us',
	'btn1_url'   => '#booking',
	'btn1_icon'  => '🌿',
] ); ?>

<?php get_template_part( 'components/features-ribbon' ); ?>
<?php get_template_part( 'components/event-typesection' ); ?>

<?php get_template_part( 'components/media-gallery', null, [
	'tag'   => $data['gallery_h']['tag']   ?? '',
	'title' => $data['gallery_h']['title'] ?? '',
	'body'  => $data['gallery_h']['body']  ?? '',
	'id'    => 'mg-events',
	'items' => $data['gallery'],
] ); ?>

<?php get_template_part( 'components/events-why', null, [
	'items' => $data['events_why']['items'] ?? [],
] ); ?>

<?php get_template_part( 'components/reviews-events' ); ?>
<?php get_template_part( 'components/booking-wizard' ); ?>

</main>
<?php get_footer(); ?>
