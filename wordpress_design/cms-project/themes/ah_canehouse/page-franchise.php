<?php
/**
 * Template Name: Franchise Opportunities
 */
defined( 'ABSPATH' ) || exit;
get_header();

$_hero    = CH_Shared_Data::section_heading( 'page_hero_franchise' );
$_gallery = CH_Shared_Data::section_heading( 'gallery_franchise' );
?>

<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => $_hero['tag']     ?? '',
	'heading'    => $_hero['heading'] ?? '',
	'desc'       => $_hero['desc']    ?? '',
	'modifier'   => 'ch-page-hero--franchise',
	'btn1_label' => 'Start Your Enquiry',
	'btn1_url'   => '#franchise-enquiry',
	'btn1_icon'  => '🌿',
] ); ?>

<?php get_template_part( 'components/media-gallery', null, [
	'tag'   => $_gallery['tag']   ?? '',
	'title' => $_gallery['title'] ?? '',
	'body'  => $_gallery['body']  ?? '',
	'bg'    => 'var(--client-color-11)',
	'id'    => 'mg-franchise',
	'items' => ch_get_franchise_media_gallery(),
] ); ?>

<?php get_template_part( 'components/franchise-why' ); ?>

<?php get_template_part( 'components/franchise-steps' ); ?>

<?php get_template_part( 'components/reviews-franchise' ); ?>

<?php get_template_part( 'components/franchise-locations' ); ?>

<?php get_template_part( 'components/franchise-enquiry' ); ?>

</main>
<?php get_footer(); ?>
