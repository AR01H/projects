<?php
/**
 * Order page. Sections: admin/data/page_sections.json ("order").
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="site-main">
	<?php nt_render_sections( 'order' ); ?>
</div>
<?php get_footer(); ?>
