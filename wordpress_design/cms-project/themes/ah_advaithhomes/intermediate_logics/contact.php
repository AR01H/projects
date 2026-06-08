<?php
defined( 'ABSPATH' ) || exit;
$_valid_enq_types = [ 'general', 'complaint', 'sales', 'support', 'media', 'other' ];
$preset_enq       = sanitize_key( $_GET['enquiry_type'] ?? '' );
if ( ! in_array( $preset_enq, $_valid_enq_types, true ) ) {
	$preset_enq = '';
}
return [
	'settings'   => ah_get_settings(),
	'faqs'       => ah_get_faqs( 6 ),
	'preset_enq' => $preset_enq,
];
