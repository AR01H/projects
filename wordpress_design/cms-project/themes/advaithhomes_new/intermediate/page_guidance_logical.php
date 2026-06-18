<?php
defined( 'ABSPATH' ) || exit;

function adn_guidance_get_context() {
	$data   = function_exists( 'adn_service_guidance_data' ) ? adn_service_guidance_data() : array();
	$chrome = function_exists( 'adn_service_site_chrome' )   ? adn_service_site_chrome()   : array();

	$form     = isset( $data['form'] )     ? (array) $data['form']     : array();
	$services = isset( $data['services'] ) ? (array) $data['services'] : array();

	// ── Overlay form.help_options + services.items from live CMS parent terms ─
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available()
		&& function_exists( 'adn_cms_guide_parents' ) ) {

		$help_opts = array();
		$svc_items = array();

		foreach ( adn_cms_guide_parents( 20 ) as $term ) {
			$slug = isset( $term->slug ) ? sanitize_key( $term->slug ) : '';
			$name = isset( $term->name ) ? (string) $term->name        : '';
			if ( '' === $slug || '' === $name ) {
				continue;
			}
			$icon = ! empty( $term->icon_emoji )  ? (string) $term->icon_emoji  : adn_term( 'icons.guide_fallback', '🏡' );
			$desc = ! empty( $term->description ) ? (string) $term->description
			                                       : sprintf( adn_term( 'guidance_page.explore_guidance', 'Explore %s guidance and resources' ), $name );

			$help_opts[] = $name;

			$svc_items[] = array(
				'icon'  => $icon,
				'title' => $name,
				'desc'  => $desc,
				'url'   => '/' . $slug . '/',
				'cta'   => 'View Guides',
			);
		}

		// Append "Other" option at the end of the form dropdown.
		$help_opts[] = adn_term( 'guidance_page.form_other', 'Other' );

		if ( ! empty( $help_opts ) ) {
			$form['help_options'] = $help_opts;
		}

		if ( ! empty( $svc_items ) ) {
			if ( empty( $services['heading'] ) ) {
				$services['heading'] = adn_term( 'guidance_page.services_heading', 'We can help you with' );
			}
			$services['items'] = $svc_items;
		}
	}

	return array(
		'meta'        => isset( $data['meta'] )        ? (array) $data['meta']        : array(),
		'breadcrumb'  => isset( $data['breadcrumb'] )  ? (array) $data['breadcrumb']  : array(),
		'hero'        => isset( $data['hero'] )        ? (array) $data['hero']        : array(),
		'form'        => $form,
		'services'    => $services,
		'why_choose'  => isset( $data['why_choose'] )  ? (array) $data['why_choose']  : array(),
		'chrome'      => $chrome,
	);
}
