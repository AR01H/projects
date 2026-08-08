<?php
/**
 * Products page. Sections: admin/data/page_sections.json ("products").
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="site-main">
	<?php app_render_sections( 'products' ); ?>
</div>
<?php get_footer(); ?>
