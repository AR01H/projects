<?php
namespace AHEcommerce\Core;

/**
 * The base class for all modules (Products, Pricing, Shipping, etc.)
 */
abstract class Abstract_Module {

	/**
	 * Module ID (e.g., 'product', 'shipping').
	 */
	abstract public function get_id();

	/**
	 * Module Name (e.g., 'Product Management').
	 */
	abstract public function get_name();

	/**
	 * Check if the module is enabled in settings.
	 */
	public function is_active() {
		// Default to active for scaffold, later check DB settings via get_option()
		return true;
	}

	/**
	 * Check if the current user has permission to interact with this module.
	 */
	public function user_has_permission() {
		// Default to true. Later check capabilities based on roles.
		return true;
	}

	/**
	 * Boot the module (hooks, routes, services).
	 */
	abstract public function boot();

	/**
	 * Install/Activate the module (e.g., create tables).
	 */
	public function activate() {
		// Override in child
	}

	/**
	 * Deactivate the module (e.g., clear caches).
	 */
	public function deactivate() {
		// Override in child
	}
}
