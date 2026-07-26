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
        AdminPage::register();
    }
}
