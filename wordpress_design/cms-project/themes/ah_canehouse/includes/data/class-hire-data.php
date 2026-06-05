<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Hire_Data
 * Hire / catering page data: packages, features, franchise locations.
 * Reads from real_data/csv/ or real_data/json/ via CH_Real_Loader.
 */
class CH_Hire_Data {

	public static function hire_packages(): array {
		$rows = CH_Real_Loader::csv( 'hire-packages' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			$raw = $r['items'] ?? '';
			return [
				'icon'  => $r['icon']  ?? '',
				'title' => $r['title'] ?? '',
				'desc'  => $r['desc']  ?? '',
				'items' => $raw !== '' ? array_map( 'trim', explode( ';', $raw ) ) : [],
			];
		}, $rows );
	}

	public static function hire_features(): array {
		$rows = CH_Real_Loader::csv( 'hire-features' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'icon' => $r['icon'] ?? '',
				'text' => $r['text'] ?? '',
			];
		}, $rows );
	}

	public static function franchise_locations(): array {
		$rows = CH_Real_Loader::csv( 'franchise-locations' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'icon' => $r['icon'] ?? '📍',
				'name' => $r['name'] ?? '',
			];
		}, $rows );
	}
	public static function events_why(): array {
		$rows = CH_Real_Loader::csv( 'events-why' );
		if ( $rows ) {
			return [ 'items' => $rows ];
		}
		return [];
	}
}
