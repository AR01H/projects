<?php
namespace AHEcommerce\Modules\Products;

use AHEcommerce\Core\Service_Provider;
use AHEcommerce\Core\Container;

class Product_Service_Provider implements Service_Provider {

	public function register( Container $container ) {
		$container->singleton( Product_Module::class, function( $c ) {
			return new Product_Module();
		});
	}

	public function boot( Container $container ) {
		$module = $container->get( Product_Module::class );
		$module->boot();
	}
}
