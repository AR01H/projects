<?php
namespace Adn\Theme\Service;

use Adn\Theme\Feature\Form\Service\FormOptionsRegistry;

defined( 'ABSPATH' ) || exit;

class GuidanceContext {

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

	/**
	 * @param string|null $related_posts_builder RelatedPostsBuilder class name or null for default.
	 */
	public static function getContext( $related_posts_builder = null ) {
		$related_posts_builder = $related_posts_builder ?: \Adn\Theme\Shared\RelatedPostsBuilder::class;
		$cache_key = 'page_guidance_context';
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) { return $cached; }

		$data   = function_exists( 'adn_service_guidance_data' ) ? adn_service_guidance_data() : array();
		$chrome = function_exists( 'adn_service_site_chrome' )   ? adn_service_site_chrome()   : array();

		$form     = isset( $data['form'] )     ? (array) $data['form']     : array();
		$services = isset( $data['services'] ) ? (array) $data['services'] : array();

		// Use FormOptionsRegistry for all form options
		$registry = new FormOptionsRegistry();

		// Help options (I am looking for help with)
		$form['help_options'] = $registry->getHelpOptions();

		// I am options (I am a)
		$form['iam_options'] = $registry->getIamOptions();

		// Timeframe options
		$form['time_options'] = $registry->getTimeOptions();

		// Contact method options
		$form['contact_methods'] = $registry->getContactMethods();

		// Validation rules
		$form['validation'] = $registry->getValidationRules( 'guidance' );

		// Build services from CMS guide parents
		if ( function_exists( 'adn_cms_available' ) && adn_cms_available()
			&& function_exists( 'adn_cms_guide_parents' ) ) {

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

				$svc_items[] = array(
					'icon'  => $icon,
					'title' => $name,
					'desc'  => $desc,
					'url'   => '/' . $slug . '/',
					'cta'   => adn_term( 'guidance_page.view_guides', 'View Guides' ),
				);
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

		$result = array(
			'meta'            => isset( $data['meta'] )        ? (array) $data['meta']        : array(),
			'breadcrumb'      => isset( $data['breadcrumb'] )  ? (array) $data['breadcrumb']  : array(),
			'hero'            => isset( $data['hero'] )        ? (array) $data['hero']        : array(),
			'form'            => $form,
			'services'        => $services,
			'contact_sidebar' => $contact_sidebar,
			'why_choose'      => isset( $data['why_choose'] )  ? (array) $data['why_choose']  : array(),
			'chrome'          => $chrome,
			'latest_news' => $related_posts_builder::latestNewsWidget( 3 ),
			'latest_updates' => $related_posts_builder::latestUpdatesWidget( 3 ),
		);

		self::cacheSet( $cache_key, $result );
		return $result;
	}
}
