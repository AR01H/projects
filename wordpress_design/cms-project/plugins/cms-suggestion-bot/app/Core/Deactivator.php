<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Core;

use CmsSuggestionBot\Cron\Scheduler;

defined( 'ABSPATH' ) || exit;

/**
 * register_deactivation_hook() target. Only unschedules cron - tables, cache,
 * logs, and settings are left in place (see uninstall.php for full removal,
 * which only runs when the admin has opted into "delete all data").
 */
final class Deactivator {

	public static function deactivate(): void {
		Scheduler::unscheduleAll();
	}
}
