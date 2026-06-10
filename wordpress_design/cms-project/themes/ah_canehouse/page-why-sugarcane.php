<?php
/**
 * Template Name: Why Sugarcane
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/why-sugarcane.php';
$cta  = $data['cta'];
?>
<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'modifier' => 'ch-page-hero--sugarcane',
	'tag'      => $data['hero']['tag']     ?? '',
	'heading'  => $data['hero']['heading'] ?? '',
	'desc'     => $data['hero']['desc']    ?? '',
	'badge'    => ( function_exists( 'ch_design_is' ) && ch_design_is( 'traditional' ) ) ? "Nature's Purest Gift" : '',
] ); ?>

<?php get_template_part( 'components/history-info' ); ?>
<?php get_template_part( 'components/story-cards' ); ?>

<?php get_template_part( 'components/why-sugarcane/nutrition-split', null, [
	'nutrition_h'    => $data['nutrition_h'],
	'nutrition_facts' => $data['nutrition_facts'],
	'nf_disclaimer'  => $data['nf_disclaimer'],
] ); ?>

<?php get_template_part( 'components/sugarcane-benefits' ); ?>
<?php get_template_part( 'components/beyondjuice' ); ?>

<?php get_template_part( 'components/cta-section', null, [
	'tag'        => $cta['tag']     ?? '',
	'heading'    => $cta['heading'] ?? '',
	'body'       => $cta['body']    ?? '',
	'btn_label'  => '🥤 Book an Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise →',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
