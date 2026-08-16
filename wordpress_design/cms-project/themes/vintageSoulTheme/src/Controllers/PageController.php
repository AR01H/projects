<?php
namespace VintageSoul\Controllers;

defined( 'ABSPATH' ) || exit;

final class PageController {

	public function prepare(): array {
		return array(
			'title'   => get_the_title(),
			'post_id' => get_the_ID(),
		);
	}
}
