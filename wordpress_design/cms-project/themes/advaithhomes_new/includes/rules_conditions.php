<?php
defined( 'ABSPATH' ) || exit;

/**
 * ADN_Rules
 *
 * Single source of truth for every AH_Rules_Engine trigger name used in this
 * theme. Use these constants everywhere instead of raw strings, so a renamed
 * trigger is caught at the point of definition, not buried in a submit handler.
 *
 * The engine itself lives in the CMS plugin (class AH_Rules_Engine); this file
 * only declares which triggers this theme fires. Always guard calls with
 * class_exists() so the theme keeps working when the plugin is inactive:
 *
 *   if ( class_exists( 'AH_Rules_Engine' ) ) {
 *       AH_Rules_Engine::evaluate( ADN_Rules::CONTACT_FORM, array(
 *           'name'         => $name,
 *           'email'        => $email,
 *           'phone'        => $phone,
 *           'message'      => $message,
 *           'site_url'     => home_url(),
 *           'submitted_at' => current_time( 'Y-m-d H:i:s' ),
 *       ), true );
 *   }
 *
 * NOTE: trigger VALUES are prefixed `advaith_` so they never collide with other
 * themes' rules in the shared {prefix}ah_rules table (canehouse uses `sugarcane_`).
 */
class ADN_Rules {

	// ── Front-end form triggers ───────────────────────────────────────────────

	/** General contact form submission (pages/page-contact.php). */
	const CONTACT_FORM     = 'advaith_contact_form';

	/** Property / buying enquiry submission. */
	const PROPERTY_ENQUIRY = 'advaith_property_enquiry';

	/** "Request a callback" submission. */
	const CALLBACK_REQUEST = 'advaith_callback_request';

	/**
	 * Convenience list of all triggers this theme owns.
	 * Handy for admin dropdowns or documentation.
	 *
	 * @return array<string,string> trigger constant value => human label
	 */
	public static function all() {
		return array(
			self::CONTACT_FORM     => 'Contact Form Submitted',
			self::PROPERTY_ENQUIRY => 'Property Enquiry Submitted',
			self::CALLBACK_REQUEST => 'Callback Requested',
		);
	}
}
