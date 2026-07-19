<?php
namespace AHEcommerce\Core;

/**
 * Interface for Service Providers.
 */
interface Service_Provider {
	/**
	 * Register services into the container.
	 *
	 * @param Container $container
	 */
	public function register( Container $container );

	/**
	 * Boot the services (e.g., add WordPress hooks).
	 *
	 * @param Container $container
	 */
	public function boot( Container $container );
}
