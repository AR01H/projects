<?php
/**
 * Template Name: Contact
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>


<!-- ── Contact form (reuses the shared component) ───────────────────────────── -->
<div class="ch-contact-page-form">
	<?php get_template_part( 'components/contact-section' ); ?>
</div>


<?php get_template_part( 'components/cta-section', null, [
	'tag'        => 'Work With Us',
	'heading'    => 'Look at us <span class="accent" style="color:var(--client-color-7);"> Its Amazing</span>',
	'body'       => 'Book us for your next event, or take the leap and bring The Cane House to your city with a franchise.',
	'btn_label'  => '🥤View Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
