<?php
/**
 * About / Our Story page. Sections: admin/data/page_sections.json ("about").
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div id="main-content" class="site-main">
	<?php nt_render_sections( 'about' ); ?>
</div>
<?php get_footer(); ?>
