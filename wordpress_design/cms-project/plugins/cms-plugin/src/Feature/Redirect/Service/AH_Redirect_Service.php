<?php
defined( 'ABSPATH' ) || exit;

/**
 * Redirect Service — checks and enforces redirect rules on frontend.
 * Replaces inline template_redirect handler in ah-cms.php.
 */
class AH_Redirect_Service {

	public static function checkRedirects(): void {
		\Ah\Cms\Feature\Redirect\Service\RedirectService::checkRedirects();
	}
}
