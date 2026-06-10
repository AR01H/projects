<?php
/**
 * Template Name: Franchise Opportunities
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/franchise.php';
?>
<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => $data['hero']['tag']     ?? '',
	'heading'    => $data['hero']['heading'] ?? '',
	'desc'       => $data['hero']['desc']    ?? '',
	'modifier'   => 'ch-page-hero--franchise',
	'badge'      => ( function_exists( 'ch_design_is' ) && ch_design_is( 'traditional' ) ) ? 'Proven Model' : '',
	'btn1_label' => 'Start Your Enquiry',
	'btn1_url'   => '#franchise-enquiry',
	'btn1_icon'  => '🌿',
] ); ?>

<?php get_template_part( 'components/media-gallery', null, [
	'tag'   => $data['gallery_h']['tag']   ?? '',
	'title' => $data['gallery_h']['title'] ?? '',
	'body'  => $data['gallery_h']['body']  ?? '',
	'id'    => 'mg-franchise',
	'items' => $data['gallery'],
] ); ?>

<?php get_template_part( 'components/franchise-why' ); ?>
<?php get_template_part( 'components/franchise-steps' ); ?>
<?php get_template_part( 'components/reviews-franchise' ); ?>
<?php get_template_part( 'components/franchise-locations' ); ?>
<?php get_template_part( 'components/franchise-enquiry' ); ?>

</main>
<?php get_footer(); ?>
