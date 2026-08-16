<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class HistoryController {

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/history.json' );

		return array(
			'hero'                => (array) ( $data['hero'] ?? array() ),
			'accent'              => (array) ( $data['accent'] ?? array() ),
			'intro'               => (array) ( $data['intro'] ?? array() ),
			'why'                 => (array) ( $data['why'] ?? array() ),
			'story'               => (array) ( $data['story'] ?? array() ),
			'history'             => (array) ( $data['history'] ?? array() ),
			'life_cycle'          => (array) ( $data['life_cycle'] ?? array() ),
			'goodness'            => (array) ( $data['goodness'] ?? array() ),
			'benefits'            => (array) ( $data['benefits'] ?? array() ),
			'uses'                => (array) ( $data['uses'] ?? array() ),
			'culture'             => (array) ( $data['culture'] ?? array() ),
			'why_everyone_loves'  => (array) ( $data['why_everyone_loves'] ?? array() ),
			'faq'                 => (array) ( $data['faq'] ?? array() ),
			'closing'             => (array) ( $data['closing'] ?? array() ),
		);
	}
}
