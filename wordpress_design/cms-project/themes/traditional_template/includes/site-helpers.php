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
 *   <?php if ( app_section_visible( 'stats' ) ) : ?>
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
function app_section_visible( $key, $default = true ) {
	static $map = null;
	if ( null === $map ) {
		$data = App_Helpers::data( 'sections' );
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
add_action( 'wp_enqueue_scripts', 'app_enqueue_jquery_for_legacy', 5 );
function app_enqueue_jquery_for_legacy() {
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
 *     app_render_sections( 'home' );
 *
 * Adding, re-ordering or toggling a section is a one-line edit in
 * page_sections.json - no PHP change. See the class docblock for the JSON shape.
 *
 * @param string $page_key Key into page_sections.json (e.g. 'home', 'about').
 */
function app_render_sections( $page_key ) {
	if ( class_exists( 'NT_Section_Renderer' ) ) {
		NT_Section_Renderer::render_page( (string) $page_key );
	}
}

/* ═══════════════════════════════════════════════════════════════════════════
   UI KIT - thin template-facing wrappers.

   All the logic lives in the feature classes (src/Ui/, src/Dialogs/); these
   one-liners exist so templates read as plain English. See src/README.md.
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * An inline SVG icon from the shared set (src/Ui/class-icons.php).
 *
 *   app_icon( 'download' );                    // echo
 *   $svg = app_icon( 'download', '', false );  // return
 *
 * @param string $name  Icon name - see NT_Icons::names().
 * @param string $class Extra CSS class(es).
 * @param bool   $echo  Echo (default) or return.
 * @return string
 */
function app_icon( $name, $class = '', $echo = true ) {
	$svg = class_exists( 'NT_Icons' ) ? NT_Icons::get( (string) $name, (string) $class ) : '';
	if ( $echo ) {
		echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput -- constant SVG, class escaped in NT_Icons.
	}
	return $svg;
}

/**
 * Render an inline alert / note box (src/Dialogs/class-alert.php).
 *
 *   app_alert( array( 'tone' => 'warning', 'title' => '…', 'body' => '…' ) );
 *
 * @param array $args See NT_Alert::normalise().
 */
function app_alert( $args = array() ) {
	if ( class_exists( 'NT_Alert' ) ) {
		NT_Alert::render( (array) $args );
	}
}

/**
 * Print the attributes that turn any element into a dialog trigger, and queue
 * that dialog for output in the footer (src/Dialogs/class-dialog.php).
 *
 *   <button <?php app_dialog_trigger( 'brochure' ); ?>>Get the brochure</button>
 *
 * Prints nothing when the id is not declared in admin/data/dialogs.json, so a
 * typo degrades to an inert button instead of a JS error.
 *
 * @param string $id Dialog key in dialogs.json.
 */
function app_dialog_trigger( $id ) {
	if ( class_exists( 'NT_Dialog' ) ) {
		echo NT_Dialog::trigger_attrs( (string) $id ); // phpcs:ignore WordPress.Security.EscapeOutput -- attributes escaped in the class.
	}
}

/**
 * Register a dialog that is NOT in dialogs.json - one a component builds out
 * of its own content, such as an application form per job opening.
 *
 *   $id = app_dialog_add( 'apply-driver', array(
 *       'title' => 'Delivery driver',
 *       'form'  => 'form_apply',
 *   ) );
 *   <button type="button" data-nt-dialog-open="<?php echo esc_attr( $id ); ?>">…
 *
 * Returns the DOM id the browser will use. Like every other dialog, the
 * markup is built client-side from this data - nothing is printed here.
 *
 * @param string $id  Unique id for this request.
 * @param array  $def Same shape as a dialogs.json entry.
 * @return string DOM id.
 */
function app_dialog_add( $id, $def = array() ) {
	if ( ! class_exists( 'NT_Dialog' ) ) {
		return '';
	}
	return NT_Dialog::add( (string) $id, (array) $def );
}

/**
 * A shared UI string from admin/data/ui.json -> labels (src/Ui/class-ui.php).
 * Keeps button wording ("Cancel", "Read more") out of PHP and JS.
 *
 * @param string $key     Label key.
 * @param string $default Fallback when the JSON has no value.
 * @return string
 */
function app_label( $key, $default = '' ) {
	return class_exists( 'NT_Ui' ) ? NT_Ui::label( (string) $key, (string) $default ) : (string) $default;
}

/**
 * Hand assets/js/ui-kit.js everything it needs as `window.ntUi`: the labels
 * and aria strings from ui.json, the tone icons, the timings, the site
 * notices that apply today, and the data for every dialog this page can open.
 * The browser builds that markup - PHP never prints a dialog or a notice.
 *
 * WHY wp_footer AND NOT wp_enqueue_scripts: the dialog payload is built from
 * a queue that fills up as sections render (a component calling
 * app_dialog_trigger() adds to it). wp_enqueue_scripts runs in the <head>,
 * long before any of that has happened, so the queue would always be empty.
 * Priority 1 is still ahead of wp_print_footer_scripts (priority 20).
 */
add_action( 'wp_footer', 'app_localize_ui_kit', 1 );
function app_localize_ui_kit() {
	if ( ! class_exists( 'NT_Ui' ) || ! wp_script_is( 'app-ui-kit', 'registered' ) ) {
		return;
	}
	wp_add_inline_script( 'app-ui-kit', 'window.ntUi=' . wp_json_encode( NT_Ui::js_config() ) . ';', 'before' );
}
