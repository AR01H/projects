<?php
namespace Adn\Theme\Service;

use Adn\Theme\Feature\Form\Service\FormOptionsRegistry;

defined( 'ABSPATH' ) || exit;

class ContactContext {

	// ── Cache helpers ───────────────────────────────────────────
	private static function cacheGet( string $key ) {
		if ( class_exists( 'ADN_Cache' ) ) {
			return \ADN_Cache::get( $key, 'pages' );
		}
		return false;
	}

	private static function cacheSet( string $key, array $ctx ): void {
		if ( class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $key, $ctx, 'pages', get_option( 'ah_cache_expiry', 3600 ) );
		}
	}

	public static function getContext() {
		$cache_key = 'page_contact_context';
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) { return $cached; }

		$data   = function_exists( 'adn_service_contact_data' ) ? adn_service_contact_data() : array();
		$chrome = function_exists( 'adn_service_site_chrome' )  ? adn_service_site_chrome()  : array();

		$form = isset( $data['form'] ) ? (array) $data['form'] : array();

		// Use FormOptionsRegistry for enquiry types
		$registry   = new FormOptionsRegistry();
		$enquiry_types = $registry->getEnquiryTypes();

		if ( ! empty( $enquiry_types ) ) {
			$form['enquiry_types'] = $enquiry_types;
		}

		// Preferred contact method options
		$form['contact_methods'] = $registry->getContactPreferences();

		// When do you need help? options
		$form['help_timing'] = $registry->getHelpTiming();

		// Attach validation rules
		$form['validation'] = $registry->getValidationRules( 'contact' );

		$resources = isset( $data['resources'] ) ? (array) $data['resources'] : array();

		$data['contact_sidebar'] = function_exists( 'adn_get_page_sidebar_data' ) ? adn_get_page_sidebar_data( get_queried_object_id() ) : array();

		$result = array(
			'meta'            => isset( $data['meta'] )            ? (array) $data['meta']            : array(),
			'breadcrumb'      => isset( $data['breadcrumb'] )      ? (array) $data['breadcrumb']      : array(),
			'hero'            => isset( $data['hero'] )            ? (array) $data['hero']            : array(),
			'form'            => $form,
			'contact_sidebar' => isset( $data['contact_sidebar'] ) ? (array) $data['contact_sidebar'] : array(),
			'process_steps'   => isset( $data['process_steps'] )   ? (array) $data['process_steps']   : array(),
			'resources'       => $resources,
			'chrome'          => $chrome,
		);

		self::cacheSet( $cache_key, $result );
		return $result;
	}
}
