<?php
/**
 * Plugin Name: AH Ecommerce Platform
 * Plugin URI:  https://example.com/ecommerce
 * Description: A robust, modular, and completely reusable ecommerce framework for WordPress.
 * Version:     1.1.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * Text Domain: ah-ecommerce
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'AH_ECOMMERCE_VERSION', '1.1.0' );
define( 'AH_ECOMMERCE_FILE', __FILE__ );
define( 'AH_ECOMMERCE_DIR', dirname( __FILE__ ) . '/' );
define( 'AH_ECOMMERCE_URL', plugin_dir_url( __FILE__ ) );

// ── Load ALL plugin files explicitly ────────────────────────────────
// This bypasses any autoloader path issues (network drives, symlinks, etc.)
$base = dirname( __FILE__ ) . '/includes/';

$ah_files = array(
	// Core.
	'core/interface-service-provider.php',
	'core/class-container.php',
	'core/abstract-module.php',
	'core/abstract-repository.php',
	'core/class-module-manager.php',

	// Database.
	'database/class-schema.php',

	// API & Theme.
	'api/abstract-rest-controller.php',
	'theme/class-template-loader.php',

	// Repositories (moved into modules).
	'modules/products/product-repository.php',
	'modules/orders/order-repository.php',
	'modules/categories/category-repository.php',
	'modules/customers/customer-repository.php',
	'modules/coupons/coupon-repository.php',
	'modules/sellers/seller-repository.php',

	// Commerce Services.
	'commerce/expiry/expiry-service.php',
	'commerce/sales/sale-service.php',
	'commerce/inventory/inventory-service.php',
	'commerce/wishlist/wishlist-service.php',
	'commerce/reviews/review-service.php',
	'commerce/coupons/coupon-engine.php',
	'commerce/shipping/shipping-service.php',
	'commerce/tax/tax-service.php',
	'commerce/notifications/email-service.php',
	'commerce/abandoned-cart/abandoned-cart-service.php',
	'commerce/recommendations/recommendation-service.php',
	'commerce/compare/compare-service.php',
	'commerce/stock-alerts/stock-alert-service.php',
	'commerce/qa/qa-service.php',

	// Modules — Admin Tools.
	'modules/admin-tools/class-admin-tools-module.php',
	'modules/admin-tools/class-admin-tools-service-provider.php',

	// Modules — Products.
	'modules/products/class-product-module.php',
	'modules/products/class-product-service-provider.php',

	// Modules — Cart.
	'modules/cart/class-cart-module.php',
	'modules/cart/class-cart-service-provider.php',

	// Modules — Checkout.
	'modules/checkout/class-checkout-module.php',
	'modules/checkout/class-checkout-service-provider.php',
);

foreach ( $ah_files as $file ) {
	$filepath = $base . $file;
	if ( file_exists( $filepath ) ) {
		require_once $filepath;
	}
}

// Also load the autoloader so any classes added later still resolve.
$autoloader = $base . 'class-autoloader.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
}

// ── WordPress hooks ─────────────────────────────────────────────────
add_action( 'plugins_loaded', array( 'AH_Ecommerce', 'init' ), 5 );
add_action( 'ah_ecommerce_cron', array( 'AH_Ecommerce', 'run_cron_tasks' ) );

// ── AJAX handlers for Compare, Stock Alerts, Q&A ────────────────────
add_action( 'wp_ajax_ah_compare_add', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	$result = \AHEcommerce\Commerce\Compare\Compare_Service::add( (int) $_POST['product_id'] );
	wp_send_json( $result );
} );
add_action( 'wp_ajax_ah_compare_remove', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	$result = \AHEcommerce\Commerce\Compare\Compare_Service::remove( (int) $_POST['product_id'] );
	wp_send_json( $result );
} );
add_action( 'wp_ajax_ah_compare_clear', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	\AHEcommerce\Commerce\Compare\Compare_Service::clear();
	wp_send_json_success( array( 'message' => 'Comparison cleared.' ) );
} );
add_action( 'wp_ajax_ah_stock_alert_subscribe', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	$result = \AHEcommerce\Commerce\Stock_Alerts\Stock_Alert_Service::subscribe( (int) $_POST['product_id'], sanitize_email( $_POST['email'] ) );
	wp_send_json( $result );
} );
add_action( 'wp_ajax_ah_question_submit', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	$id = \AHEcommerce\Commerce\QA\QA_Service::ask( (int) $_POST['product_id'], array(
		'name'    => sanitize_text_field( $_POST['name'] ),
		'email'   => sanitize_email( $_POST['email'] ),
		'question' => wp_kses_post( $_POST['question'] ),
	) );
	if ( $id ) {
		wp_send_json_success( array( 'message' => 'Question submitted. It will appear after admin approval.' ) );
	}
	wp_send_json_error( array( 'message' => 'Failed to submit question.' ) );
} );
add_action( 'wp_ajax_ah_question_answer', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied.' ) );
	}
	$ok = \AHEcommerce\Commerce\QA\QA_Service::answer( (int) $_POST['question_id'], array(
		'name'    => get_user_meta( get_current_user_id(), 'display_name', true ),
		'email'   => get_user_email( get_current_user_id() ),
		'answer'  => wp_kses_post( $_POST['answer'] ),
		'is_admin' => true,
	) );
	wp_send_json( $ok ? array( 'success' => true, 'message' => 'Answer posted.' ) : array( 'success' => false, 'message' => 'Failed.' ) );
} );
// Guest-compatible variants.
add_action( 'wp_ajax_nopriv_ah_compare_add', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	$result = \AHEcommerce\Commerce\Compare\Compare_Service::add( (int) $_POST['product_id'] );
	wp_send_json( $result );
} );
add_action( 'wp_ajax_nopriv_ah_compare_remove', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	$result = \AHEcommerce\Commerce\Compare\Compare_Service::remove( (int) $_POST['product_id'] );
	wp_send_json( $result );
} );
add_action( 'wp_ajax_nopriv_ah_stock_alert_subscribe', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	$result = \AHEcommerce\Commerce\Stock_Alerts\Stock_Alert_Service::subscribe( (int) $_POST['product_id'], sanitize_email( $_POST['email'] ) );
	wp_send_json( $result );
} );
add_action( 'wp_ajax_nopriv_ah_question_submit', function () {
	check_ajax_referer( 'ah_cart_nonce', 'nonce' );
	$id = \AHEcommerce\Commerce\QA\QA_Service::ask( (int) $_POST['product_id'], array(
		'name'    => sanitize_text_field( $_POST['name'] ),
		'email'   => sanitize_email( $_POST['email'] ),
		'question' => wp_kses_post( $_POST['question'] ),
	) );
	wp_send_json( $id ? array( 'success' => true, 'message' => 'Question submitted.' ) : array( 'success' => false, 'message' => 'Failed.' ) );
} );

add_filter( 'cron_schedules', function ( $schedules ) {
	$schedules['ah_ecommerce_5min'] = array(
		'interval' => 300,
		'display'  => 'Every 5 Minutes (AH Ecommerce)',
	);
	return $schedules;
} );

/**
 * Main Plugin Bootstrap.
 */
class AH_Ecommerce {

	private static $container = null;

	public static function init() {
		if ( self::$container instanceof \AHEcommerce\Core\Container ) {
			return;
		}

		self::$container = new \AHEcommerce\Core\Container();

		// Repositories.
		self::$container->singleton( \AHEcommerce\Modules\Products\Product_Repository::class );
		self::$container->singleton( \AHEcommerce\Modules\Orders\Order_Repository::class );
		self::$container->singleton( \AHEcommerce\Modules\Categories\Category_Repository::class );
		self::$container->singleton( \AHEcommerce\Modules\Customers\Customer_Repository::class );
		self::$container->singleton( \AHEcommerce\Modules\Coupons\Coupon_Repository::class );
		self::$container->singleton( \AHEcommerce\Modules\Sellers\Seller_Repository::class );

		// Service Providers.
		self::$container->register( new \AHEcommerce\Modules\Admin_Tools\Admin_Tools_Service_Provider() );
		self::$container->register( new \AHEcommerce\Modules\Products\Product_Service_Provider() );
		self::$container->register( new \AHEcommerce\Modules\Cart\Cart_Service_Provider() );
		self::$container->register( new \AHEcommerce\Modules\Checkout\Checkout_Service_Provider() );

		do_action( 'ah_ecommerce_register_services', self::$container );

		self::$container->boot();

		do_action( 'ah_ecommerce_ready', self::$container );
	}

	public static function container() {
		return self::$container;
	}

	public static function run_cron_tasks() {
		\AHEcommerce\Commerce\Sales\Sale_Service::activate_sales();
		\AHEcommerce\Commerce\Expiry\Expiry_Service::process_expired_products();
		\AHEcommerce\Commerce\Abandoned_Cart\Abandoned_Cart_Service::send_reminders( 24 );
	}

	public static function activate() {
		if ( ! wp_next_scheduled( 'ah_ecommerce_cron' ) ) {
			wp_schedule_event( time(), 'ah_ecommerce_5min', 'ah_ecommerce_cron' );
		}
		\AHEcommerce\Database\Schema::install();
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( 'ah_ecommerce_cron' );
	}
}

register_activation_hook( __FILE__, array( 'AH_Ecommerce', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AH_Ecommerce', 'deactivate' ) );
