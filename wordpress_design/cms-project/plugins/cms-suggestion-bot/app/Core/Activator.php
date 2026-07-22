<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Core;

use CmsSuggestionBot\Installer\Installer;
use CmsSuggestionBot\Cron\Scheduler;

defined( 'ABSPATH' ) || exit;

/**
 * register_activation_hook() target. Only structural setup happens here -
 * no content reading, so activation stays fast even on large sites.
 */
final class Activator {

	public static function activate(): void {
		Installer::install();

		// Plugin::boot() (which normally hooks Scheduler::registerIntervals via
		// 'cron_schedules') hasn't run yet on this request - register the same
		// static callback directly so wp_schedule_event() below recognizes
		// the 'weekly'/'monthly' interval names.
		add_filter( 'cron_schedules', array( Scheduler::class, 'registerIntervals' ) ); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
		Scheduler::scheduleAll();
	}
}
