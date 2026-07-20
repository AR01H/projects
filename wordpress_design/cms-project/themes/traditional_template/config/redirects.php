<?php
/**
 * config/redirects.php - Redirect rules.
 *
 * core/redirects.php loops this array on template_redirect (before anything
 * renders). Key = the incoming URL path (no leading/trailing slashes).
 *
 * Entry keys:
 *   to     (string) Destination. '/path/' (internal) or full https:// URL.
 *                   External hosts must be whitelisted via the
 *                   'allowed_redirect_hosts' filter (see ARCHITECTURE.md).
 *   status (int)    301 permanent (default) or 302 temporary.
 *
 * NOTE: the sitewide coming-soon gate is NOT a rule here - it is driven by
 * the NT_COMING_SOON flag in config/theme.php.
 */

defined( 'ABSPATH' ) || exit;

return array(

	// Old URL structure -> new pages.
	'contact-us-old' => array( 'to' => '/contact/', 'status' => 301 ),

	// 'promo' => array( 'to' => 'https://partner.example.com/offer', 'status' => 302 ),
);
