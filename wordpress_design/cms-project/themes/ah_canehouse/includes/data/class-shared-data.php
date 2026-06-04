<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Shared_Data
 * Data shared across multiple pages: reviews, services, certifications.
 * Reads from real_data/csv/ or real_data/json/ via CH_Real_Loader.
 */
class CH_Shared_Data {

	public static function reviews(): array {
		return CH_Real_Loader::csv( 'reviews' );
	}

	public static function services(): array {
		$rows = CH_Real_Loader::csv( 'services' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'icon'        => $r['icon']        ?? '',
				'title'       => $r['title']       ?? '',
				'description' => $r['description'] ?? '',
				'details'     => $r['details']     ?? '',
				'image_url'   => $r['image_url']   ?? '',
				'status'      => $r['status']      ?? 'active',
				'sort_order'  => (int) ( $r['sort_order'] ?? 0 ),
			];
		}, $rows );
	}

	public static function certifications(): array {
		$rows = CH_Real_Loader::csv( 'certifications' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'icon'  => $r['icon']  ?? '✅',
				'title' => $r['title'] ?? '',
				'desc'  => $r['desc']  ?? '',
				'badge' => $r['badge'] ?? '',
			];
		}, $rows );
	}
}
