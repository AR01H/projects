<?php

namespace Adn\Theme\Feature\Contact\Controller;

defined( 'ABSPATH' ) || exit;

class ContactController {

	public static function getContext(): array {
		return \Adn\Theme\Service\ContactContext::getContext();
	}
}
