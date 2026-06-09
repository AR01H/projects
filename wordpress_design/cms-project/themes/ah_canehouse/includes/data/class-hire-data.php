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
				'sub'  => $r['sub']  ?? '',
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

	public static function events_why_settings(): array {
		$heading = CH_Shared_Data::section_heading( 'events_why' );
		$data    = CH_Real_Loader::json( 'events-why' );
		return array_merge( $heading, [
			'image' => $data['image'] ?? '',
		] );
	}

	public static function events_quote_settings(): array {
		$heading = CH_Shared_Data::section_heading( 'events_quote' );
		$data    = CH_Real_Loader::json( 'events-quote' );
		return array_merge( $heading, [
			'event_types' => isset( $data['event_types'] ) && is_array( $data['event_types'] ) ? $data['event_types'] : [],
		] );
	}

	public static function booking_wizard_settings(): array {
		$heading = CH_Shared_Data::section_heading( 'booking_wizard' );
		$data    = CH_Real_Loader::json( 'booking-wizard' );
		return array_merge( $heading, [
			'step_labels' => isset( $data['step_labels'] ) && is_array( $data['step_labels'] ) ? $data['step_labels'] : [],
		] );
	}
}
