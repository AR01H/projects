<?php
namespace AHEcommerce\Modules\Admin_Tools;

use AHEcommerce\Core\Service_Provider;
use AHEcommerce\Core\Container;

class Admin_Tools_Service_Provider implements Service_Provider {

	public function register( Container $container ) {
		$container->singleton( Admin_Tools_Module::class, function( $c ) {
			return new Admin_Tools_Module();
		});
	}

	public function boot( Container $container ) {
		$module = $container->get( Admin_Tools_Module::class );
		$module->boot();
	}
}
