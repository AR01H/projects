<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\NavigationService;
use VintageSoul\Services\RouteService;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

final class FooterController {

	public function prepare(): array {
		$cms_footer = NavigationService::footer();
		$fallback   = (array) ( JsonFileProvider::read( 'data/content/footer.json' ) ?? array() );
		$labels     = (array) ( $fallback['labels'] ?? array() );

		// Columns from CMS Plugin Navigation Editor or fallback
		$columns = array();
		if ( ! empty( $cms_footer['columns'] ) && is_array( $cms_footer['columns'] ) ) {
			foreach ( $cms_footer['columns'] as $col ) {
				$col_items = array();
				foreach ( (array) ( $col['items'] ?? array() ) as $c_item ) {
					$c_label = trim( (string) ( $c_item['label'] ?? '' ) );
					$c_url   = (string) ( $c_item['url'] ?? '' );
					if ( '' === $c_label || '' === $c_url ) {
						continue;
					}
					$col_items[] = array(
						'label'     => $c_label,
						'url'       => UrlHelper::resolve( $c_url ),
						'highlight' => ! empty( $c_item['highlight'] ),
					);
				}
				if ( ! empty( $col_items ) || ! empty( $col['title'] ) ) {
					$columns[] = array(
						'title' => (string) ( $col['title'] ?? 'Links' ),
						'items' => $col_items,
					);
				}
			}
		}

		// Legal links from CMS Plugin Navigation Editor or fallback
		$legal_links = array();
		if ( ! empty( $cms_footer['legal_links'] ) && is_array( $cms_footer['legal_links'] ) ) {
			foreach ( $cms_footer['legal_links'] as $l_item ) {
				$l_label = trim( (string) ( $l_item['label'] ?? '' ) );
				$l_url   = (string) ( $l_item['url'] ?? '' );
				if ( '' === $l_label || '' === $l_url ) {
					continue;
				}
				$legal_links[] = array(
					'label' => $l_label,
					'url'   => UrlHelper::resolve( $l_url ),
				);
			}
		}

		if ( empty( $legal_links ) ) {
			$legal_links = $this->resolve_url_links( (array) ( $fallback['bottom_links'] ?? array() ) );
		}

		$tagline = ! empty( $cms_footer['brand_description'] )
			? (string) $cms_footer['brand_description']
			: (string) ( $fallback['brand']['tagline'] ?? SettingsService::tagline_fallback() );

		return array(
			'columns'      => $columns,
			'quick_links'  => ! empty( $columns ) ? ( $columns[0]['items'] ?? array() ) : $this->resolve_url_links( (array) ( $fallback['quick_links'] ?? array() ) ),
			'legal_links'  => $legal_links,
			'tagline'      => $tagline,
			'brand_bg'     => (string) ( $fallback['brand']['bg_image'] ?? '' ),
			'watermark'    => (string) ( $fallback['brand']['watermark'] ?? 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg' ),
			'standards'    => (array) ( $fallback['standards'] ?? array() ),
			'labels'       => array(
				'quick_links' => (string) ( $labels['quick_links_heading'] ?? 'QUICK LINKS' ),
				'contact'     => (string) ( $labels['contact_heading'] ?? 'CONTACT US' ),
				'rights'      => (string) ( $labels['rights_text'] ?? 'All Rights Reserved.' ),
			),
			'phone'        => SettingsService::phone(),
			'email'        => SettingsService::email(),
			'address'      => SettingsService::address(),
			'socials'      => SettingsService::socials(),
			'year'         => gmdate( 'Y' ),
		);
	}

	private function resolve_route_links( array $links ): array {
		$resolved = array();
		foreach ( $links as $link ) {
			$label = trim( (string) ( $link['label'] ?? '' ) );
			$route = (string) ( $link['route'] ?? '' );
			if ( '' === $label || '' === $route ) {
				continue;
			}
			$resolved[] = array(
				'label' => $label,
				'url'   => RouteService::url( $route ),
			);
		}
		return $resolved;
	}

	private function resolve_url_links( array $links ): array {
		$resolved = array();
		foreach ( $links as $link ) {
			$label = trim( (string) ( $link['label'] ?? '' ) );
			$url   = (string) ( $link['url'] ?? '' );
			if ( '' === $label || '' === $url ) {
				continue;
			}
			$resolved[] = array(
				'label' => $label,
				'url'   => UrlHelper::resolve( $url ),
			);
		}
		return $resolved;
	}
}
