<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Cron;

use CmsSuggestionBot\Services\CacheService;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the "weekly"/"monthly" custom cron intervals (WordPress core
 * only ships hourly/twicedaily/daily) and wires the three Configuration ->
 * Cache schedule options to actual cache-rebuild runs.
 */
final class Scheduler {

	public function __construct( private readonly CacheService $cacheService ) {}

	public function hooks(): void {
		add_filter( 'cron_schedules', array( self::class, 'registerIntervals' ) ); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval

		add_action( CSB_CRON_HOOK_DAILY, array( $this, 'runDaily' ) );
		add_action( CSB_CRON_HOOK_WEEKLY, array( $this, 'runWeekly' ) );
		add_action( CSB_CRON_HOOK_MONTHLY, array( $this, 'runMonthly' ) );
	}

	/**
	 * Static and side-effect-free so Core\Activator can register the same
	 * intervals during plugin activation, before Plugin::boot() has run and
	 * hooked this class's instance methods for normal requests.
	 *
	 * @param array<string, array{interval:int,display:string}> $schedules
	 * @return array<string, array{interval:int,display:string}>
	 */
	public static function registerIntervals( array $schedules ): array {
		$schedules['weekly'] ??= array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once Weekly', 'cms-suggestion-bot' ),
		);
		$schedules['monthly'] ??= array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display'  => __( 'Once Monthly', 'cms-suggestion-bot' ),
		);

		return $schedules;
	}

	public static function scheduleAll(): void {
		if ( ! wp_next_scheduled( CSB_CRON_HOOK_DAILY ) ) {
			wp_schedule_event( time(), 'daily', CSB_CRON_HOOK_DAILY );
		}
		if ( ! wp_next_scheduled( CSB_CRON_HOOK_WEEKLY ) ) {
			wp_schedule_event( time(), 'weekly', CSB_CRON_HOOK_WEEKLY );
		}
		if ( ! wp_next_scheduled( CSB_CRON_HOOK_MONTHLY ) ) {
			wp_schedule_event( time(), 'monthly', CSB_CRON_HOOK_MONTHLY );
		}
	}

	public static function unscheduleAll(): void {
		wp_clear_scheduled_hook( CSB_CRON_HOOK_DAILY );
		wp_clear_scheduled_hook( CSB_CRON_HOOK_WEEKLY );
		wp_clear_scheduled_hook( CSB_CRON_HOOK_MONTHLY );
	}

	public function runDaily(): void {
		if ( $this->cacheService->settingEnabled( 'generate_daily' ) ) {
			$this->cacheService->rebuildAll();
		}
	}

	public function runWeekly(): void {
		if ( $this->cacheService->settingEnabled( 'generate_weekly' ) ) {
			$this->cacheService->rebuildAll();
		}
	}

	public function runMonthly(): void {
		if ( $this->cacheService->settingEnabled( 'generate_monthly' ) ) {
			$this->cacheService->rebuildAll();
		}
	}
}
