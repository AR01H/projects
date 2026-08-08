<?php
/**
 * FAQ / help centre page. Sections: admin/data/page_sections.json ("faq").
 *
 * A registry-driven page: it lists no components. The ordered section list
 * lives in admin/data/page_sections.json under "faq" and is rendered by
 * NT_Section_Renderer - so re-ordering, adding or hiding a band on this page
 * is a one-line JSON edit with no PHP change.
 *
 * Registered as 'faq' in config/pages.php.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div id="main-content" class="site-main">
	<?php app_render_sections( 'faq' ); ?>
</div>
<?php get_footer(); ?>
