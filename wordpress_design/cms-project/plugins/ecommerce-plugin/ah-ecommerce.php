<?php
/**
 * Plugin Name: AH Ecommerce Platform
 * Plugin URI:  https://example.com/ecommerce
 * Description: A robust, modular, and completely reusable ecommerce framework for WordPress.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * Text Domain: ah-ecommerce
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

// Define Constants
define( 'AH_ECOMMERCE_VERSION', '1.0.0' );
define( 'AH_ECOMMERCE_FILE', __FILE__ );
define( 'AH_ECOMMERCE_DIR', plugin_dir_path( __FILE__ ) );
define( 'AH_ECOMMERCE_URL', plugin_dir_url( __FILE__ ) );

// Load Autoloader
require_once AH_ECOMMERCE_DIR . 'includes/class-autoloader.php';

// Initialize the plugin container
add_action( 'plugins_loaded', array( 'AH_Ecommerce', 'init' ) );

/**
 * Main Plugin Bootstrap Class
 */
class AH_Ecommerce {

	/**
	 * The dependency injection container instance.
	 */
	private static $container = null;

	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		// Initialize the DI Container
		self::$container = new \AHEcommerce\Core\Container();

		// Register service providers
		self::$container->register( new \AHEcommerce\Modules\Admin_Tools\Admin_Tools_Service_Provider() );
		self::$container->register( new \AHEcommerce\Modules\Products\Product_Service_Provider() );
		self::$container->register( new \AHEcommerce\Modules\Checkout\Checkout_Module() );
		self::$container->register( new \AHEcommerce\Modules\Cart\Cart_Module() );

		// Boot all services
		self::$container->boot();
	}

	/**
	 * Get the application container.
	 * 
	 * @return \AHEcommerce\Core\Container
	 */
	public static function container() {
		return self::$container;
	}
}
