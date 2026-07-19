<?php
namespace AHEcommerce\Theme;

/**
 * Template Loader.
 * Allows the plugin to serve default templates that can be overridden by the active theme.
 */
class Template_Loader {

	/**
	 * Get a template part.
	 * Searches the active theme first, then falls back to the plugin's templates directory.
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialized template.
	 * @param array  $args Additional arguments passed to the template.
	 */
	public static function get_template_part( $slug, $name = '', $args = array() ) {
		$template = '';

		// Look in theme/child theme first
		if ( $name ) {
			$template = locate_template( array( "{$slug}-{$name}.php", "ecommerce/{$slug}-{$name}.php" ) );
		}

		// Look for generic template in theme
		if ( ! $template ) {
			$template = locate_template( array( "{$slug}.php", "ecommerce/{$slug}.php" ) );
		}

		// Fallback to plugin templates
		if ( ! $template ) {
			$file = AH_ECOMMERCE_DIR . 'templates/' . $slug . ( $name ? "-{$name}" : '' ) . '.php';
			if ( file_exists( $file ) ) {
				$template = $file;
			}
		}

		if ( $template ) {
			if ( ! empty( $args ) && is_array( $args ) ) {
				extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			}
			include $template;
		}
	}
}
