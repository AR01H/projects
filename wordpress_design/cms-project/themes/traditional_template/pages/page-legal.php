<?php
/**
 * Template Name: Legal document
 *
 * ONE template serving EVERY policy page - privacy, cookies, terms,
 * delivery, accessibility. Which document it renders is decided by the page
 * key the router stamped, mapped to a JSON file in admin/data/legal.json:
 *
 *   "privacy-policy" : "legal_privacy"
 *   "cookie-policy"  : "legal_cookies"
 *   "terms"          : "legal_terms"
 *
 * So adding another policy is: one entry in config/pages.php pointing here,
 * one line in legal.json, one new admin/data/legal_<name>.json. No new PHP.
 *
 * Registered as 'privacy-policy', 'cookie-policy' and 'terms' in
 * config/pages.php - all three share this file.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nt_map      = app_data( 'legal' );
$nt_page_key = (string) get_query_var( 'app_active_page' );

// Fall back to the first document in the map, so a page registered here but
// not yet listed still renders something rather than a blank screen.
$nt_documents = is_array( $nt_map['documents'] ?? null ) ? $nt_map['documents'] : array();
$nt_source    = (string) ( $nt_documents[ $nt_page_key ] ?? reset( $nt_documents ) );
?>
<div id="main-content" class="site-main app-legal-page">
	<?php
	if ( '' !== $nt_source ) {
		app_component( 'legal-document', array( 'source' => $nt_source ) );
	}

	// Anything the JSON wants under every policy - a contact promo, the
	// "change your cookie choices" link, a newsletter sign-up.
	app_render_sections( 'legal_after' );
	?>
</div>
<?php get_footer(); ?>
