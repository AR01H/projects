<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class GuidanceContext {

	public static function getContext() {
		$data   = function_exists( 'adn_service_guidance_data' ) ? adn_service_guidance_data() : array();
		$chrome = function_exists( 'adn_service_site_chrome' )   ? adn_service_site_chrome()   : array();

		$form     = isset( $data['form'] )     ? (array) $data['form']     : array();
		$services = isset( $data['services'] ) ? (array) $data['services'] : array();

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

		$contact_sidebar = isset( $data['contact_sidebar'] ) ? (array) $data['contact_sidebar'] : array();
		if ( function_exists( 'adn_get_page_sidebar_data' ) ) {
			$global_sidebar = adn_get_page_sidebar_data( get_queried_object_id() );
			foreach ( array( 'whatsapp', 'email', 'phone', 'address' ) as $key ) {
				if ( ! isset( $contact_sidebar[$key] ) && isset( $global_sidebar[$key] ) ) {
					$contact_sidebar[$key] = $global_sidebar[$key];
				}
			}
		}

		return array(
			'meta'            => isset( $data['meta'] )        ? (array) $data['meta']        : array(),
			'breadcrumb'      => isset( $data['breadcrumb'] )  ? (array) $data['breadcrumb']  : array(),
			'hero'            => isset( $data['hero'] )        ? (array) $data['hero']        : array(),
			'form'            => $form,
			'services'        => $services,
			'contact_sidebar' => $contact_sidebar,
			'why_choose'      => isset( $data['why_choose'] )  ? (array) $data['why_choose']  : array(),
			'chrome'          => $chrome,
			'latest_news' => array(
				'heading' => array(
					'title'      => adn_term( 'labels.latest_news', 'Latest News' ),
					'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
					'link_url'   => defined( 'SITE_NEWS_URL' ) ? SITE_NEWS_URL : '/',
				),
				'items' => adn_shared_latest_news_items( 3 ),
			),
			'latest_updates' => array(
				'heading' => array(
					'title'      => adn_term( 'labels.latest_updates', 'Latest Updates' ),
					'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
					'link_url'   => defined( 'SITE_REGULATIONS_URL' ) ? SITE_REGULATIONS_URL : '/',
				),
				'items' => function_exists( 'adn_shared_latest_updates_items' ) ? adn_shared_latest_updates_items( 3 ) : array(),
			),
		);
	}
}
