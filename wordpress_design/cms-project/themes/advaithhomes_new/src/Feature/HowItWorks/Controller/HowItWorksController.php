<?php

namespace Adn\Theme\Feature\HowItWorks\Controller;

defined( 'ABSPATH' ) || exit;

class HowItWorksController {

	public static function getContext(): array {
		return \Adn\Theme\Service\HowItWorksContext::getContext();
	}
}
