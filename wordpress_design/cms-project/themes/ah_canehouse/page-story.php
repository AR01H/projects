<?php
/**
 * Template Name: Our Story
 * Slug: /our-story/
 *
 * This page is entirely about sugarcane — its lifecycle, global love, and history.
 * The business story lives at /about/.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'        => 'The Journey',
	'heading'    => 'The <em>Sugarcane</em> Story',
	'desc'       => 'From ancient fields across five continents to your cup — pressed live, served cool, with 2,000 years of tradition behind every glass.',
	'modifier'   => 'ch-page-hero--sugarcane',
	'btn1_label' => 'See the Lifecycle',
	'btn1_url'   => '#story-cards',
	'btn1_icon'  => '🌿',
] ); ?>

<!-- Sugarcane lifecycle tabs -->
<?php get_template_part( 'components/story-cards' ); ?>

<!-- Why Sugarcane Juice is Loved Worldwide -->
<?php get_template_part( 'components/sugarcane-benefits' ); ?>

<!-- Beyond the Juice — history & heritage -->
<?php get_template_part( 'components/story' ); ?>

<!-- CTA -->
<?php get_template_part( 'components/cta-section', null, [
	'tag'        => 'Experience It',
	'heading'    => 'Ready to <span class="accent" style="color:var(--ch-lime);">Taste the Tradition?</span>',
	'body'       => 'Book us for your next event or explore a franchise opportunity in your city.',
	'btn_label'  => '🥤 Book an Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise →',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
