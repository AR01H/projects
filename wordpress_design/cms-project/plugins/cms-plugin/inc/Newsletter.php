<?php
/**
 * Backward-compatibility wrapper — delegates to Ah\Cms\Feature\Newsletter\Controller\NewsletterController.
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Feature/Newsletter/Controller/NewsletterController.php';

class_alias( \Ah\Cms\Feature\Newsletter\Controller\NewsletterController::class, 'AH_Newsletter' );
