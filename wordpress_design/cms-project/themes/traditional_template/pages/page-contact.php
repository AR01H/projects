<?php
/**
 * Contact page. Sections: admin/data/page_sections.json ("contact").
 * The contact form posts via NT.ajax('contact_submit') (config/ajax.php ->
 * handlers/ajax/contact.php); nonce handled by the dispatcher + common.js.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="nt-contact-page">
	<?php nt_render_sections( 'contact' ); ?>
</div>
<?php get_footer(); ?>
