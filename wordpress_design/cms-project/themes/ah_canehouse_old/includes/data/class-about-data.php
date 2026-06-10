<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_About_Data
 * About page data: page settings, team members, equipment gallery.
 * Reads from real_data/csv/ or real_data/json/ via CH_Real_Loader.
 */
class CH_About_Data {

	public static function page_header_info() {
		return CH_Real_Loader::json( 'about-page-header' );
	}

	public static function about_settings(): array {
		$kv = CH_Real_Loader::kv( 'about-settings' );
		if ( $kv && isset( $kv['promise_tags'] ) ) {
			$kv['promise_tags'] = array_map( 'trim', explode( ';', $kv['promise_tags'] ) );
		}
		return $kv;
	}

	public static function about_team(): array {
		$rows = CH_Real_Loader::csv( 'about-team' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'name'       => $r['name']       ?? '',
				'role'       => $r['role']       ?? '',
				'bio'        => $r['bio']        ?? '',
				'image_url'  => $r['image_url']  ?? '',
				'status'     => $r['status']     ?? 'active',
				'sort_order' => (int) ( $r['sort_order'] ?? 0 ),
			];
		}, $rows );
	}

	public static function about_equipment(): array {
		$rows = CH_Real_Loader::csv( 'about-equipment' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'src'   => $r['src']   ?? '',
				'label' => $r['label'] ?? '',
				'desc'  => $r['desc']  ?? '',
			];
		}, $rows );
	}

	public static function origin_settings(): array {
		return CH_Real_Loader::json( 'about-origin' );
	}

	public static function origin_milestones(): array {
		$rows = CH_Real_Loader::csv( 'about-milestones' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'year' => $r['year'] ?? '',
				'text' => $r['text'] ?? '',
			];
		}, $rows );
	}

	public static function franchise_why_items(): array {
		$rows = CH_Real_Loader::csv( 'franchise-why' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'icon'  => $r['icon']  ?? '',
				'title' => $r['title'] ?? '',
				'text'  => $r['text']  ?? '',
			];
		}, $rows );
	}

	public static function franchise_why_settings(): array {
		return CH_Shared_Data::section_heading( 'franchise_why' );
	}

	public static function franchise_steps_settings(): array {
		$data = CH_Real_Loader::json( 'franchise-steps' );
		return [
			'steps' => isset( $data['steps'] ) && is_array( $data['steps'] ) ? $data['steps'] : [],
		];
	}

	public static function franchise_enquiry_settings(): array {
		$heading = CH_Shared_Data::section_heading( 'franchise_enquiry' );
		$data    = CH_Real_Loader::json( 'franchise-enquiry' );
		return array_merge( $heading, [
			'features'          => isset( $data['features'] )          && is_array( $data['features'] )          ? $data['features']          : [],
			'steps'             => isset( $data['steps'] )             && is_array( $data['steps'] )             ? $data['steps']             : [],
			'unit_types'        => isset( $data['unit_types'] )        && is_array( $data['unit_types'] )        ? $data['unit_types']        : [],
			'investment_ranges' => isset( $data['investment_ranges'] ) && is_array( $data['investment_ranges'] ) ? $data['investment_ranges'] : [],
			'timelines'         => isset( $data['timelines'] )         && is_array( $data['timelines'] )         ? $data['timelines']         : [],
		] );
	}

	public static function origins_showcase_settings(): array {
		$data = CH_Real_Loader::json( 'origins-showcase' );
		if ( ! $data ) {
			return [];
		}
		return [
			'section_heading'  => $data['section_heading']  ?? '',
			'section_title'    => $data['section_title']    ?? '',
			'section_subtitle' => $data['section_subtitle'] ?? '',
			'uk'               => isset( $data['uk'] )      && is_array( $data['uk'] )      ? $data['uk']      : [],
			'origins'          => isset( $data['origins'] ) && is_array( $data['origins'] ) ? $data['origins'] : [],
		];
	}

	public static function history_pages(): array {
		$data = CH_Real_Loader::json( 'history-info' );
		if ( ! $data || ! is_array( $data ) ) {
			return [];
		}
		return $data;
	}
}
