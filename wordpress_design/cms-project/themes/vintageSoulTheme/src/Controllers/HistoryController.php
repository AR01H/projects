<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

final class HistoryController {

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/history.json' );

		// Recursively resolve all image paths with UrlHelper
		$resolved = $this->resolveImages( (array) $data );

		return array(
			'hero'                => (array) ( $resolved['hero'] ?? array() ),
			'accent'              => (array) ( $resolved['accent'] ?? array() ),
			'intro'               => (array) ( $resolved['intro'] ?? array() ),
			'why'                 => (array) ( $resolved['why'] ?? array() ),
			'timeline'            => (array) ( $resolved['timeline'] ?? array() ),
			'story'               => (array) ( $resolved['story'] ?? array() ),
			'history'             => (array) ( $resolved['history'] ?? array() ),
			'life_cycle'          => (array) ( $resolved['life_cycle'] ?? array() ),
			'varieties'           => (array) ( $resolved['varieties'] ?? array() ),
			'goodness'            => (array) ( $resolved['goodness'] ?? array() ),
			'nutritional_alchemy' => (array) ( $resolved['nutritional_alchemy'] ?? array() ),
			'benefits'            => (array) ( $resolved['benefits'] ?? array() ),
			'uses'                => (array) ( $resolved['uses'] ?? array() ),
			'culture'             => (array) ( $resolved['culture'] ?? array() ),
			'storage_guide'       => (array) ( $resolved['storage_guide'] ?? array() ),
			'why_everyone_loves'  => (array) ( $resolved['why_everyone_loves'] ?? array() ),
			'faq'                 => (array) ( $resolved['faq'] ?? array() ),
			'closing'             => (array) ( $resolved['closing'] ?? array() ),
		);
	}

	private function resolveImages( array $data ): array {
		foreach ( $data as $key => $val ) {
			if ( is_array( $val ) ) {
				$data[ $key ] = $this->resolveImages( $val );
			} elseif ( is_string( $val ) && ( 'image' === $key || 'src' === $key || 'mobile_src' === $key || preg_match( '/\.(jpe?g|png|webp|svg)$/i', $val ) ) ) {
				$data[ $key ] = UrlHelper::resolve( $val );
			}
		}
		return $data;
	}
}
