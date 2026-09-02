<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class EventsController {

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/events.json' );

		$hero = (array) ( $data['hero'] ?? array() );
		if ( '' !== (string) ( $hero['image'] ?? '' ) && 0 !== strpos( (string) $hero['image'], 'http' ) ) {
			$hero['image'] = VINTAGESOUL_URI . '/' . ltrim( (string) $hero['image'], '/' );
		}

		return array(
			'hero'        => $hero,
			'event_types' => (array) ( $data['event_types'] ?? array() ),
			'inclusions'  => (array) ( $data['inclusions'] ?? array() ),
			'packages'    => (array) ( $data['packages'] ?? array() ),
			'process'     => (array) ( $data['process'] ?? array() ),
			'gallery'     => (array) ( $data['gallery'] ?? array() ),
			'reviews'     => (array) ( $data['reviews'] ?? array() ),
			'faqs'        => (array) ( $data['faqs'] ?? array() ),
		);
	}
}
