<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class SettingsController {
    public function get_all( \WP_REST_Request $request ): \WP_REST_Response {
        $data = get_option( 'cms_settings', [ 'brand' => [ 'name' => '', 'tagline' => '', 'shortName' => '' ], 'contact' => [ 'phone' => '', 'email' => '', 'address' => '' ], 'shipping' => [ 'freeShippingThreshold' => 0, 'defaultShippingCharge' => 0, 'codCharge' => 0, 'estimatedDeliveryMin' => 3, 'estimatedDeliveryMax' => 7 ], 'currency' => [ 'code' => 'INR', 'symbol' => '₹', 'locale' => 'en-IN' ], 'social' => [ 'instagram' => '', 'facebook' => '', 'pinterest' => '', 'youtube' => '', 'whatsapp' => '' ] ] );
        return Response::success( $data );
    }

    public function update( \WP_REST_Request $request ): \WP_REST_Response {
        $existing = get_option( 'cms_settings', [] );
        $data = $request->get_json_params();
        $merged = array_replace_recursive( $existing, $data );
        update_option( 'cms_settings', $merged );
        return Response::success( $merged );
    }
}
