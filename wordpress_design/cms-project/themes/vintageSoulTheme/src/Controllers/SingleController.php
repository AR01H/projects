<?php
namespace VintageSoul\Controllers;

use VintageSoul\Support\PostHelper;

defined( 'ABSPATH' ) || exit;

final class SingleController {

	public function prepare(): array {
		$post_id = get_the_ID();

		return array(
			'post_id'       => $post_id,
			'title'         => get_the_title( $post_id ),
			'excerpt'       => PostHelper::excerpt( $post_id ),
			'reading_time'  => PostHelper::reading_time_minutes( $post_id ),
		);
	}
}
