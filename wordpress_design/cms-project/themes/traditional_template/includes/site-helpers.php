<?php
/**
 * includes/site-helpers.php - small site-level helpers.
 *
 * Loaded on every request via config/files.php ('always').
 */
defined( 'ABSPATH' ) || exit;

/**
 * Is a page section switched on?
 *
 * Reads admin/data/sections.json - a flat map of "section key => true/false".
 * Templates gate each section with this so any block can be shown or hidden
 * by editing ONE JSON file, no template edits:
 *
 *   <?php if ( nt_section_visible( 'stats' ) ) : ?>
 *       ... section markup ...
 *   <?php endif; ?>
 *
 * A key that is missing from the JSON defaults to visible, so new sections
 * appear until someone deliberately turns them off.
 *
 * @param string $key     Section identifier used in sections.json.
 * @param bool   $default Value when the key is absent. Default true.
 * @return bool
 */
function nt_section_visible( $key, $default = true ) {
	static $map = null;
	if ( null === $map ) {
		$data = nt_data( 'sections' );
		$map  = is_array( $data ) ? $data : array();
	}
	if ( ! array_key_exists( $key, $map ) ) {
		return (bool) $default;
	}
	return ! empty( $map[ $key ] );
}

/**
 * assets/js/legacy.js is a carried-over bundle whose forms section is wrapped
 * in a jQuery IIFE - `(function ($) { ... }(jQuery))`. Without jQuery on the
 * page that closing `}(jQuery))` throws a ReferenceError that aborts the whole
 * file, killing every script after it (mobile nav toggle, carousels, wizards).
 * WordPress ships jQuery registered but not loaded; enqueue it so legacy.js runs.
 */
add_action( 'wp_enqueue_scripts', 'nt_enqueue_jquery_for_legacy', 5 );
function nt_enqueue_jquery_for_legacy() {
	wp_enqueue_script( 'jquery' );
}

/**
 * Disable WordPress's 404 "guess" redirect. This is a virtual-routing theme -
 * canonical slugs live in config/pages.php + config/routes.php and the router
 * resolves them even before a real WP page row exists. WP's guesser, however,
 * runs first on template_redirect and 301s an unknown path to the "nearest"
 * real post whose slug merely starts the same way - e.g. /order/ was being
 * hijacked to a stray /ordertodeliver/ page, so page-order.php never rendered.
 * Turning the guess off lets the router own routing.
 */
add_filter( 'do_redirect_guess_404_permalink', '__return_false' );

/**
 * Render a page's sections from the JSON registry (admin/data/page_sections.json).
 *
 * Thin template-facing wrapper around the OOP feature class
 * NT_Section_Renderer (src/Sections/class-section-renderer.php) - that class is
 * the intermediate layer holding all "what to render / in what order / is it
 * visible / with what context" logic. Page templates only ever call this:
 *
 *     nt_render_sections( 'home' );
 *
 * Adding, re-ordering or toggling a section is a one-line edit in
 * page_sections.json - no PHP change. See the class docblock for the JSON shape.
 *
 * @param string $page_key Key into page_sections.json (e.g. 'home', 'about').
 */
function nt_render_sections( $page_key ) {
	if ( class_exists( 'NT_Section_Renderer' ) ) {
		NT_Section_Renderer::render_page( (string) $page_key );
	}
}
