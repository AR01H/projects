<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Auth\JWT;
use CMS_ECOMMERCE\Auth\Middleware;
use CMS_ECOMMERCE\Helpers\Response;
use CMS_ECOMMERCE\Helpers\Validator;

if ( ! defined( 'ABSPATH' ) ) exit;

class AuthController {
    public function login( \WP_REST_Request $request ): \WP_REST_Response {
        $email    = Validator::sanitize_string( $request->get_param( 'email' ) ?? '' );
        $password = $request->get_param( 'password' ) ?? '';
        if ( ! $email || ! $password ) return Response::error( 'Email and password are required.' );

        $user = wp_authenticate( $email, $password );
        if ( is_wp_error( $user ) ) return Response::error( 'Invalid email or password.', 401 );

        $token = JWT::encode( [ 'user_id' => $user->ID, 'email' => $user->user_email, 'role' => $user->roles[0] ?? 'subscriber' ] );
        return Response::success( [ 'user' => $this->format_user( $user ), 'token' => $token ] );
    }

    public function register( \WP_REST_Request $request ): \WP_REST_Response {
        $email    = Validator::sanitize_string( $request->get_param( 'email' ) ?? '' );
        $password = $request->get_param( 'password' ) ?? '';
        $name     = Validator::sanitize_string( $request->get_param( 'name' ) ?? '' );
        $phone    = Validator::sanitize_string( $request->get_param( 'phone' ) ?? '' );

        $err = Validator::required( compact( 'email', 'password', 'name' ), [ 'email', 'password', 'name' ] );
        if ( $err ) return Response::error( $err->get_error_message() );
        if ( ! Validator::email( $email ) ) return Response::error( 'Invalid email address.' );
        if ( email_exists( $email ) ) return Response::error( 'Email already registered.', 409 );

        $user_id = wp_create_user( $email, $password, $email );
        if ( is_wp_error( $user_id ) ) return Response::error( $user_id->get_error_message() );

        wp_update_user( [ 'ID' => $user_id, 'display_name' => $name, 'first_name' => $name ] );
        if ( $phone ) update_user_meta( $user_id, 'phone', $phone );

        $user = get_user_by( 'id', $user_id );
        $user->add_role( 'subscriber' );

        $token = JWT::encode( [ 'user_id' => $user_id, 'email' => $email, 'role' => 'subscriber' ] );
        return Response::success( [ 'user' => $this->format_user( $user ), 'token' => $token ], 201 );
    }

    public function get_profile( \WP_REST_Request $request ): \WP_REST_Response {
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        return Response::success( $this->format_user( $user ) );
    }

    public function update_profile( \WP_REST_Request $request ): \WP_REST_Response {
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $name  = Validator::sanitize_string( $request->get_param( 'name' ) ?? '' );
        $phone = Validator::sanitize_string( $request->get_param( 'phone' ) ?? '' );
        if ( $name ) wp_update_user( [ 'ID' => $user->ID, 'display_name' => $name ] );
        if ( $phone ) update_user_meta( $user->ID, 'phone', $phone );
        $user = get_user_by( 'id', $user->ID );
        return Response::success( $this->format_user( $user ) );
    }

    public function logout( \WP_REST_Request $request ): \WP_REST_Response {
        return Response::success( [ 'message' => 'Logged out successfully' ] );
    }

    private function format_user( \WP_User $user ): array {
        return [
            'id'        => (string) $user->ID,
            'name'      => $user->display_name,
            'email'     => $user->user_email,
            'phone'     => get_user_meta( $user->ID, 'phone', true ) ?: null,
            'createdAt' => $user->user_registered,
        ];
    }
}
