<?php
/**
 * Plugin Name: CMS Ecommerce Plugin
 * Description: REST API backend for ecommerce — storefront + admin panel
 * Version: 1.0.0
 * Author: CMS Project
 * Text Domain: cms-ecommerce
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CMS_ECOMMERCE_VERSION', '1.0.0' );
define( 'CMS_ECOMMERCE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CMS_ECOMMERCE_URL', plugin_dir_url( __FILE__ ) );

// Autoload classes
spl_autoload_register( function ( $class ) {
    $prefix = 'CMS_ECOMMERCE\\';
    if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) return;
    $relative = substr( $class, strlen( $prefix ) );
    $file = CMS_ECOMMERCE_PATH . 'includes/' . str_replace( '\\', '/', $relative ) . '.php';
    if ( file_exists( $file ) ) require_once $file;
});

// Boot
CMS_ECOMMERCE\Core\Plugin::instance()->init();
