<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class GuideContext {

	public static function getContext( $slug = '' ) {
		if ( '' === $slug ) {
			$page = get_queried_object();
			$slug = ( $page instanceof \WP_Post ) ? (string) $page->post_name : '';
		}
		$slug = sanitize_key( $slug );
		$cache_key = 'page_guide_context_' . $slug;
		if ( class_exists( 'ADN_Cache' ) ) {
			$cached = \ADN_Cache::get( $cache_key, 'pages' );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$data   = function_exists( 'adn_service_guide_data' ) ? adn_service_guide_data( $slug ) : array();
		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome()      : array();

		$ctx = array(
			'slug'          => $slug,
			'meta'          => isset( $data['meta'] )          ? (array) $data['meta']          : array(),
			'breadcrumb'    => isset( $data['breadcrumb'] )    ? (array) $data['breadcrumb']    : array(),
			'article'       => isset( $data['article'] )       ? (array) $data['article']       : array(),
			'key_takeaways' => isset( $data['key_takeaways'] ) ? (array) $data['key_takeaways'] : array(),
			'toc'           => isset( $data['toc'] )           ? (array) $data['toc']           : array(),
			'sections'      => isset( $data['sections'] )      ? (array) $data['sections']      : array(),
			'feedback'      => isset( $data['feedback'] )      ? (array) $data['feedback']      : array(),
			'author'        => isset( $data['author'] )        ? (array) $data['author']        : array(),
			'sidebar'       => isset( $data['sidebar'] )       ? (array) $data['sidebar']       : array(),
			'stay_informed' => isset( $data['stay_informed'] ) ? (array) $data['stay_informed'] : array(),
			'chrome'        => $chrome,
		);

		if ( class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $cache_key, $ctx, 'pages', get_option( 'ah_cache_expiry', 3600 ) );
		}
		return $ctx;
	}
}
