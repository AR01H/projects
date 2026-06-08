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
	 * Load one component's heading data from section-headings.json.
	 * Substitutes brand tokens: {brand_name}, {product_name}, {Product_name},
	 * {product_short}, {Product_short} - values from brand.json.
	 * Static cache so both files are parsed only once per request.
	 */
	public static function section_heading( string $key ): array {
		static $all    = null;
		static $tokens = null;
		if ( $all === null ) {
			$all = CH_Real_Loader::json( 'section-headings' ) ?: [];
		}
		if ( $tokens === null ) {
			$b      = CH_Site_Data::brand();
			$tokens = [
				'{brand_name}'    => $b['brand_name']    ?? '',
				'{product_name}'  => $b['product_name']  ?? '',
				'{Product_name}'  => ucwords( $b['product_name']  ?? '' ),
				'{product_short}' => $b['product_short'] ?? '',
				'{Product_short}' => ucwords( $b['product_short'] ?? '' ),
			];
		}
		$heading = $all[ $key ] ?? [];
		if ( ! $heading ) {
			return [];
		}
		$keys   = array_keys( $tokens );
		$values = array_values( $tokens );
		return array_map( static function ( $v ) use ( $keys, $values ) {
			return is_string( $v ) ? str_replace( $keys, $values, $v ) : $v;
		}, $heading );
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
