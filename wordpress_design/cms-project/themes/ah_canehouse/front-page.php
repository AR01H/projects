<?php
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/home.php';
?>
<main class="ch-main" id="main-content">

<?php get_template_part( 'components/news-ticker' ); ?>
<?php get_template_part( 'components/home-banners' ); ?>
<?php get_template_part( 'components/hero' ); ?>

<?php get_template_part( 'components/showcase-carousel', null, [
	'tag'   => 'See It Live',
	'title' => 'Our Machines, <span class="accent">Our Craft</span>',
	'body'  => 'From the press to the bottle - a closer look at how we bring fresh sugarcane to life.',
	'bg'    => 'var(--client-color-12)',
	'id'    => 'sc-home',
	'items' => $data['eq_media'],
] ); ?>

<?php get_template_part( 'components/menu-builder' ); ?>
<?php get_template_part( 'components/benefits' ); ?>
<?php get_template_part( 'components/story' ); ?>
<?php get_template_part( 'components/event-typesection' ); ?>
<?php get_template_part( 'components/booking-wizard' ); ?>
<?php get_template_part( 'components/franchise-section' ); ?>
<?php get_template_part( 'components/franchise-enquiry' ); ?>
<?php get_template_part( 'components/faq-section' ); ?>
<?php get_template_part( 'components/contact-section' ); ?>
<?php get_template_part( 'components/certifications' ); ?>

</main>
<?php get_footer(); ?>
