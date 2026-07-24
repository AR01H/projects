<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class ContactContext {

	public static function getContext() {
		$data   = function_exists( 'adn_service_contact_data' ) ? adn_service_contact_data() : array();
		$chrome = function_exists( 'adn_service_site_chrome' )  ? adn_service_site_chrome()  : array();

		$form = isset( $data['form'] ) ? (array) $data['form'] : array();

		if ( function_exists( 'adn_cms_available' ) && adn_cms_available()
			&& function_exists( 'adn_cms_guide_parents' ) ) {

			$term_types = array();
			foreach ( adn_cms_guide_parents( 20 ) as $term ) {
				$slug = isset( $term->slug ) ? sanitize_key( $term->slug ) : '';
				$name = isset( $term->name ) ? (string) $term->name        : '';
				if ( '' === $slug || '' === $name ) {
					continue;
				}
				$icon        = ! empty( $term->icon_emoji ) ? (string) $term->icon_emoji : adn_term( 'icons.guide_fallback', '🏡' );
				$term_types[] = array( 'key' => $slug, 'icon' => $icon, 'label' => $name );
			}

			$term_types[] = array( 'key' => 'general', 'icon' => adn_term( 'icons.enquiry', '💬' ), 'label' => 'General Enquiry' );

			if ( ! empty( $term_types ) ) {
				$form['enquiry_types'] = $term_types;
			}
		}

		$resources = isset( $data['resources'] ) ? (array) $data['resources'] : array();

		$data['contact_sidebar'] = adn_get_page_sidebar_data( get_queried_object_id() );

		return array(
			'meta'            => isset( $data['meta'] )            ? (array) $data['meta']            : array(),
			'breadcrumb'      => isset( $data['breadcrumb'] )      ? (array) $data['breadcrumb']      : array(),
			'hero'            => isset( $data['hero'] )            ? (array) $data['hero']            : array(),
			'form'            => $form,
			'contact_sidebar' => isset( $data['contact_sidebar'] ) ? (array) $data['contact_sidebar'] : array(),
			'process_steps'   => isset( $data['process_steps'] )   ? (array) $data['process_steps']   : array(),
			'resources'       => $resources,
			'chrome'          => $chrome,
		);
	}
}
