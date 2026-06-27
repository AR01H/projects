<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Rules
 *
 * Single source of truth for every AH_Workflow_Manager trigger name used in this theme.
 * Use these constants everywhere instead of raw strings so a renamed trigger is
 * caught at the point of definition, not buried in a submit handler.
 *
 * Usage:
 *   AH_Workflow_Manager::evaluate( CH_Rules::CONTACT_FORM, $context, true );
 */
class CH_Rules {

	// ── Front-end form triggers ───────────────────────────────────────────────

	/** General contact / enquiry form submission */
	const CONTACT_FORM      = 'sugarcane_contact_form';

	/** Order-to-Deliver wizard submission */
	const ORDER_TO_DELIVER  = 'sugarcane_order_to_deliver';

	/** Event / occasion booking wizard submission */
	const BOOKING_REQUEST   = 'sugarcane_booking_request';

	/** Franchise enquiry wizard submission */
	const FRANCHISE_ENQUIRY = 'sugarcane_franchise_enquiry';
}
