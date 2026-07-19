<?php
namespace AHEcommerce\Core;

/**
 * Manages the registration, activation, and loading of Ecommerce Modules.
 */
class Module_Manager {
	private $modules = array();

	/**
	 * Register a module instance.
	 */
	public function register_module( Abstract_Module $module ) {
		$this->modules[ $module->get_id() ] = $module;
	}

	/**
	 * Boot all active modules.
	 */
	public function boot_modules() {
		foreach ( $this->modules as $module ) {
			if ( $module->is_active() && $module->user_has_permission() ) {
				$module->boot();
			}
		}
	}

	/**
	 * Get all registered modules.
	 */
	public function get_modules() {
		return $this->modules;
	}
}
