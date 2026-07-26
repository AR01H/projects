<?php
namespace CMS_ECOMMERCE\Helpers;

if ( ! defined( 'ABSPATH' ) ) exit;

class Response {
    public static function success( $data = null, int $code = 200 ): \WP_REST_Response {
        return new \WP_REST_Response( [ 'data' => $data ], $code );
    }
    public static function error( string $message, int $code = 400 ): \WP_REST_Response {
        return new \WP_REST_Response( [ 'error' => $message ], $code );
    }
}
