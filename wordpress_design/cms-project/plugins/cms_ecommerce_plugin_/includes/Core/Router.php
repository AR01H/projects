<?php
namespace CMS_ECOMMERCE\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Router {
    public static function register(): void {
        $routes = require CMS_ECOMMERCE_PATH . 'includes/Core/Routes.php';

        foreach ( $routes as $signature => $handler ) {
            [ $method, $path, $auth_level ] = self::parse( $signature, $handler );

            register_rest_route( 'cms/v1', $path, [
                'methods'             => $method,
                'callback'            => [ new $handler[0], $handler[1] ],
                'permission_callback' => self::permission( $auth_level ),
            ]);
        }
    }

    private static function parse( string $signature, array $handler ): array {
        $parts = explode( ' ', $signature, 2 );
        $auth  = $handler[2] ?? 'public';
        return [ $parts[0], $parts[1], $auth ];
    }

    private static function permission( string $level ): callable {
        return match ( $level ) {
            'admin'  => function () {
                $user = \CMS_ECOMMERCE\Auth\Middleware::get_user();
                return $user && in_array( 'administrator', (array) $user->roles, true );
            },
            'auth'   => function () {
                return \CMS_ECOMMERCE\Auth\Middleware::get_user() !== null;
            },
            default  => '__return_true',
        };
    }
}
