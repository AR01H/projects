<?php
/**
 * Events & Catering page. Sections: admin/data/page_sections.json ("events").
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="site-main">
	<?php nt_render_sections( 'events' ); ?>
</div>
<?php get_footer(); ?>
