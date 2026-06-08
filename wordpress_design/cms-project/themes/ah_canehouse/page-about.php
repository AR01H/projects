<?php
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/about.php';
?>
<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'      => $data['header']['tag']         ?? '',
	'heading'  => $data['header']['heading']     ?? '',
	'desc'     => $data['header']['description'] ?? '',
	'modifier' => $data['header']['modifier']    ?? '',
] ); ?>

<?php get_template_part( 'components/about-origin', null, [
	'origin'     => $data['origin'],
	'milestones' => $data['milestones'],
] ); ?>

<?php get_template_part( 'components/mission-vision', null, [ 'mvv' => $data['mvv'] ] ); ?>

<?php get_template_part( 'components/media-gallery', null, [
	'tag'   => $data['gh_about']['tag']   ?? '',
	'title' => $data['gh_about']['title'] ?? '',
	'body'  => $data['gh_about']['body']  ?? '',
	'id'    => 'mg-about',
	'items' => $data['gallery'],
] ); ?>

<?php get_template_part( 'components/gallery-strip', null, [
	'tag'      => $data['gh_strip']['tag']   ?? '',
	'title'    => $data['gh_strip']['title'] ?? '',
	'body'     => $data['gh_strip']['body']  ?? '',
	'modifier' => 'ch-gstrip--about',
	'id'       => 'gstrip-about',
	'bg'       => 'var(--client-color-11)',
	'images'   => $data['eq_gallery'],
] ); ?>

<?php get_template_part( 'components/quality-promise', null, [ 'values' => $data['values'] ] ); ?>

<?php get_template_part( 'components/events-preview', null, [
	'tag'     => $data['gh_events']['tag']     ?? '',
	'heading' => $data['gh_events']['heading'] ?? '',
	'body'    => $data['gh_events']['body']    ?? '',
] ); ?>

<?php get_template_part( 'components/cta-section', null, [
	'tag'        => $data['gh_cta']['tag']     ?? '',
	'heading'    => $data['gh_cta']['heading'] ?? '',
	'body'       => $data['gh_cta']['body']    ?? '',
	'btn_label'  => '🥤 Book an Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
