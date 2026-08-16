<?php
namespace VintageSoul\Services;

use VintageSoul\Repositories\Contracts\TestimonialRepositoryInterface;
use VintageSoul\Repositories\JsonTestimonialRepository;

defined( 'ABSPATH' ) || exit;

final class TestimonialService {

	private TestimonialRepositoryInterface $repository;

	public function __construct( ?TestimonialRepositoryInterface $repository = null ) {

		$this->repository = $repository ?? new JsonTestimonialRepository();
	}

	public function featured( int $limit = 3 ): array {
		$featured = array_values( array_filter(
			$this->repository->all(),
			static fn( array $item ) => $item['rating'] >= 4
		) );
		return array_slice( $featured, 0, $limit );
	}
}
