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
