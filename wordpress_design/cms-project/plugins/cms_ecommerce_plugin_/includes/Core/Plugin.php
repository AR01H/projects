<?php
namespace CMS_ECOMMERCE\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Plugin {
    private static ?self $instance = null;

    public static function instance(): self {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    public function init(): void {
        register_activation_hook( CMS_ECOMMERCE_PATH . 'cms_ecommerce_plugin_.php', [Activator::class, 'activate'] );
        add_action( 'rest_api_init', function () {
            CORS::register();
            Router::register();
        });
        add_action( 'init', [ $this, 'add_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
        AdminPage::register();
    }

    /**
     * Add wp-json rewrite rules so pretty permalinks work for REST API.
     * This persists even when WordPress regenerates .htaccess.
     */
    public function add_rewrite_rules(): void {
        add_rewrite_rule( 'wp-json/(.*)$', 'index.php?rest_route=/$matches[1]', 'top' );
        add_rewrite_rule( 'wp-json$', 'index.php?rest_route=/', 'top' );
    }

    public function add_query_vars( $vars ): array {
        $vars[] = 'rest_route';
        return $vars;
    }
}
