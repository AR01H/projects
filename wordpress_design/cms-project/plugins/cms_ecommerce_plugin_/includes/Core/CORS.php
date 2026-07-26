<?php
namespace CMS_ECOMMERCE\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class CORS {
    public static function register(): void {
        remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
        add_filter( 'rest_pre_serve_request', function () {
            header( 'Access-Control-Allow-Origin: *' );
            header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );
            header( 'Access-Control-Allow-Headers: Content-Type, Authorization' );
            if ( $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ) {
                status_header( 200 );
                exit;
            }
        });
    }
}
