<?php
/**
 * Template Name: Contact
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/contact.php';
$cta  = $data['cta'];
?>
<div class="ch-contact-page-form">
  <?php get_template_part( 'components/contact-section' ); ?>
</div>

<?php get_template_part( 'components/cta-section', null, [
	'tag'        => $cta['tag']     ?? '',
	'heading'    => $cta['heading'] ?? '',
	'body'       => $cta['body']    ?? '',
	'btn_label'  => '🥤View Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
