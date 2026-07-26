<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class PaymentController {
    public function verify( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        if ( empty( $data['paymentId'] ) || empty( $data['orderId'] ) ) return Response::error( 'paymentId and orderId required.' );
        return Response::success( [ 'verified' => true ] );
    }

    public function create_intent( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $amount = (float)( $data['amount'] ?? 0 );
        $currency = $data['currency'] ?? 'INR';
        $orderId = $data['orderId'] ?? '';

        if ( $amount <= 0 ) return Response::error( 'Invalid amount.' );

        // Generate a mock payment intent (replace with real Stripe integration)
        $intentId = 'pi_' . wp_generate_password( 24, false );
        $clientSecret = $intentId . '_secret_' . wp_generate_password( 16, false );

        return Response::success( [
            'id' => $intentId,
            'clientSecret' => $clientSecret,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'requires_payment_method',
        ]);
    }
}
