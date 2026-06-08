<?php
/**
 * Template Name: Online Delivery
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/ordertodeliver.php';
$hero = $data['hero'];
$cta  = $data['cta'];
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
<?php get_template_part( 'components/review-carousel' ); ?>
<?php get_template_part( 'components/certifications' ); ?>

<?php get_template_part( 'components/cta-section', null, [
	'tag'        => $cta['tag']     ?? 'Ready to Order?',
	'heading'    => $cta['heading'] ?? 'Fresh Juice at <span class="accent" style="color:var(--client-color-7);">Your Doorstep</span>',
	'body'       => $cta['body']    ?? '',
	'btn_label'  => '🥤 Order Now',
	'btn_url'    => '#order-to-deliver',
	'show_phone' => true,
] ); ?>

<?php get_template_part( 'components/contact-section' ); ?>

</main>
<?php get_footer(); ?>
