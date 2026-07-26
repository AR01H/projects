<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class NotifyController {
    public function subscribe( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $email = sanitize_email( $data['email'] ?? '' );
        $product_id = sanitize_text_field( $data['productId'] ?? '' );

        if ( ! $email || ! is_email( $email ) ) {
            return Response::error( 'Valid email address required.' );
        }

        // Store notification requests
        $notifications = get_option( 'cms_notify_subscribers', [] );
        $notifications[] = [
            'email' => $email,
            'product_id' => $product_id,
            'created_at' => current_time( 'mysql' ),
            'notified' => false,
        ];
        update_option( 'cms_notify_subscribers', $notifications );

        return Response::success( [ 'message' => 'We will notify you when this product is back in stock.' ], 201 );
    }
}
