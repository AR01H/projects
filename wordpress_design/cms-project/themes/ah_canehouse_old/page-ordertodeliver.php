<?php
/**
 * Template Name: Online Delivery
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/ordertodeliver.php';
$hero = $data['hero'];
?>
<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => $hero['tag']     ?? 'Order Fresh',
	'heading'    => $hero['heading'] ?? 'Delivered Fresh to <em>Your Door</em>',
	'desc'       => $hero['desc']    ?? '',
	'modifier'   => 'ch-page-hero--sugarcane',
	'btn1_label' => 'Order Now',
	'btn1_url'   => '#order-to-deliver',
	'btn1_icon'  => '🥤',
] ); ?>

<?php get_template_part( 'components/features-ribbon' ); ?>
<?php get_template_part( 'components/order-to-deliver' ); ?>
<?php get_template_part( 'components/how-to-order' ); ?>
<?php get_template_part( 'components/carousels/review-carousel' ); ?>
<?php get_template_part( 'components/certifications' ); ?>

<?php get_template_part( 'components/contact-section' ); ?>

</main>
<?php get_footer(); ?>
