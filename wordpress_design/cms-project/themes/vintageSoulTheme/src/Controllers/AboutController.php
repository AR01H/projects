<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\Plugins\PageBridgeService;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

final class AboutController {

	public function prepare(): array {
		$hero_raw = (array) JsonFileProvider::read( 'data/content/about-hero.json' );
		$intro    = (array) JsonFileProvider::read( 'data/content/about-intro.json' );
		$values   = (array) JsonFileProvider::read( 'data/content/values.json' );
		$story    = (array) JsonFileProvider::read( 'data/content/story.json' );

		$hero = PageBridgeService::resolve_hero( 'about', $this->resolve( $hero_raw ) );

		return array(
			'hero'   => $hero,
			'intro'  => $this->resolve( $intro ),
			'values' => $this->resolve( $values ),
			'story'  => $this->resolve( $story ),
		);
	}

	private function resolve( array $data ): array {
		foreach ( $data as $key => $val ) {
			if ( is_array( $val ) ) {
				$data[ $key ] = $this->resolve( $val );
			} elseif ( is_string( $val ) && ( 'image' === $key || 'src' === $key || preg_match( '/\.(jpe?g|png|webp|svg)$/i', $val ) ) ) {
				$data[ $key ] = UrlHelper::resolve( $val );
			}
		}
		return $data;
	}
}
