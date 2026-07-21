<?php
/**
 * Template Name: Contact
 *
 * Registered as 'contact' in config/pages.php. Reuses the same vintage-styled
 * contact-section.php component shown on the home page (polaroid photo, stamp
 * badge, contact details, form) instead of a separate plain/unstyled layout -
 * one styled, JSON-driven component, no duplicate CSS to maintain.
 * The form posts through NT.ajax('contact_submit') -> config/ajax.php ->
 * handlers/ajax/contact.php. No nonce code here - the dispatcher and the
 * data-nt-ajax-form handler in common.js take care of it.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nt_hdr = nt_data( 'page_headers' )['contact'] ?? array();
?>
<div class="nt-contact-page">
	<?php
	nt_component( 'parts/page_header', array(
		'tag'      => $nt_hdr['tag']      ?? '',
		'icon'     => $nt_hdr['icon']     ?? '',
		'title'    => $nt_hdr['title']    ?? __( 'Contact Us', NT_TEXT_DOMAIN ),
		'subtitle' => $nt_hdr['subtitle'] ?? '',
		'image'    => $nt_hdr['image']    ?? '',
	) );
	get_template_part( 'components/contact-section' );
	?>
</div>
<?php
get_footer();
