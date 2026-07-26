<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class UploadController {
    public function upload( \WP_REST_Request $request ): \WP_REST_Response {
        $files = $request->get_file_params();
        if ( empty( $files['file'] ) ) return Response::error( 'No file uploaded.' );

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload( 'file', 0 );
        if ( is_wp_error( $attachment_id ) ) return Response::error( $attachment_id->get_error_message() );

        $url = wp_get_attachment_url( $attachment_id );
        return Response::success( [ 'id' => (string) $attachment_id, 'url' => $url, 'name' => $files['file']['name'], 'type' => $files['file']['type'], 'size' => $files['file']['size'] ], 201 );
    }

    public function get_all( \WP_REST_Request $request ): \WP_REST_Response {
        $attachments = get_posts( [ 'post_type' => 'attachment', 'posts_per_page' => -1, 'post_status' => 'inherit' ] );
        $files = array_map( function ( $a ) {
            return [ 'id' => (string) $a->ID, 'url' => wp_get_attachment_url( $a->ID ), 'name' => $a->post_title, 'type' => $a->post_mime_type, 'size' => (int) get_post_meta( $a->ID, '_wp_attachment_metadata', true )['filesize'] ?? 0 ];
        }, $attachments );
        return Response::success( $files );
    }

    public function delete( \WP_REST_Request $request ): \WP_REST_Response {
        $deleted = wp_delete_attachment( $request->get_param( 'id' ), true );
        return Response::success( $deleted !== false );
    }
}
