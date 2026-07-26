<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class NewsletterController {
    public function subscribe( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $email = sanitize_email( $data['email'] ?? '' );

        if ( ! $email || ! is_email( $email ) ) {
            return Response::error( 'Valid email address required.' );
        }

        // Store subscribers
        $subscribers = get_option( 'cms_newsletter_subscribers', [] );

        // Check if already subscribed
        if ( in_array( $email, $subscribers, true ) ) {
            return Response::success( [ 'message' => 'You are already subscribed.' ] );
        }

        $subscribers[] = $email;
        update_option( 'cms_newsletter_subscribers', $subscribers );

        return Response::success( [ 'message' => 'Thank you for subscribing!' ], 201 );
    }
}
