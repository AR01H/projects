<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Blog_Data
 * Journal / blog data: post list.
 * Reads from real_data/csv/ or real_data/json/ via CH_Real_Loader.
 */
class CH_Blog_Data {

	public static function journal_posts(): array {
		$rows = CH_Real_Loader::csv( 'journal-posts' );
		if ( ! $rows ) {
			return [];
		}
		return array_map( static function ( $r ) {
			return [
				'title'   => $r['title']    ?? '',
				'excerpt' => $r['excerpt']  ?? '',
				'cat'     => $r['category'] ?? '',
				'content' => $r['content']  ?? '',
			];
		}, $rows );
	}
}
