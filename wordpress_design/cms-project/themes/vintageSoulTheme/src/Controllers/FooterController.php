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
		$footer = JsonFileProvider::read( 'data/content/footer.json' );
		$labels = (array) ( $footer['labels'] ?? array() );

		return array(
			'quick_links'  => NavigationService::menu( 'footer' ),
			'items'        => $this->resolve_route_links( (array) ( $footer['items'] ?? array() ) ),
			'bottom_links' => $this->resolve_url_links( (array) ( $footer['bottom_links'] ?? array() ) ),
			'tagline'      => (string) ( $footer['brand']['tagline'] ?? SettingsService::tagline_fallback() ),
			'brand_bg'     => (string) ( $footer['brand']['bg_image'] ?? '' ),
			'labels'       => array(
				'quick_links' => (string) ( $labels['quick_links_heading'] ?? '' ),
				'items'       => (string) ( $labels['items_heading'] ?? '' ),
				'contact'     => (string) ( $labels['contact_heading'] ?? '' ),
				'rights'      => (string) ( $labels['rights_text'] ?? '' ),
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
