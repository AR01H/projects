<?php
/**
 * PSR-4 compliant autoloader.
 * 
 * Namespace: AHEcommerce
 * Maps to:   includes/
 */

spl_autoload_register( function ( $class ) {

	// project-specific namespace prefix
	$prefix = 'AHEcommerce\\';

	// base directory for the namespace prefix
	$base_dir = AH_ECOMMERCE_DIR . 'includes/';

	// does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// no, move to the next registered autoloader
		return;
	}

	// get the relative class name
	$relative_class = substr( $class, $len );

	// Explode the parts
	$parts = explode( '\\', $relative_class );
	
	// The last part is the class name itself. Convert to WP naming standard (class-name.php)
	$class_name = array_pop( $parts );
	
	// Base file name (kebab-case)
	$file_base_name = strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

	// The remaining parts become lowercase directory names
	$dir_path = '';
	if ( ! empty( $parts ) ) {
		$parts = array_map( 'strtolower', $parts );
		$parts = array_map( function( $part ) { return str_replace( '_', '-', $part ); }, $parts );
		$dir_path = implode( '/', $parts ) . '/';
	}

	$full_dir = $base_dir . $dir_path;
	
	// Because WP file names can be prefixed with class-, abstract-, or interface-, 
	// and we can't reliably know from just the class name (e.g., Service_Provider doesn't start with Interface_)
	// we will try them in order.
	
	$possible_files = array(
		$full_dir . 'class-' . $file_base_name,
		$full_dir . 'interface-' . $file_base_name,
		$full_dir . 'abstract-' . $file_base_name,
		$full_dir . $file_base_name // fallback
	);
	
	// If the class name explicitly had 'abstract_' or 'interface_' at the start, 
	// we might end up with 'class-abstract-module.php'. Let's add specific cleanups.
	if ( str_starts_with( strtolower( $class_name ), 'abstract_' ) ) {
		$clean_name = substr( strtolower( str_replace( '_', '-', $class_name ) ), 9 ) . '.php';
		array_unshift( $possible_files, $full_dir . 'abstract-' . $clean_name );
	}
	if ( str_starts_with( strtolower( $class_name ), 'interface_' ) ) {
		$clean_name = substr( strtolower( str_replace( '_', '-', $class_name ) ), 10 ) . '.php';
		array_unshift( $possible_files, $full_dir . 'interface-' . $clean_name );
	}

	foreach ( $possible_files as $file ) {
		if ( file_exists( $file ) ) {
			require $file;
			return;
		}
	}
} );
