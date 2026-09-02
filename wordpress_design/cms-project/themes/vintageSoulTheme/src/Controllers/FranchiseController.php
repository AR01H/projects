<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class FranchiseController {

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/franchise.json' );

		$hero = (array) ( $data['hero'] ?? array() );
		if ( '' !== (string) ( $hero['image'] ?? '' ) && 0 !== strpos( (string) $hero['image'], 'http' ) ) {
			$hero['image'] = VINTAGESOUL_URI . '/' . ltrim( (string) $hero['image'], '/' );
		}

		return array(
			'hero'      => $hero,
			'why'       => (array) ( $data['why'] ?? array() ),
			'how'       => (array) ( $data['how'] ?? array() ),
			'pillars'   => (array) ( $data['pillars'] ?? array() ),
			'formats'   => (array) ( $data['formats'] ?? array() ),
			'gallery'   => (array) ( $data['gallery'] ?? array() ),
			'reviews'   => (array) ( $data['reviews'] ?? array() ),
			'faqs'      => (array) ( $data['faqs'] ?? array() ),
			'closing'   => (array) ( $data['closing'] ?? array() ),
		);
	}
}
