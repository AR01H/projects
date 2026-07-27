<?php
/**
 * FormOptionsRegistry — Central registry for all form field options.
 *
 * All dropdown/select options for Contact and Guidance pages are defined here.
 * To add a new option, just add it to the appropriate array below.
 *
 * Architecture:
 *   1. Hardcoded options (always available)
 *   2. CMS guide parent terms (dynamic, from DB)
 *   3. Both merged, with CMS terms taking priority
 *
 * Usage:
 *   $registry = new FormOptionsRegistry();
 *   $enquiry_types = $registry->getEnquiryTypes();  // Contact page
 *   $help_options  = $registry->getHelpOptions();    // Guidance page
 *   $iam_options   = $registry->getIamOptions();     // Guidance page
 */

namespace Adn\Theme\Feature\Form\Service;

defined( 'ABSPATH' ) || exit;

class FormOptionsRegistry {

	// ══════════════════════════════════════════════════════════════
	// CONTACT PAGE — Enquiry Types (What best describes you?)
	// ══════════════════════════════════════════════════════════════

	private static array $enquiry_types = array(
		array( 'key' => 'buying',           'icon' => '🏠', 'label' => 'Buying a Home' ),
		array( 'key' => 'selling',          'icon' => '🔑', 'label' => 'Selling a Home' ),
		array( 'key' => 'moving',           'icon' => '🚚', 'label' => 'Moving Home' ),
		array( 'key' => 'new-to-uk',        'icon' => '🇬🇧', 'label' => 'New to the UK' ),
		array( 'key' => 'mortgage',         'icon' => '💰', 'label' => 'Mortgage Question' ),
		array( 'key' => 'conveyancing',     'icon' => '📋', 'label' => 'Conveyancing Question' ),
		array( 'key' => 'survey',           'icon' => '🔍', 'label' => 'Survey Question' ),
		array( 'key' => 'stamp-duty',       'icon' => '📊', 'label' => 'Stamp Duty Question' ),
		array( 'key' => 'first-time-buyer', 'icon' => '🎯', 'label' => 'First Time Buyer' ),
		array( 'key' => 'help-to-buy',      'icon' => '🏗️', 'label' => 'Help to Buy' ),
		array( 'key' => 'shared-ownership', 'icon' => '🤝', 'label' => 'Shared Ownership' ),
		array( 'key' => 'remortgage',       'icon' => '🔄', 'label' => 'Remortgage' ),
		array( 'key' => 'equity-release',   'icon' => '💎', 'label' => 'Equity Release' ),
		array( 'key' => 'property-invest',  'icon' => '📈', 'label' => 'Property Investment' ),
		array( 'key' => 'legal-advice',     'icon' => '⚖️', 'label' => 'Legal Advice' ),
		array( 'key' => 'insurance',        'icon' => '🛡️', 'label' => 'Insurance Question' ),
		array( 'key' => 'energy-rating',    'icon' => '⚡', 'label' => 'Energy Rating' ),
		array( 'key' => 'property-report',  'icon' => '📄', 'label' => 'Property Report' ),
		array( 'key' => 'auction',          'icon' => '🔨', 'label' => 'Property Auction' ),
		array( 'key' => 'leasehold',        'icon' => '📜', 'label' => 'Leasehold Question' ),
		array( 'key' => 'freehold',         'icon' => '🏡', 'label' => 'Freehold Question' ),
		array( 'key' => 'council-tax',      'icon' => '🏛️', 'label' => 'Council Tax' ),
		array( 'key' => 'rental',           'icon' => '🏢', 'label' => 'Rental Property' ),
		array( 'key' => 'commercial',       'icon' => '🏪', 'label' => 'Commercial Property' ),
		array( 'key' => 'referral',         'icon' => '📞', 'label' => 'Referral' ),
		array( 'key' => 'feedback',         'icon' => '💬', 'label' => 'Feedback' ),
		array( 'key' => 'complaint',        'icon' => '⚠️', 'label' => 'Complaint' ),
		array( 'key' => 'general',          'icon' => '💬', 'label' => 'General Property Question' ),
	);

	private static array $cms_term_icons = array(
		'buying'       => '🏠',
		'selling'      => '🔑',
		'moving'       => '🚚',
		'mortgage'     => '💰',
		'conveyancing' => '📋',
		'surveys'      => '🔍',
		'legal'        => '⚖️',
		'new-build'    => '🏗️',
		'first-time'   => '🎯',
		'renting'      => '🏢',
		'stamp-duty'   => '📊',
		'remortgage'   => '🔄',
		'investment'   => '📈',
		'auction'      => '🔨',
	);

	// ══════════════════════════════════════════════════════════════
	// GUIDANCE PAGE — Help Options (When do you need help?)
	// ══════════════════════════════════════════════════════════════

	private static array $help_options = array(
		'Buying a Home',
		'Selling a Home',
		'Mortgage Advice',
		'Conveyancing',
		'Survey & Valuation',
		'Moving Home',
		'Legal Advice',
		'New Build Property',
		'First Time Buyer',
		'Stamp Duty Advice',
		'Remortgage',
		'Equity Release',
		'Property Investment',
		'Energy Rating',
		'Property Report',
		'Leasehold & Freehold',
		'Council Tax',
		'Insurance',
		'Home Insurance',
		'Life Insurance',
		'Buildings Insurance',
		'Contents Insurance',
		'Buy to Let',
		'Commercial Property',
		'Property Auction',
		'Shared Ownership',
		'Help to Buy',
		'Right to Buy',
		'Other',
	);

	// ══════════════════════════════════════════════════════════════
	// GUIDANCE PAGE — I Am Options (I am a)
	// ══════════════════════════════════════════════════════════════

	private static array $iam_options = array(
		'First Time Buyer',
		'Home Mover',
		'Seller',
		'Landlord',
		'Investor',
		'New to the UK',
		'Property Developer',
		'Commercial Buyer',
		'Retiree',
		'Young Professional',
		'Family',
		'Single Buyer',
		'Joint Buyer',
		'Cash Buyer',
		'Mortgage Buyer',
		'Other',
	);

	// ══════════════════════════════════════════════════════════════
	// GUIDANCE PAGE — Timeframe Options
	// ══════════════════════════════════════════════════════════════

	private static array $time_options = array(
		'As soon as possible',
		'Within 1 week',
		'Within 2 weeks',
		'Within 1 month',
		'Within 2 months',
		'Within 3 months',
		'Within 6 months',
		'Within 1 year',
		'Just exploring',
		'No rush',
	);

	// ══════════════════════════════════════════════════════════════
	// GUIDANCE PAGE — Contact Method Options
	// ══════════════════════════════════════════════════════════════

	private static array $contact_methods = array(
		array( 'key' => 'email',    'label' => 'Email' ),
		array( 'key' => 'phone',    'label' => 'Phone' ),
		array( 'key' => 'whatsapp', 'label' => 'WhatsApp' ),
		array( 'key' => 'any',     'label' => 'Any Method' ),
	);

	// ══════════════════════════════════════════════════════════════
	// CONTACT PAGE — Preferred Contact Method
	// ══════════════════════════════════════════════════════════════

	private static array $contact_preferences = array(
		array( 'key' => 'email',      'label' => 'Email' ),
		array( 'key' => 'phone',      'label' => 'Phone' ),
		array( 'key' => 'whatsapp',   'label' => 'WhatsApp' ),
		array( 'key' => 'any',        'label' => 'Any Method' ),
	);

	// ══════════════════════════════════════════════════════════════
	// CONTACT PAGE — When Do You Need Help?
	// ══════════════════════════════════════════════════════════════

	private static array $help_timing = array(
		'Immediately',
		'Within 1 week',
		'Within 1 month',
		'Within 3 months',
		'Within 6 months',
		'Just exploring options',
		'No specific timeline',
	);

	// ══════════════════════════════════════════════════════════════
	// VALIDATION RULES
	// ══════════════════════════════════════════════════════════════

	private static array $validation_rules = array(
		'contact' => array(
			'name'             => array( 'required' => true, 'min' => 2, 'max' => 100 ),
			'email'            => array( 'required' => true, 'type' => 'email' ),
			'whatsapp'         => array( 'required' => false, 'type' => 'phone' ),
			'postcode'         => array( 'required' => false, 'pattern' => '/^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i', 'max' => 10 ),
			'message'          => array( 'required' => true, 'min' => 10, 'max' => 2000 ),
			'enquiry_type'     => array( 'required' => true ),
			'contact_method'   => array( 'required' => false ),
			'help_timing'      => array( 'required' => false ),
			'consent'          => array( 'required' => true ),
		),
		'guidance' => array(
			'name'             => array( 'required' => true, 'min' => 2, 'max' => 100 ),
			'email'            => array( 'required' => true, 'type' => 'email' ),
			'phone'            => array( 'required' => false, 'type' => 'phone' ),
			'help_with'        => array( 'required' => true ),
			'i_am'             => array( 'required' => true ),
			'requirement'      => array( 'required' => true, 'min' => 10, 'max' => 2000 ),
			'time_frame'       => array( 'required' => false ),
			'contact_method'   => array( 'required' => false ),
			'consent'          => array( 'required' => true ),
		),
	);

	// ══════════════════════════════════════════════════════════════
	// PUBLIC METHODS
	// ══════════════════════════════════════════════════════════════

	public function getEnquiryTypes(): array {
		$types = self::$enquiry_types;

		if ( function_exists( 'adn_cms_available' ) && adn_cms_available()
			&& function_exists( 'adn_cms_guide_parents' ) ) {

			$cms_keys = array_column( $types, 'key' );

			foreach ( adn_cms_guide_parents( 20 ) as $term ) {
				$slug = isset( $term->slug ) ? sanitize_key( $term->slug ) : '';
				$name = isset( $term->name ) ? (string) $term->name : '';
				if ( '' === $slug || '' === $name ) {
					continue;
				}
				if ( in_array( $slug, $cms_keys, true ) ) {
					continue;
				}
				$icon = ! empty( $term->icon_emoji ) ? (string) $term->icon_emoji
					: ( self::$cms_term_icons[ $slug ] ?? adn_term( 'icons.guide_fallback', '🏡' ) );

				$types[] = array( 'key' => $slug, 'icon' => $icon, 'label' => $name );
			}
		}

		return $types;
	}

	public function getHelpOptions(): array {
		$options = self::$help_options;

		if ( function_exists( 'adn_cms_available' ) && adn_cms_available()
			&& function_exists( 'adn_cms_guide_parents' ) ) {

			foreach ( adn_cms_guide_parents( 20 ) as $term ) {
				$name = isset( $term->name ) ? (string) $term->name : '';
				if ( '' !== $name && ! in_array( $name, $options, true ) ) {
					$options[] = $name;
				}
			}
		}

		return $options;
	}

	public function getIamOptions(): array {
		return self::$iam_options;
	}

	public function getTimeOptions(): array {
		return self::$time_options;
	}

	public function getContactMethods(): array {
		return self::$contact_methods;
	}

	public function getContactPreferences(): array {
		return self::$contact_preferences;
	}

	public function getHelpTiming(): array {
		return self::$help_timing;
	}

	public function getValidationRules( string $form ): array {
		return self::$validation_rules[ $form ] ?? array();
	}

	public function getAllOptions(): array {
		return array(
			'enquiry_types'      => $this->getEnquiryTypes(),
			'help_options'       => $this->getHelpOptions(),
			'iam_options'        => $this->getIamOptions(),
			'time_options'       => $this->getTimeOptions(),
			'contact_methods'    => $this->getContactMethods(),
			'contact_preferences'=> $this->getContactPreferences(),
			'help_timing'        => $this->getHelpTiming(),
			'validation'         => self::$validation_rules,
		);
	}
}
