<?php
// Plugin uninstall — clean up when deleted from WordPress admin
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

// Drop custom tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rh_orders" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rh_order_items" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rh_cart" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rh_wishlist" );

// Delete options
delete_option( 'cms_footer' );
delete_option( 'cms_settings' );
delete_option( 'cms_certifications' );

// Remove custom role
remove_role( 'rh_customer' );
