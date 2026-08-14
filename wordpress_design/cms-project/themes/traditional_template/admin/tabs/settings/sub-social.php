<?php
/**
 * Site Settings -> Social Links. Rendered in the footer by
 * components/navigation/main_footer.php via App_Helpers::option( 'social' ).
 */

defined( 'ABSPATH' ) || exit;

App_Admin::form_open( 'social' );

App_Admin::fields( 'social', array(
	'facebook'  => array( 'label' => 'Facebook' ),
	'instagram' => array( 'label' => 'Instagram' ),
	'youtube'   => array( 'label' => 'YouTube' ),
	'linkedin'  => array( 'label' => 'LinkedIn' ),
	'whatsapp'  => array( 'label' => 'WhatsApp', 'help' => __( 'Number with country code, e.g. +919999999999.', NT_TEXT_DOMAIN ) ),
) );

App_Admin::form_close();
