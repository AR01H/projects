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

	/**
	 * Load one component's heading data from the single section-headings.json.
	 * Static cache so the file is only parsed once per request.
	 */
	public static function section_heading( string $key ): array {
		static $all = null;
		if ( $all === null ) {
			$all = CH_Real_Loader::json( 'section-headings' ) ?: [];
		}
		return $all[ $key ] ?? [];
	}

	public static function review_carousel_settings(): array {
		return self::section_heading( 'review_carousel' );
	}

	public static function cta_section_settings(): array {
		return self::section_heading( 'cta_section' );
	}

	public static function certifications_section_settings(): array {
		return self::section_heading( 'certifications' );
	}

	public static function reviews_franchise_settings(): array {
		$heading = self::section_heading( 'reviews_franchise' );
		$data    = CH_Real_Loader::json( 'reviews-franchise' );
		return array_merge( $heading, [
			'cities' => isset( $data['cities'] ) && is_array( $data['cities'] ) ? $data['cities'] : [],
		] );
	}

	public static function reviews_events_settings(): array {
		$heading = self::section_heading( 'reviews_events' );
		$data    = CH_Real_Loader::json( 'reviews-events' );
		return array_merge( $heading, [
			'limit'        => (int) ( $data['limit'] ?? 6 ),
			'event_badges' => isset( $data['event_badges'] ) && is_array( $data['event_badges'] ) ? $data['event_badges'] : [],
		] );
	}
}
