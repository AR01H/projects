<?php
/**
 * Template Name: Franchise Opportunities
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => 'Grow With Us',
	'heading'    => 'Franchise <em>Opportunities</em>',
	'desc'       => 'Join the UK\'s fastest-growing natural juice movement. Bring live-pressed sugarcane juice to your city - we provide everything you need to succeed.',
	'modifier'   => 'ch-page-hero--franchise',
	'btn1_label' => 'Start Your Enquiry',
	'btn1_url'   => '#franchise-enquiry',
	'btn1_icon'  => '🌿',
	'btn2_label' => 'Call Us',
	'btn2_icon'  => '📞',
	'show_phone' => true,
] ); ?>

<?php get_template_part( 'components/gallery-strip', null, [
	'tag'      => 'Franchise Gallery',
	'title'    => 'The Cane House <span class="accent">in Action</span>',
	'body'     => 'Our partners across the UK — branded stalls, live pressing, and happy queues.',
	'modifier' => 'ch-gstrip--franchise',
	'id'       => 'gstrip-franchise',
	'bg'       => 'var(--ch-white)',
	'images' => ch_get_franchise_gallery(),
] ); ?>

<?php get_template_part( 'components/franchise-why' ); ?>

<?php get_template_part( 'components/franchise-steps' ); ?>

<?php get_template_part( 'components/reviews-franchise' ); ?>

<?php get_template_part( 'components/franchise-locations' ); ?>

<?php get_template_part( 'components/franchise-enquiry' ); ?>

</main>
<?php get_footer(); ?>
