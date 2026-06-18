<?php
/**
 * includes/adn-sidebar-helpers.php
 *
 * adn_get_page_sidebar_data( $page_id ) — reusable sidebar data builder.
 * Returns an array compatible with components/parts/contact_sidebar.php.
 * Usage on any page template:
 *   adn_component( 'parts/contact_sidebar', array(
 *       'page_sidebar' => adn_get_page_sidebar_data( get_queried_object_id() ),
 *   ) );
 */
defined( 'ABSPATH' ) || exit;

/**
 * Build sidebar data (contact details + optional page FAQs) for any page.
 *
 * @param int $page_id  WP post/page ID; pass 0 or omit for global FAQs only.
 * @param int $faq_limit Max sidebar FAQ items (default 3).
 * @return array  Keys: whatsapp, email, faqs, coming_soon (same shape as contact_sidebar).
 */
function adn_get_page_sidebar_data( int $page_id = 0, int $faq_limit = 3 ): array {
	// Base contact info comes from adn_service_contact_data() (merges JSON + DB).
	$sidebar = array();
	if ( function_exists( 'adn_service_contact_data' ) ) {
		$contact_data = adn_service_contact_data();
		if ( isset( $contact_data['contact_sidebar'] ) && is_array( $contact_data['contact_sidebar'] ) ) {
			$sidebar = $contact_data['contact_sidebar'];
		}
	}

	// FAQs: page-specific first, fall back to global.
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() && class_exists( 'AH_Faqs_Model' ) ) {
		try {
			$model = new AH_Faqs_Model();
			$faqs  = $page_id > 0 ? $model->get_for_page( $page_id ) : array();
			if ( empty( $faqs ) ) {
				$faqs = $model->get_global();
			}
			if ( ! empty( $faqs ) ) {
				$sidebar['faqs'] = array_slice( $faqs, 0, $faq_limit );
			}
		} catch ( Throwable $e ) {
			// If model fails, just show no FAQs.
		}
	}

	return $sidebar;
}
