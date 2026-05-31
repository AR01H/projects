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
	'images'   => [
		[ 'src' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Branded Stall',        'desc' => 'Full The Cane House branding pack' ],
		[ 'src' => 'https://images.unsplash.com/photo-1518893494013-481c1d8ed3fd?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Live Pressing',       'desc' => 'In front of customers, every time' ],
		[ 'src' => 'https://images.unsplash.com/photo-1486428263684-28ec9e4f2584?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Birmingham',          'desc' => 'Our fastest-growing franchise city' ],
		[ 'src' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Commercial Machine',  'desc' => 'Stainless steel, high-volume press' ],
		[ 'src' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Partner Training',    'desc' => 'Full onboarding + ongoing support' ],
		[ 'src' => 'https://images.unsplash.com/photo-1578269174936-2709b6aeb913?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Market Days',         'desc' => 'High footfall weekend markets' ],
	],
] ); ?>

<?php get_template_part( 'components/franchise-why' ); ?>

<?php get_template_part( 'components/franchise-steps' ); ?>

<?php get_template_part( 'components/reviews-franchise' ); ?>

<?php get_template_part( 'components/franchise-locations' ); ?>

<?php get_template_part( 'components/franchise-enquiry' ); ?>

</main>
<?php get_footer(); ?>
