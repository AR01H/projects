<?php
/**
 * config/ajax.php - AJAX action registry.
 *
 * core/ajax.php loops this array and registers every action on admin-ajax.php.
 * The generic dispatcher enforces NONCE + CAPABILITY *before* your callback
 * runs, so a handler can never forget its security checks.
 *
 * ADD AN AJAX CALL (2 steps):
 *   1. Add an entry below.
 *   2. Write the callback function in the 'file' you point to.
 *
 * Call it from JS with the built-in helper (assets/js/common.js):
 *   NT.ajax( 'contact_submit', { name: 'A', email: 'a@b.c' } )
 *      .then( function ( res ) { ... } );
 *   The helper adds the wp action name (nt_contact_submit) and the per-action
 *   nonce automatically - nonces for every action are localized on all pages.
 *
 * Entry keys:
 *   callback   (string) PHP function to run. Required.
 *   file       (string) Theme-relative file that defines the callback.
 *                       Lazy-loaded only when this action actually fires.
 *   public     (bool)   true = logged-out visitors allowed (wp_ajax_nopriv_).
 *   capability (string) Extra current_user_can() gate. '' = none.
 *   nonce      (bool)   Verify the per-action nonce. Default true - only set
 *                       false for truly anonymous fire-and-forget endpoints.
 */

defined( 'ABSPATH' ) || exit;

return array(

	// Contact form submission (public, nonce-checked).
	'contact_submit' => array(
		'callback' => 'nt_ajax_contact_submit',
		'file'     => 'handlers/ajax/contact.php',
		'public'   => true,
	),

	// Live post search used by the header search box (public, nonce-checked).
	'search_posts' => array(
		'callback' => 'nt_ajax_search_posts',
		'file'     => 'handlers/ajax/search.php',
		'public'   => true,
	),

	// Order-to-deliver wizard submission (public, nonce-checked).
	'order_submit' => array(
		'callback' => 'nt_ajax_order_submit',
		'file'     => 'handlers/ajax/order.php',
		'public'   => true,
	),

	// Generic multi-step lead form (order / franchise / events / any wizard).
	'lead_submit' => array(
		'callback' => 'nt_ajax_lead_submit',
		'file'     => 'handlers/ajax/lead.php',
		'public'   => true,
	),

	// Example of an admin-only action:
	// 'clear_cache' => array(
	//     'callback'   => 'nt_ajax_clear_cache',
	//     'file'       => 'handlers/ajax/admin-tools.php',
	//     'public'     => false,
	//     'capability' => 'manage_options',
	// ),
);
