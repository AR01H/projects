<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * NavigationBridgeService — Intermediate theme-level service for CMS Plugin Navigation Editor integration.
 *
 * Dynamically loads and normalizes header navigation, dropdowns, submenus, CTA buttons,
 * and footer columns from `admin.php?page=ah-navigation` (`ah_cms_navigation`, `ah_cms_nav_cta`, `ah_cms_footer`).
 */
final class NavigationBridgeService {

	/**
	 * Get primary header navigation items from CMS Plugin or fallback
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_primary_navigation(): array {
		$items = array();

		// 1. Direct class from CMS Plugin
		if ( class_exists( '\Ah\Cms\Feature\Navigation\Controller\NavigationAdminController' ) ) {
			$raw_items = \Ah\Cms\Feature\Navigation\Controller\NavigationAdminController::get_navigation_data();
			if ( ! empty( $raw_items ) && is_array( $raw_items ) ) {
				$items = self::normalize_items( $raw_items );
			}
		}

		// 2. Direct options check if plugin class not booted
		if ( empty( $items ) && function_exists( 'get_option' ) ) {
			$opt = get_option( 'ah_cms_navigation', null );
			if ( ! empty( $opt ) ) {
				$decoded = is_string( $opt ) ? json_decode( $opt, true ) : $opt;
				if ( ! empty( $decoded ) && is_array( $decoded ) ) {
					$items = self::normalize_items( $decoded );
				}
			}
		}

		// 3. JSON fallback from config/navigation.json
		if ( empty( $items ) ) {
			$fallback = (array) ( JsonFileProvider::read( 'config/navigation.json' ) ?? array() );
			$items    = self::normalize_items( (array) ( $fallback['primary'] ?? array() ) );
		}

		return $items;
	}

	/**
	 * Get header CTA action button data from CMS Plugin or fallback
	 *
	 * @return array<string, string>
	 */
	public static function get_header_cta(): array {
		$cta = array();

		// 1. Direct CMS Plugin class
		if ( class_exists( '\Ah\Cms\Feature\Navigation\Controller\NavigationAdminController' ) ) {
			$cta = \Ah\Cms\Feature\Navigation\Controller\NavigationAdminController::get_nav_cta_data();
		}

		// 2. Direct option check
		if ( empty( $cta ) && function_exists( 'get_option' ) ) {
			$opt = get_option( 'ah_cms_nav_cta', null );
			if ( ! empty( $opt ) ) {
				$decoded = is_string( $opt ) ? json_decode( $opt, true ) : $opt;
				if ( is_array( $decoded ) ) {
					$cta = $decoded;
				}
			}
		}

		// 3. Fallback from config/navigation.json (also the ultimate default -
		// nothing hardcoded here duplicating what that file already defines)
		$fallback = (array) ( JsonFileProvider::read( 'config/navigation.json' ) ?? array() );
		$fb_cta   = (array) ( $fallback['header_cta'] ?? array() );

		$label    = (string) ( $cta['label'] ?? ( $fb_cta['label'] ?? '' ) );
		$sublabel = (string) ( $cta['sublabel'] ?? ( $fb_cta['sublabel'] ?? '' ) );
		$route    = (string) ( $cta['url'] ?? ( $cta['route'] ?? ( $fb_cta['route'] ?? '' ) ) );

		return array(
			'label'    => $label,
			'sublabel' => $sublabel,
			'route'    => $route,
			'url'      => UrlHelper::resolve( $route ),
		);
	}

	/**
	 * Get footer navigation / columns data from CMS Plugin or fallback
	 *
	 * @return array<string, mixed>
	 */
	public static function get_footer_data(): array {
		$footer = array();

		// 1. Direct CMS Plugin class
		if ( class_exists( '\Ah\Cms\Feature\Navigation\Controller\NavigationAdminController' ) ) {
			$footer = \Ah\Cms\Feature\Navigation\Controller\NavigationAdminController::get_footer_data();
		}

		// 2. Direct option check
		if ( empty( $footer['columns'] ) && function_exists( 'get_option' ) ) {
			$opt = get_option( 'ah_cms_footer', null );
			if ( ! empty( $opt ) ) {
				$decoded = is_string( $opt ) ? json_decode( $opt, true ) : $opt;
				if ( is_array( $decoded ) ) {
					$footer = $decoded;
				}
			}
		}

		return (array) $footer;
	}

	/**
	 * Normalize navigation items into a unified structure
	 *
	 * @param array<int, mixed> $raw_items
	 * @return array<int, array<string, mixed>>
	 */
	private static function normalize_items( array $raw_items ): array {
		$normalized = array();

		foreach ( $raw_items as $index => $item ) {
			$item = (array) $item;

			// Skip hidden items from Navigation Builder
			if ( isset( $item['visible'] ) && ! $item['visible'] ) {
				continue;
			}

			$label = trim( (string) ( $item['label'] ?? '' ) );
			if ( '' === $label ) {
				continue;
			}

			$type     = (string) ( $item['type'] ?? 'link' );
			$url      = (string) ( $item['url'] ?? '#' );
			$sub_raw  = (array) ( $item['submenu'] ?? ( $item['children'] ?? array() ) );
			$children = array();

			foreach ( $sub_raw as $sub_item ) {
				$sub_item  = (array) $sub_item;
				$sub_label = trim( (string) ( $sub_item['label'] ?? '' ) );
				if ( '' === $sub_label ) {
					continue;
				}

				$icon = trim( (string) ( $sub_item['icon'] ?? '' ) );
				if ( '' !== $icon && 0 === strpos( $sub_label, $icon ) ) {
					$sub_label = trim( substr( $sub_label, strlen( $icon ) ) );
				}

				$children[] = array(
					'label'       => $sub_label,
					'url'         => (string) ( $sub_item['url'] ?? '#' ),
					'description' => (string) ( $sub_item['description'] ?? '' ),
					'icon'        => $icon,
					'css_class'   => (string) ( $sub_item['css_class'] ?? '' ),
					'highlight'   => ! empty( $sub_item['highlight'] ),
				);
			}

			$has_kids = ! empty( $children );

			$normalized[] = array(
				'id'          => (string) ( $item['id'] ?? ( 'nav-' . $index ) ),
				'label'       => $label,
				'type'        => $has_kids ? 'dropdown' : $type,
				'url'         => ( $type === 'dropdown' && empty( $url ) ) ? '#' : $url,
				'icon'        => (string) ( $item['icon'] ?? '' ),
				'description' => (string) ( $item['description'] ?? '' ),
				'css_class'   => (string) ( $item['css_class'] ?? '' ),
				'panel_image' => (string) ( $item['panel_image'] ?? '' ),
				'children'    => $children,
			);
		}

		return $normalized;
	}

}
