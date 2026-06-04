<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Home_Data
 * Home page data: hero settings, marquee, benefits, order steps, juice showcase.
 * Reads from real_data/csv/ or real_data/json/ via CH_Real_Loader.
 */
class CH_Home_Data {

	public static function home_settings(): array {
		return CH_Real_Loader::kv( 'home-settings' );
	}

	public static function marquee_items(): array {
		$rows = CH_Real_Loader::csv( 'marquee-items' );
		return $rows ? array_column( $rows, 'item' ) : [];
	}

	public static function benefits(): array {
		$rows = CH_Real_Loader::csv( 'benefits' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'icon'  => $r['icon']  ?? '',
				'title' => $r['title'] ?? '',
				'desc'  => $r['desc']  ?? '',
			];
		}, $rows );
	}

	public static function order_steps(): array {
		$rows = CH_Real_Loader::csv( 'order-steps' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'num'       => $r['num']       ?? '',
				'emoji'     => $r['emoji']     ?? '',
				'title'     => $r['title']     ?? '',
				'desc'      => $r['desc']      ?? '',
				'highlight' => filter_var( $r['highlight'] ?? false, FILTER_VALIDATE_BOOLEAN ),
			];
		}, $rows );
	}

	public static function juice_showcase(): array {
		$rows = CH_Real_Loader::csv( 'juice-showcase' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'image' => $r['image'] ?? '',
				'title' => $r['title'] ?? '',
				'desc'  => $r['desc']  ?? '',
			];
		}, $rows );
	}
}
