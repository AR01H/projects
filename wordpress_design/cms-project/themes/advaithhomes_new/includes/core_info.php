<?php
// Read company/contact/social details from plugin settings DB (ah_site_settings).
// Falls back to hardcoded defaults if the table is not yet created or the value is empty.
// This file loads early (functions.php line 12), before services.php, so we query $wpdb directly.

$_ah_info = array();
global $wpdb;
if ( $wpdb ) {
	$_t = $wpdb->prefix . 'ah_site_settings';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $_t ) ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table from $wpdb->prefix, WHERE values are static literals
		$_rows = $wpdb->get_results(
			"SELECT setting_key, setting_val FROM `{$_t}` WHERE group_name IN ('general','contact','social')",
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

// ── Company / contact ────────────────────────────────────────────────────────
define( 'COMPANY_NAME',              _ah_info( 'company_name',   'Advaith Homes' ) );
// contact_phone is the canonical key; fall back to legacy 'phone'.
define( 'COMPANY_PHONE_NO',          _ah_info( 'contact_phone',  _ah_info( 'phone', '' ) ) );
define( 'COMPANY_EXTENDED_PHONE_NO', _ah_info( 'contact_phone',  _ah_info( 'phone', '' ) ) );
define( 'COMPANY_EMAIL',             _ah_info( 'contact_email',  _ah_info( 'email', '' ) ) );
define( 'COMPANY_WHATSAPP_NO',       _ah_info( 'whatsapp_number', _ah_info( 'whatsapp', '' ) ) );

// ── Social media URLs (empty string = not set) ───────────────────────────────
define( 'SOCIAL_FACEBOOK',  _ah_info( 'facebook_url',  '' ) );
define( 'SOCIAL_INSTAGRAM', _ah_info( 'instagram_url', '' ) );
define( 'SOCIAL_TWITTER',   _ah_info( 'twitter_url',   '' ) );
define( 'SOCIAL_LINKEDIN',  _ah_info( 'linkedin_url',  '' ) );
define( 'SOCIAL_YOUTUBE',   _ah_info( 'youtube_url',   '' ) );
define( 'SOCIAL_TIKTOK',    _ah_info( 'tiktok_url',    '' ) );

// Switch which data folder the theme reads from.
define( 'DATA_FILES', 'advaith' );

unset( $_ah_info );
