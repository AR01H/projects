<?php

namespace Adn\Theme\Feature\Guidance\Controller;

defined( 'ABSPATH' ) || exit;

class GuidanceController {

	public static function getContext(): array {
		return \Adn\Theme\Service\GuidanceContext::getContext();
	}
}
