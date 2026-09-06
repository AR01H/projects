<?php

defined( 'ABSPATH' ) || exit;

define( 'VINTAGESOUL_VERSION', '0.1.0' );
define( 'VINTAGESOUL_DIR', get_template_directory() );
define( 'VINTAGESOUL_URI', get_template_directory_uri() );

// Optional cache-busting override (mirrors advaithhomes_new's LOCAL_CACHE_VERSION):
// every enqueued CSS/JS file normally gets its own filemtime()-based version, which
// auto-busts whenever that one file changes. Define this constant (e.g. in
// wp-config.php, or uncomment below) to force every CSS/JS/image URL to one fixed
// version instead - useful right after a deploy where file mtimes aren't trustworthy
// (a fresh git checkout/build resets them all to the same time). Leave undefined to
// keep the default per-file, auto-busting behavior.
// define( 'VINTAGESOUL_CACHE_VERSION', 'v2' );

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

if ( is_file( VINTAGESOUL_DIR . '/includes/ADN_Cache.php' ) ) {
	require_once VINTAGESOUL_DIR . '/includes/ADN_Cache.php';
}

VintageSoul\Bootstrap\Theme::init();

