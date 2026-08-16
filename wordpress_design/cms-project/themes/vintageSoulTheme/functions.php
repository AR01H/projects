<?php

defined( 'ABSPATH' ) || exit;

define( 'VINTAGESOUL_VERSION', '0.1.0' );
define( 'VINTAGESOUL_DIR', get_template_directory() );
define( 'VINTAGESOUL_URI', get_template_directory_uri() );

spl_autoload_register( static function ( string $class ): void {
	$prefix = 'VintageSoul\\';
	if ( 0 !== strpos( $class, $prefix ) ) {
		return;
	}
	$relative = substr( $class, strlen( $prefix ) );
	$path     = VINTAGESOUL_DIR . '/src/' . str_replace( '\\', '/', $relative ) . '.php';
	if ( is_file( $path ) ) {
		require_once $path;
	}
} );

VintageSoul\Bootstrap\Theme::init();
