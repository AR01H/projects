<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class FooterController {
    public function get_all( \WP_REST_Request $request ): \WP_REST_Response {
        $data = get_option( 'cms_footer', [ 'quickLinks' => [], 'policyLinks' => [], 'socialLinks' => [], 'paymentMethods' => [], 'trustBadges' => [], 'certifications' => [], 'workingHours' => [] ] );
        return Response::success( $data );
    }

    public function get_all_admin( \WP_REST_Request $request ): \WP_REST_Response {
        return $this->get_all( $request );
    }

    public function update( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        update_option( 'cms_footer', $data );
        return Response::success( $data );
    }
}
