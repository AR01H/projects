<?php
/**
 * Site Settings -> General. Field TYPES are declared once in
 * config/admin.php; this view only supplies labels/help text.
 * Values are read anywhere with nt_option( 'general', 'field' ).
 */

defined( 'ABSPATH' ) || exit;

nt_admin_form_open( 'general' );

nt_admin_fields( 'general', array(
	'tagline'     => array( 'label' => __( 'Tagline', NT_TEXT_DOMAIN ), 'help' => __( 'Short line shown in the header/footer.', NT_TEXT_DOMAIN ) ),
	'phone'       => array( 'label' => __( 'Phone', NT_TEXT_DOMAIN ) ),
	'email'       => array( 'label' => __( 'Public Email', NT_TEXT_DOMAIN ), 'help' => __( 'Contact form submissions are sent here (falls back to the WP admin email).', NT_TEXT_DOMAIN ) ),
	'address'     => array( 'label' => __( 'Address', NT_TEXT_DOMAIN ) ),
	'footer_note' => array( 'label' => __( 'Footer Note', NT_TEXT_DOMAIN ), 'help' => __( 'Small HTML allowed (links, bold).', NT_TEXT_DOMAIN ) ),
) );

nt_admin_form_close();
