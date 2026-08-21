<?php
namespace VintageSoul\Repositories;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Repositories\Contracts\TestimonialRepositoryInterface;

defined( 'ABSPATH' ) || exit;

final class JsonTestimonialRepository implements TestimonialRepositoryInterface {

	public function all(): array {
		$data = JsonFileProvider::read( 'data/content/testimonials.json' );
		// The file is a {tag, title, items} document like every other content
		// JSON in this theme - not a bare list. Fall back to the top level so a
		// plain array still works.
		$rows = ( isset( $data['items'] ) && is_array( $data['items'] ) ) ? $data['items'] : $data;

		return array_values( array_filter( array_map( static function ( $row ) {
			$row  = (array) $row;
			$name = trim( (string) ( $row['name'] ?? '' ) );
			if ( '' === $name ) {
				return null;
			}
			return array(
				'name'   => $name,

				'role'   => (string) ( $row['role'] ?? $row['location'] ?? '' ),
				'quote'  => (string) ( $row['quote'] ?? $row['text'] ?? '' ),
				'rating' => max( 0, min( 5, (int) ( $row['rating'] ?? 5 ) ) ),
				'avatar' => (string) ( $row['avatar'] ?? '' ),
			);
		}, $rows ) ) );
	}
}
