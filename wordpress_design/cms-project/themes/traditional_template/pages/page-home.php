<?php
/**
 * Template Name: Home
 *
 * Sections are declared in admin/data/page_sections.json ("home") and rendered
 * by NT_Section_Renderer. To add/re-order a home section, edit that JSON - not
 * this file. See src/Sections/class-section-renderer.php.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="site-main app-trad-home" id="main-content">
	<?php app_render_sections( 'home' ); ?>
</div>
<?php get_footer(); ?>
