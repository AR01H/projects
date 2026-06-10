<?php
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/home.php';

// Traditional design adds the mockup sections (Our Story / Our Drinks /
// Events & Catering) alongside the existing home sections. Gated on the
// constant so the Modern home is byte-identical to before.
$is_trad = function_exists( 'ch_design_is' ) && ch_design_is( 'traditional' );
?>
<main class="ch-main<?php echo $is_trad ? ' ch-trad-home' : ''; ?>" id="main-content">

<?php get_template_part( 'components/news-ticker' ); ?>
<?php get_template_part( 'components/home-banners' ); ?>
<?php get_template_part( 'components/hero' ); ?>

<?php if ( $is_trad ) { get_template_part( 'components/traditional/our-story-home' ); } ?>

<?php get_template_part( 'components/carousels/showcase-carousel', null, [
	'tag'   => $data['showcase_h']['tag']   ?? '',
	'title' => $data['showcase_h']['title'] ?? '',
	'body'  => $data['showcase_h']['body']  ?? '',
	'bg'    => 'var(--client-color-12)',
	'id'    => 'sc-home',
	'items' => $data['eq_media'],
] ); ?>

<?php get_template_part( 'components/carousels/carousel_video_scroll', null, [
	'tag'   => $data['video_h']['tag']   ?? '',
	'title' => $data['video_h']['title'] ?? '',
	'body'  => $data['video_h']['body']  ?? '',
	'items' => $data['video_media'],   // loaded in intermediate_logics/home.php
] ); ?>

<?php get_template_part( 'components/carousels/carousel_mini_video_scroll', null, [
	'tag'   => $data['mini_h']['tag']   ?? '',
	'title' => $data['mini_h']['title'] ?? '',
	'body'  => $data['mini_h']['body']  ?? '',
	'items' => $data['mini_media'],    // loaded in intermediate_logics/home.php
] ); ?>

<?php get_template_part( 'components/menu-builder' ); ?>

<?php if ( $is_trad ) { get_template_part( 'components/traditional/our-drinks' ); } ?>

<?php get_template_part( 'components/benefits' ); ?>
<?php get_template_part( 'components/story' ); ?>
<?php get_template_part( 'components/event-typesection' ); ?>

<?php if ( $is_trad ) { get_template_part( 'components/traditional/events-catering' ); } ?>

<?php get_template_part( 'components/booking-wizard' ); ?>
<?php get_template_part( 'components/franchise-section' ); ?>
<?php get_template_part( 'components/franchise-enquiry' ); ?>
<?php get_template_part( 'components/faq-section' ); ?>
<?php get_template_part( 'components/contact-section' ); ?>
<?php get_template_part( 'components/certifications' ); ?>

</main>
<?php get_footer(); ?>
