<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Menu_Data
 * Drinks menu data: sizes, cane types, textures, flavours.
 * Reads from real_data/csv/ or real_data/json/ via CH_Real_Loader.
 */
class CH_Menu_Data {

	private static function map_drink_rows( array $rows ): array {
		return array_map( static function ( $r ) {
			return [
				'icon'     => $r['icon']     ?? '',
				'name'     => $r['name']     ?? '',
				'desc'     => $r['desc']     ?? '',
				'price'    => $r['price']    ?? '',
				'badge'    => $r['badge']    ?? '',
				'featured' => filter_var( $r['featured'] ?? false, FILTER_VALIDATE_BOOLEAN ),
			];
		}, $rows );
	}

	public static function menu_sizes(): array {
		$rows = CH_Real_Loader::csv( 'menu-sizes' );
		return $rows ? self::map_drink_rows( $rows ) : [];
	}

	public static function cane_types(): array {
		$rows = CH_Real_Loader::csv( 'cane-types' );
		return $rows ? self::map_drink_rows( $rows ) : [];
	}

	public static function textures(): array {
		$rows = CH_Real_Loader::csv( 'textures' );
		return $rows ? self::map_drink_rows( $rows ) : [];
	}

	public static function flavours(): array {
		$rows = CH_Real_Loader::csv( 'flavours' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'emoji'    => $r['emoji']    ?? '',
				'name'     => $r['name']     ?? '',
				'desc'     => $r['desc']     ?? '',
				'category' => $r['category'] ?? 'pure',
			];
		}, $rows );
	}
}
