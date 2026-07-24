<?php
/**
 * Provides a reusable terminology lookup API for the CMS plugin.
 *
 * Terms are supplied externally (e.g. by the active theme) via the
 * `cms_plugin_terms` filter. The plugin itself holds no path logic,
 * no class references, and no theme knowledge – keeping it fully reusable
 * across any client or theme.
 *
 * Usage (plugin code):
 *   AH_Term_Manager::get_label( 'related_links.article', 'Article' );
 *   AH_Term_Manager::get_terms();   // full array
 *
 * Usage (theme side – register terms via filter):
 *   add_filter( 'cms_plugin_terms', function( $terms ) {
 *       return array_merge( $terms, ADN_Real_Loader::json( 'terms' ) );
 *   } );
 */

namespace Ah\Cms\Feature\Taxonomy;

defined( 'ABSPATH' ) || exit;

class TermManager {

	/**
	 * Cached terms array.
	 * @var array|null
	 */
	private static $terms = null;

	/**
	 * Load terms via the `cms_plugin_terms` filter.
	 *
	 * The plugin supplies an empty array as the starting point. Any theme or
	 * mu-plugin that wants to provide terminology hooks into this filter and
	 * merges in its own data.
	 *
	 * @return array Associative array of terms.
	 */
	private static function load_terms() {
		if ( self::$terms !== null ) {
			return self::$terms;
		}

		// Start with no terms – the theme (or other code) fills this in
		// via the filter below.
		$terms = [];

		/**
		 * Filter: cms_plugin_terms
		 *
		 * Allows any theme or plugin to supply / modify the terminology array.
		 *
		 * @param array $terms Empty array provided by the plugin.
		 * @return array Merged terminology data.
		 */
		$terms = (array) apply_filters( 'cms_plugin_terms', $terms );

		// Cache for the lifetime of the request.
		self::$terms = $terms;
		return $terms;
	}

	/**
	 * Retrieve a term value using dot notation, e.g. "related_links.article".
	 *
	 * @param string $key Dot-separated key path.
	 * @param string $default Default value if key not found.
	 * @return string
	 */
	public static function get_label( $key, $default = '' ) {
		$terms = self::load_terms();
		$parts = explode( '.', $key );
		$value = $terms;
		foreach ( $parts as $part ) {
			if ( is_array( $value ) && array_key_exists( $part, $value ) ) {
				$value = $value[ $part ];
			} else {
				$value = $default;
				break;
			}
		}
		// Ensure we return a string.
		if ( is_array( $value ) ) {
			// If the final node is an array (e.g., contains sub-fields), json encode it.
			$value = wp_json_encode( $value );
		}
		return (string) $value;
	}

	/**
	 * Return the *entire* terminology array (raw JSON decoded).
	 * Useful when a caller needs to iterate over groups (e.g. link types,
	 * container suggestions, etc.) rather than a single label.
	 *
	 * @return array
	 */
	public static function get_terms(): array {
		return self::load_terms();
	}
}
