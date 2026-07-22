<?php
/**
 * PSR-4 compliant autoloader.
 *
 * Namespace: AHEcommerce
 * Maps to:   includes/
 */

spl_autoload_register( function ( $class ) {

	$prefix = 'AHEcommerce\\';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	// Skip if already loaded.
	if ( class_exists( $class, false ) || interface_exists( $class, false ) || trait_exists( $class, false ) ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$parts = explode( '\\', $relative_class );

	// Last part is the class name — convert to kebab-case file name.
	$class_name = array_pop( $parts );
	$file_name  = strtolower( str_replace( '_', '-', $class_name ) );

	// Remaining parts become lowercase directory names.
	$dir = '';
	if ( ! empty( $parts ) ) {
		$dir = strtolower( implode( '/', $parts ) ) . '/';
	}

	// Build base from __DIR__ (this file lives in includes/).
	$base_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
	$full_dir = $base_dir . $dir;

	// Try standard WP file name prefixes.
	$candidates = array(
		$full_dir . 'class-' . $file_name . '.php',
		$full_dir . 'abstract-' . $file_name . '.php',
		$full_dir . 'interface-' . $file_name . '.php',
		$full_dir . $file_name . '.php',
	);

	foreach ( $candidates as $file ) {
		$check = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, $file );
		if ( is_file( $check ) ) {
			require_once $check;
			return;
		}
	}

	// Fallback: try realpath to resolve symlinks / network drives.
	$real_base = realpath( dirname( __FILE__ ) );
	if ( $real_base ) {
		$real_dir = $real_base . DIRECTORY_SEPARATOR . $dir;
		$real_candidates = array(
			$real_dir . 'class-' . $file_name . '.php',
			$real_dir . 'abstract-' . $file_name . '.php',
			$real_dir . 'interface-' . $file_name . '.php',
			$real_dir . $file_name . '.php',
		);
		foreach ( $real_candidates as $file ) {
			if ( is_file( $file ) ) {
				require_once $file;
				return;
			}
		}
	}
} );
