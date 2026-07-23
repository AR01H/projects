<?php
/**
 * Gallery page. Sections: admin/data/page_sections.json ("gallery").
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="site-main">
	<?php nt_render_sections( 'gallery' ); ?>
</div>
<?php get_footer(); ?>
