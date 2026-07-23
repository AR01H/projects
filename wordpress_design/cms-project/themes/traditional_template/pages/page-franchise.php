<?php
/**
 * Franchise page. Sections: admin/data/page_sections.json ("franchise").
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="site-main">
	<?php nt_render_sections( 'franchise' ); ?>
</div>
<?php get_footer(); ?>
