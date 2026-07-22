<?php
namespace AHEcommerce\Modules\Checkout;

use AHEcommerce\Core\Service_Provider;
use AHEcommerce\Core\Container;

class Checkout_Service_Provider implements Service_Provider {

	public function register( Container $container ) {
		$container->singleton( Checkout_Module::class, function ( $c ) {
			return new Checkout_Module( $c );
		} );
	}

	public function boot( Container $container ) {
		$module = $container->get( Checkout_Module::class );
		$module->boot();
	}
}
