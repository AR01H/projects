<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class GameController {

	private const EMBED_PATH = 'static/games/canerush/index.html';

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/game.json' );

		return array(
			'hero'  => (array) ( $data['hero'] ?? array() ),
			'embed' => VINTAGESOUL_URI . '/' . self::EMBED_PATH,
		);
	}
}
