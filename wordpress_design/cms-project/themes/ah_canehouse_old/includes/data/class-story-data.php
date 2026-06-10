<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Story_Data
 * Story section data: page settings, interactive cards, stat facts.
 * Reads from real_data/csv/ or real_data/json/ via CH_Real_Loader.
 */
class CH_Story_Data {

	public static function story_settings(): array {
		$kv = CH_Real_Loader::kv( 'story-settings' );
		if ( ! $kv ) {
			return [];
		}
		if ( isset( $kv['badge_text'] ) ) {
			$kv['badge_text'] = str_replace( '\n', "\n", $kv['badge_text'] );
		}
		$kv['facts'] = self::story_facts();
		return $kv;
	}

	public static function story_cards(): array {
		$rows = CH_Real_Loader::csv( 'story-cards' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			$split = static function ( $val ) {
				$val = (string) ( $val ?? '' );
				return $val === '' ? [] : array_values( array_filter( array_map( 'trim', explode( ';', $val ) ) ) );
			};
			return [
				'id'      => $r['id']      ?? '',
				'icon'    => $r['icon']    ?? '🌿',
				'label'   => $r['label']   ?? '',
				'heading' => $r['heading'] ?? '',
				'body'    => $r['body']    ?? '',
				'facts'   => $split( $r['facts']  ?? '' ),
				'images'  => $split( $r['images'] ?? '' ),
			];
		}, $rows );
	}

	public static function story_facts(): array {
		$rows = CH_Real_Loader::csv( 'story-facts' );
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

	public static function sugarcane_benefits(): array {
		$rows = CH_Real_Loader::csv( 'sugarcane-benefits' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'icon'  => $r['icon']  ?? '',
				'title' => $r['title'] ?? '',
				'text'  => $r['text']  ?? '',
				'stat'  => $r['stat']  ?? '',
			];
		}, $rows );
	}

	public static function story_cards_settings(): array {
		return CH_Shared_Data::section_heading( 'story_cards' );
	}

	public static function sugarcane_benefits_settings(): array {
		return CH_Shared_Data::section_heading( 'sugarcane_benefits' );
	}

	public static function quality_promise_settings(): array {
		return CH_Shared_Data::section_heading( 'quality_promise' );
	}

	public static function mission_vision_settings(): array {
		return CH_Shared_Data::section_heading( 'mission_vision' );
	}

	public static function sugarcane_experience_settings(): array {
		$heading = CH_Shared_Data::section_heading( 'sugarcane_experience' );
		$data    = CH_Real_Loader::json( 'sugarcane-experience' );
		return array_merge( $heading, [
			'steps'   => isset( $data['steps'] )   && is_array( $data['steps'] )   ? $data['steps']   : [],
			'sensory' => isset( $data['sensory'] ) && is_array( $data['sensory'] ) ? $data['sensory'] : [],
		] );
	}
}
