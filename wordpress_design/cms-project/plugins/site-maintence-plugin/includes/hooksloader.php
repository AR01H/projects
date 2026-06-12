<?php
/**
 * Hooks loader - collects actions/filters and registers them in one pass.
 *
 * Inspired by the WP Plugin Boilerplate pattern.
 *
 * @package SiteModeManager
 */

declare( strict_types=1 );

namespace SiteModeManager;

// Block direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HooksLoader
 */
final class HooksLoader {

	/**
	 * Collected actions.
	 *
	 * @var array<int, array{hook: string, object: object, method: string, priority: int, args: int}>
	 */
	private array $actions = [];

	/**
	 * Collected filters.
	 *
	 * @var array<int, array{hook: string, object: object, method: string, priority: int, args: int}>
	 */
	private array $filters = [];

	// ─── Registration ────────────────────────────────────────────────────────

	/**
	 * Queue an action.
	 *
	 * @param string $hook     WordPress action hook name.
	 * @param object $object   Service object.
	 * @param string $method   Method name on the service.
	 * @param int    $priority Hook priority.
	 * @param int    $args     Expected argument count.
	 * @return void
	 */
	public function add_action(
		string $hook,
		object $object,
		string $method,
		int $priority = 10,
		int $args = 1
	): void {
		$this->actions[] = compact( 'hook', 'object', 'method', 'priority', 'args' );
	}

	/**
	 * Queue a filter.
	 *
	 * @param string $hook     WordPress filter hook name.
	 * @param object $object   Service object.
	 * @param string $method   Method name on the service.
	 * @param int    $priority Hook priority.
	 * @param int    $args     Expected argument count.
	 * @return void
	 */
	public function add_filter(
		string $hook,
		object $object,
		string $method,
		int $priority = 10,
		int $args = 1
	): void {
		$this->filters[] = compact( 'hook', 'object', 'method', 'priority', 'args' );
	}

	// ─── Execution ───────────────────────────────────────────────────────────

	/**
	 * Register all queued actions and filters with WordPress.
	 *
	 * Call once after all services have been configured.
	 *
	 * @return void
	 */
	public function run(): void {
		foreach ( $this->actions as $a ) {
			add_action( $a['hook'], [ $a['object'], $a['method'] ], $a['priority'], $a['args'] );
		}

		foreach ( $this->filters as $f ) {
			add_filter( $f['hook'], [ $f['object'], $f['method'] ], $f['priority'], $f['args'] );
		}
	}
}
