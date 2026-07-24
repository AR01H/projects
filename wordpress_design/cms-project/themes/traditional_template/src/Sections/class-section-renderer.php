<?php
/**
 * src/Sections/class-section-renderer.php
 *
 * FEATURE: Page Sections
 * ----------------------
 * The intermediate layer that sits between page templates (UI) and the data.
 * A page template never lists components; it calls ONE method and this class
 * decides - from admin/data/page_sections.json - which section components to
 * render, in what order, whether each is visible, and with what context.
 *
 * Why a class (OOP): all the "what to render and how" logic lives in one
 * testable, self-documenting place. Templates stay dumb (pure UI), data stays
 * in JSON, and this class is the only thing that knows how to wire them.
 *
 * Add a section to any page  -> one line in page_sections.json (no PHP).
 * Change how sections render  -> only this class.
 *
 * @package NT\Sections
 */

defined( 'ABSPATH' ) || exit;

class NT_Section_Renderer {

	/**
	 * JSON registry file (admin/data/<DATA_KEY>.json) that maps page keys to
	 * an ordered list of section definitions.
	 */
	public const DATA_KEY = 'page_sections';

	/**
	 * Render every section registered for a page, in order.
	 *
	 * @param string $page_key e.g. 'home', 'about', 'gallery'.
	 */
	public static function render_page( string $page_key ): void {
		foreach ( self::sections_for( $page_key ) as $section ) {
			self::render_section( (array) $section );
		}
	}

	/**
	 * The ordered section list for a page, or [] if none / not registered.
	 *
	 * @param string $page_key
	 * @return array<int,array>
	 */
	public static function sections_for( string $page_key ): array {
		$map = function_exists( 'nt_data' ) ? nt_data( self::DATA_KEY ) : array();
		if ( ! is_array( $map ) || empty( $map[ $page_key ] ) || ! is_array( $map[ $page_key ] ) ) {
			return array();
		}
		return $map[ $page_key ];
	}

	/**
	 * Render one section definition.
	 *
	 * Section shape (all keys optional except `component`):
	 *   component (string)        components/<component>.php to render. Required.
	 *   key       (string)        visibility toggle via sections.json (nt_section_visible).
	 *   header    (string)        merge page_headers.json[header] into the context
	 *                             (used with component "parts/page_header").
	 *   args      (object)        extra context passed to the component.
	 *   variant   (string|array)  design variant(s) - wraps the section in
	 *                             .nt-variant.nt-variant--<name> so the SAME
	 *                             component can look different on each page
	 *                             (e.g. "dark", "compact", "split") with no PHP
	 *                             or component change. See "SECTION VARIANTS" in
	 *                             assets/css/vintage.css for the available names.
	 *
	 * @param array $section
	 */
	protected static function render_section( array $section ): void {
		$component = isset( $section['component'] ) ? (string) $section['component'] : '';
		if ( '' === $component ) {
			return;
		}

		// Visibility (admin/data/sections.json). Missing key = visible.
		$key = isset( $section['key'] ) ? (string) $section['key'] : '';
		if ( '' !== $key && function_exists( 'nt_section_visible' ) && ! nt_section_visible( $key ) ) {
			return;
		}

		if ( ! function_exists( 'nt_component' ) ) {
			return;
		}

		$context = self::build_context( $section );
		$wrapper = self::variant_class( $section );

		// The variant wrapper is what lets one component carry many designs:
		// CSS targets `.nt-variant--dark .nt-reviews { … }` etc.
		if ( '' !== $wrapper ) {
			echo '<div class="' . esc_attr( $wrapper ) . '">';
		}

		// nt_component() realpath-guards the path and extracts $context into
		// variables, so parts/page_header gets $title/$subtitle/etc while a
		// plain section component just reads its own JSON and ignores context.
		nt_component( $component, $context );

		if ( '' !== $wrapper ) {
			echo '</div>';
		}
	}

	/**
	 * Build the variant wrapper class list for a section, or '' when the section
	 * declares no variant. Accepts a single string or an array of names.
	 *
	 * @param array $section
	 * @return string
	 */
	protected static function variant_class( array $section ): string {
		if ( empty( $section['variant'] ) ) {
			return '';
		}
		$variants = is_array( $section['variant'] ) ? $section['variant'] : array( $section['variant'] );
		$classes  = array( 'nt-variant' );
		foreach ( $variants as $variant ) {
			$slug = sanitize_html_class( (string) $variant );
			if ( '' !== $slug ) {
				$classes[] = 'nt-variant--' . $slug;
			}
		}
		return ( count( $classes ) > 1 ) ? implode( ' ', $classes ) : '';
	}

	/**
	 * Assemble the context (variables) a section component receives: inline
	 * `args`, plus an optional page-header data block pulled from
	 * page_headers.json when `header` is set.
	 *
	 * @param array $section
	 * @return array
	 */
	protected static function build_context( array $section ): array {
		$context = ( isset( $section['args'] ) && is_array( $section['args'] ) ) ? $section['args'] : array();

		if ( ! empty( $section['header'] ) && function_exists( 'nt_data' ) ) {
			$headers = nt_data( 'page_headers' );
			$name    = (string) $section['header'];
			$hdr     = ( is_array( $headers ) && isset( $headers[ $name ] ) && is_array( $headers[ $name ] ) ) ? $headers[ $name ] : array();
			// Inline args win over the shared header block.
			$context = array_merge( $hdr, $context );
		}

		return $context;
	}
}
