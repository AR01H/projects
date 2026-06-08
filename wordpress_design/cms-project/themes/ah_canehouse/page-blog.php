<?php
/**
 * Template Name: The Cane Journal
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/blog.php';
?>
<main class="ch-main" id="main-content">

<?php get_template_part( 'components/page-hero', null, [
	'tag'     => $data['hero']['tag']     ?? '',
	'heading' => $data['hero']['heading'] ?? '',
	'desc'    => $data['hero']['desc']    ?? '',
] ); ?>

<?php get_template_part( 'components/blog/post-list', null, [
	'journal_query' => $data['journal_query'],
	'active_cat'    => $data['active_cat'],
	'paged'         => $data['paged'],
] ); ?>

</main>
<?php get_footer(); ?>
