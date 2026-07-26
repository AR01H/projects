<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class ContactController {
    public function submit( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $name = sanitize_text_field( $data['name'] ?? '' );
        $email = sanitize_email( $data['email'] ?? '' );
        $message = sanitize_textarea_field( $data['message'] ?? '' );
        $subject = sanitize_text_field( $data['subject'] ?? 'Contact Form Submission' );

        if ( ! $name || ! $email || ! $message ) {
            return Response::error( 'Name, email, and message are required.' );
        }
        if ( ! is_email( $email ) ) {
            return Response::error( 'Invalid email address.' );
        }

        // Store as a WP option for admin review
        $submissions = get_option( 'cms_contact_submissions', [] );
        $submissions[] = [
            'id' => wp_generate_uuid4(),
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'status' => 'unread',
            'created_at' => current_time( 'mysql' ),
        ];
        update_option( 'cms_contact_submissions', $submissions );

        // Send email notification to admin
        $admin_email = get_option( 'admin_email' );
        $site_name = get_bloginfo( 'name' );
        wp_mail( $admin_email, "[$site_name] New Contact: $subject", "Name: $name\nEmail: $email\n\n$message" );

        return Response::success( [ 'message' => 'Your message has been received. We will get back to you soon.' ], 201 );
    }
}
