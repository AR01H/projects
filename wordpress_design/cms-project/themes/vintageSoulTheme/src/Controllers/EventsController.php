<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\Plugins\FaqBridgeService;
use VintageSoul\Services\Plugins\PageBridgeService;

defined( 'ABSPATH' ) || exit;

final class EventsController {

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/events.json' );

		$hero_raw = (array) ( $data['hero'] ?? array() );
		if ( '' !== (string) ( $hero_raw['image'] ?? '' ) && 0 !== strpos( (string) $hero_raw['image'], 'http' ) ) {
			$hero_raw['image'] = VINTAGESOUL_URI . '/' . ltrim( (string) $hero_raw['image'], '/' );
		}

		$hero = PageBridgeService::resolve_hero( 'events', $hero_raw );

		// FAQ list: CMS FAQ Builder (admin.php?page=ah-faqs, attached to slug
		// "events", Section per events.json's faqs.section) first, falls
		// back to events.json's faqs.items.
		$faqs_json = (array) ( $data['faqs'] ?? array() );
		$faqs_cms  = FaqBridgeService::get_items( 'events', (string) ( $faqs_json['section'] ?? '' ) );
		$faqs      = array(
			'tag'   => (string) ( $faqs_json['tag'] ?? '' ),
			'title' => (string) ( $faqs_json['title'] ?? '' ),
			'items' => ! empty( $faqs_cms ) ? $faqs_cms : (array) ( $faqs_json['items'] ?? array() ),
		);

		return array(
			'hero'        => $hero,
			'event_types' => (array) ( $data['event_types'] ?? array() ),
			'inclusions'  => (array) ( $data['inclusions'] ?? array() ),
			'packages'    => (array) ( $data['packages'] ?? array() ),
			'process'     => (array) ( $data['process'] ?? array() ),
			'gallery'     => (array) ( $data['gallery'] ?? array() ),
			'reviews'     => (array) ( $data['reviews'] ?? array() ),
			'faqs'        => $faqs,
		);
	}
}
