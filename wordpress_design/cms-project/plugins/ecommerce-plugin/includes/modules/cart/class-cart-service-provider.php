<?php
namespace AHEcommerce\Modules\Cart;

use AHEcommerce\Core\Service_Provider;
use AHEcommerce\Core\Container;

class Cart_Service_Provider implements Service_Provider {

	public function register( Container $container ) {
		$container->singleton( Cart_Module::class, function( $c ) {
			return new Cart_Module();
		});
	}

	public function boot( Container $container ) {
		$module = $container->get( Cart_Module::class );
		if ( $module->is_active() ) {
			$module->boot();
		}
	}
}
