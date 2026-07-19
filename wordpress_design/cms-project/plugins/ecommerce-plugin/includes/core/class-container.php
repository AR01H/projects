<?php
namespace AHEcommerce\Core;

/**
 * Lightweight Dependency Injection Container.
 */
class Container {
	private $services = array();
	private $providers = array();

	/**
	 * Bind a service to the container.
	 */
	public function bind( $id, $concrete = null ) {
		if ( is_null( $concrete ) ) {
			$concrete = $id;
		}
		$this->services[ $id ] = $concrete;
	}

	/**
	 * Bind a service as a singleton.
	 */
	public function singleton( $id, $concrete = null ) {
		if ( is_null( $concrete ) ) {
			$concrete = $id;
		}
		$this->bind( $id, function( $c ) use ( $concrete ) {
			static $instance;
			if ( is_null( $instance ) ) {
				$instance = is_callable( $concrete ) ? $concrete( $c ) : new $concrete();
			}
			return $instance;
		});
	}

	/**
	 * Resolve a service from the container.
	 */
	public function make( $id ) {
		if ( ! isset( $this->services[ $id ] ) ) {
			throw new \Exception( "Service {$id} not found." );
		}

		$concrete = $this->services[ $id ];

		if ( is_callable( $concrete ) ) {
			return $concrete( $this );
		}

		return new $concrete();
	}
	
	public function get( $id ) {
		return $this->make( $id );
	}

	/**
	 * Register a service provider.
	 */
	public function register( Service_Provider $provider ) {
		$provider->register( $this );
		$this->providers[] = $provider;
	}

	/**
	 * Boot all registered service providers.
	 */
	public function boot() {
		foreach ( $this->providers as $provider ) {
			$provider->boot( $this );
		}
	}
}
