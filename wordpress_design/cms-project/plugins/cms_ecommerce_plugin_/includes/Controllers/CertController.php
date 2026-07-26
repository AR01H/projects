<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class CertController {
    public function get_all( \WP_REST_Request $request ): \WP_REST_Response {
        $data = get_option( 'cms_certifications', [] );
        return Response::success( $data );
    }
}
