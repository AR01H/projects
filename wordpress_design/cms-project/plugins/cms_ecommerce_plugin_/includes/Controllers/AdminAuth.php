<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Auth\JWT;
use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminAuth {
    public function login( \WP_REST_Request $request ): \WP_REST_Response {
        $username = sanitize_text_field( $request->get_param( 'username' ) ?? $request->get_param( 'email' ) ?? '' );
        $password = $request->get_param( 'password' ) ?? '';
        if ( ! $username || ! $password ) return Response::error( 'Username and password are required.' );

        $user = wp_authenticate( $username, $password );
        if ( is_wp_error( $user ) ) return Response::error( 'Invalid credentials.', 401 );
        if ( ! in_array( 'administrator', (array) $user->roles, true ) ) return Response::error( 'Access denied.', 403 );

        $token = JWT::encode( [ 'user_id' => $user->ID, 'email' => $user->user_email, 'role' => 'administrator' ] );
        update_user_meta( $user->ID, 'last_login', current_time( 'mysql' ) );

        return Response::success( [
            'user'  => [ 'id' => (string) $user->ID, 'name' => $user->display_name, 'email' => $user->user_email, 'role' => 'super_admin', 'avatar' => get_avatar_url( $user->ID ), 'lastLogin' => get_user_meta( $user->ID, 'last_login', true ) ],
            'token' => $token,
        ]);
    }

    public function get_profile( \WP_REST_Request $request ): \WP_REST_Response {
        $user = \CMS_ECOMMERCE\Auth\Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        return Response::success( [ 'id' => (string) $user->ID, 'name' => $user->display_name, 'email' => $user->user_email, 'role' => 'super_admin', 'avatar' => get_avatar_url( $user->ID ), 'lastLogin' => get_user_meta( $user->ID, 'last_login', true ) ] );
    }
}
