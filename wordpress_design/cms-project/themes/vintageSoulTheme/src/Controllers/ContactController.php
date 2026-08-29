<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

final class ContactController {

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/contact-page.json' );
		if ( empty( $data ) ) {
			$data = JsonFileProvider::read( 'data/content/contact-info.json' );
		}

		$hero = (array) ( $data['hero'] ?? array() );
		if ( ! empty( $hero['image'] ) ) {
			$hero['image'] = UrlHelper::resolve( (string) $hero['image'] );
		}

		return array(
			'hero'          => $hero,
			'contact_info'  => (array) ( $data['contact_info'] ?? array() ),
			'event_bar'     => (array) ( $data['event_bar'] ?? array() ),
			'enquiry_types' => (array) ( $data['enquiry_types'] ?? array() ),
			'faqs'          => (array) ( $data['faqs'] ?? array() ),
		);
	}
}
