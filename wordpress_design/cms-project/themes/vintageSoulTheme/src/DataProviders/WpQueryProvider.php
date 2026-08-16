<?php
namespace VintageSoul\DataProviders;

defined( 'ABSPATH' ) || exit;

final class WpQueryProvider {

	public static function posts( array $args ): array {
		$query = new \WP_Query( array_merge( array( 'no_found_rows' => true ), $args ) );
		return $query->posts;
	}
}
