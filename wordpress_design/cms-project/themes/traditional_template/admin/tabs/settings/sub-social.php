<?php
/**
 * Site Settings -> Social Links. Rendered in the footer by
 * components/parts/main_footer.php via app_option( 'social' ).
 */

defined( 'ABSPATH' ) || exit;

app_admin_form_open( 'social' );

app_admin_fields( 'social', array(
	'facebook'  => array( 'label' => 'Facebook' ),
	'instagram' => array( 'label' => 'Instagram' ),
	'youtube'   => array( 'label' => 'YouTube' ),
	'linkedin'  => array( 'label' => 'LinkedIn' ),
	'whatsapp'  => array( 'label' => 'WhatsApp', 'help' => __( 'Number with country code, e.g. +919999999999.', NT_TEXT_DOMAIN ) ),
) );

app_admin_form_close();
