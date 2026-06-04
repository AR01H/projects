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

	/**
	 * Origin section settings (tag, title, image, paras array).
	 * Source: real_data/json/about-origin.json
	 */
	public static function origin_settings(): array {
		return CH_Real_Loader::json( 'about-origin' );
	}

	/**
	 * Origin section timeline milestones (year, text rows).
	 * Source: real_data/csv/about-milestones.csv
	 */
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
}
