<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\Plugins\FaqBridgeService;
use VintageSoul\Services\Plugins\PageBridgeService;

defined( 'ABSPATH' ) || exit;

final class FranchiseController {

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/franchise.json' );

		$hero_raw = (array) ( $data['hero'] ?? array() );
		if ( '' !== (string) ( $hero_raw['image'] ?? '' ) && 0 !== strpos( (string) $hero_raw['image'], 'http' ) ) {
			$hero_raw['image'] = VINTAGESOUL_URI . '/' . ltrim( (string) $hero_raw['image'], '/' );
		}

		$hero = PageBridgeService::resolve_hero( 'franchise', $hero_raw );

		// FAQ list: CMS FAQ Builder (admin.php?page=ah-faqs, attached to slug
		// "franchise", Section per franchise.json's faqs.section) first,
		// falls back to franchise.json's faqs.items.
		$faqs_json = (array) ( $data['faqs'] ?? array() );
		$faqs_cms  = FaqBridgeService::get_items( 'franchise', (string) ( $faqs_json['section'] ?? '' ) );
		$faqs      = array(
			'tag'   => (string) ( $faqs_json['tag'] ?? '' ),
			'title' => (string) ( $faqs_json['title'] ?? '' ),
			'items' => ! empty( $faqs_cms ) ? $faqs_cms : (array) ( $faqs_json['items'] ?? array() ),
		);

		return array(
			'hero'      => $hero,
			'why'       => (array) ( $data['why'] ?? array() ),
			'how'       => (array) ( $data['how'] ?? array() ),
			'pillars'   => (array) ( $data['pillars'] ?? array() ),
			'formats'   => (array) ( $data['formats'] ?? array() ),
			'gallery'   => (array) ( $data['gallery'] ?? array() ),
			'reviews'   => (array) ( $data['reviews'] ?? array() ),
			'faqs'      => $faqs,
			'closing'   => (array) ( $data['closing'] ?? array() ),
		);
	}
}
