<?php
// Read company/contact details from plugin settings DB (ah_site_settings).
// Falls back to hardcoded defaults if the table is not yet created or the value is empty.
// This file loads early (functions.php line 12), before services.php, so we query $wpdb directly.

$_ah_info = array();
global $wpdb;
if ( $wpdb ) {
	$_t = $wpdb->prefix . 'ah_site_settings';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $_t ) ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table from $wpdb->prefix, WHERE values are static literals
		$_rows = $wpdb->get_results(
			"SELECT setting_key, setting_val FROM `{$_t}` WHERE group_name IN ('general','contact')",
			ARRAY_A
		);
		if ( is_array( $_rows ) ) {
			foreach ( $_rows as $_r ) {
				$_ah_info[ (string) $_r['setting_key'] ] = (string) $_r['setting_val'];
			}
		}
	}
}

/**
 * Return DB value for $key if non-empty, otherwise $fallback.
 * Local helper - only used during constants bootstrap below.
 */
function _ah_info( string $key, string $fallback ): string {
	global $_ah_info;
	return ( isset( $_ah_info[ $key ] ) && '' !== $_ah_info[ $key ] ) ? $_ah_info[ $key ] : $fallback;
}

define( 'COMPANY_NAME',              _ah_info( 'company_name', 'Advaith Homes' ) );
define( 'COMPANY_PHONE_NO',          _ah_info( 'phone',        '+91000000000'  ) );
define( 'COMPANY_EXTENDED_PHONE_NO', _ah_info( 'phone',        '+91 000000000' ) );
define( 'COMPANY_EMAIL',             _ah_info( 'email',        'test@gmail.com' ) );
define( 'COMPANY_WHATSAPP_NO',       _ah_info( 'whatsapp',     ''              ) );
// Switch which data folder the theme reads from. Set to 'organics' to use the organic dataset.
// define('DATA_FILES','organics');
define('DATA_FILES','advaith');
// define('DATA_FILES','health');

unset( $_ah_info );
