<?php
namespace CMS_ECOMMERCE\Auth;

if ( ! defined( 'ABSPATH' ) ) exit;

class Middleware {
    public static function get_user(): ?\WP_User {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if ( ! str_starts_with( $header, 'Bearer ' ) ) return null;
        $token   = substr( $header, 7 );
        $payload = JWT::decode( $token );
        if ( ! $payload || empty( $payload['user_id'] ) ) return null;
        $user = get_user_by( 'id', $payload['user_id'] );
        return $user ?: null;
    }
}
