<?php
/**
 * Template Name: Contact
 */
defined( 'ABSPATH' ) || exit;
get_header();

$_cta = CH_Shared_Data::section_heading( 'cta_contact' );
?>


<!-- ── Contact form (reuses the shared component) ───────────────────────────── -->
<div class="ch-contact-page-form">
	<?php get_template_part( 'components/contact-section' ); ?>
</div>


<?php get_template_part( 'components/cta-section', null, [
	'tag'        => $_cta['tag']     ?? '',
	'heading'    => $_cta['heading'] ?? '',
	'body'       => $_cta['body']    ?? '',
	'btn_label'  => '🥤View Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
