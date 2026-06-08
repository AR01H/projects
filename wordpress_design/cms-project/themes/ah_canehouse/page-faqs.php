<?php
/**
 * Template Name: FAQs
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/faqs.php';

get_template_part( 'components/page-hero', null, [
	'tag'      => $data['hero']['tag']     ?? '',
	'heading'  => $data['hero']['heading'] ?? '',
	'desc'     => $data['hero']['desc']    ?? '',
	'modifier' => 'ch-page-hero--sugarcane',
] );
?>
<main class="ch-main" id="main-content">
<?php
get_template_part( 'components/faqs/faq-groups', null, [ 'grouped' => $data['grouped'] ] );
get_template_part( 'components/contact-section' );
?>
</main>
<?php get_footer(); ?>
