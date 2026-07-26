<?php
namespace CMS_ECOMMERCE\Auth;

if ( ! defined( 'ABSPATH' ) ) exit;

class JWT {
    private static string $secret = '';

    private static function get_secret(): string {
        if ( self::$secret === '' ) {
            self::$secret = defined( 'AUTH_KEY' ) && AUTH_KEY ? AUTH_KEY : 'cms-ecommerce-fallback-secret';
        }
        return self::$secret;
    }

    public static function encode( array $payload ): string {
        $header = self::base64url( json_encode( [ 'typ' => 'JWT', 'alg' => 'HS256' ] ) );
        $payload['iat'] = time();
        $payload['exp'] = time() + ( 7 * DAY_IN_SECONDS );
        $body   = self::base64url( json_encode( $payload ) );
        $sig    = self::base64url( hash_hmac( 'sha256', "$header.$body", self::get_secret(), true ) );
        return "$header.$body.$sig";
    }

    public static function decode( string $token ): ?array {
        $parts = explode( '.', $token );
        if ( count( $parts ) !== 3 ) return null;
        [ $header, $body, $sig ] = $parts;
        $expected = self::base64url( hash_hmac( 'sha256', "$header.$body", self::get_secret(), true ) );
        if ( ! hash_equals( $expected, $sig ) ) return null;
        $payload = json_decode( base64_decode( strtr( $body, '-_', '+/' ) ), true );
        if ( ! $payload || ( $payload['exp'] ?? 0 ) < time() ) return null;
        return $payload;
    }

    private static function base64url( string $data ): string {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    }
}
