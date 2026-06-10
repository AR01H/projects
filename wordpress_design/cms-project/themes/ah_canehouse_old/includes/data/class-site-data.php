<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Site_Data
 * Global site config: settings, contact, navigation, footer, news bar.
 * Reads from real_data/csv/ or real_data/json/ via CH_Real_Loader.
 */
class CH_Site_Data {

	public static function settings(): array {
		return CH_Real_Loader::kv( 'settings' );
	}

	public static function brand(): array {
		static $cache = null;
		if ( $cache === null ) {
			$cache = CH_Real_Loader::json( 'brand' ) ?: [];
		}
		return $cache;
	}

	public static function contact_settings(): array {
		$kv = CH_Real_Loader::kv( 'contact-settings' );
		return array_merge( [ 'recipient_email' => get_option( 'admin_email' ) ], $kv );
	}

	public static function navigation(): array {
		return CH_Real_Loader::csv( 'navigation' );
	}

	public static function news_bar(): array {
		return CH_Real_Loader::csv( 'news-bar' );
	}

	public static function footer(): array {
		$kv = CH_Real_Loader::kv( 'footer-settings' );

		$columns = [];
		foreach ( CH_Real_Loader::csv( 'footer-links' ) as $r ) {
			$title = trim( $r['column'] ?? '' );
			$label = trim( $r['label'] ?? '' );
			if ( $title === '' || $label === '' ) {
				continue;
			}
			if ( ! isset( $columns[ $title ] ) ) {
				$columns[ $title ] = [ 'title' => $title, 'items' => [] ];
			}
			$columns[ $title ]['items'][] = [
				'label'     => $label,
				'url'       => $r['url'] ?? '#',
				'highlight' => filter_var( $r['highlight'] ?? false, FILTER_VALIDATE_BOOLEAN ),
			];
		}

		$legal = [];
		foreach ( CH_Real_Loader::csv( 'footer-legal' ) as $r ) {
			$label = trim( $r['label'] ?? '' );
			if ( $label === '' ) {
				continue;
			}
			$legal[] = [ 'label' => $label, 'url' => $r['url'] ?? '#' ];
		}

		return [
			'brand_description' => $kv['brand_description'] ?? '',
			'badge_text'        => $kv['badge_text']        ?? '',
			'copyright_suffix'  => $kv['copyright_suffix']  ?? '',
			'columns'           => array_values( $columns ),
			'cta'               => [
				'label' => $kv['cta_label'] ?? '',
				'url'   => $kv['cta_url']   ?? '#contact',
			],
			'contact'           => [
				'phone_note' => $kv['phone_note'] ?? '',
				'email_note' => $kv['email_note'] ?? '',
			],
			'legal_links'       => $legal,
			'social'            => [],
		];
	}
}
