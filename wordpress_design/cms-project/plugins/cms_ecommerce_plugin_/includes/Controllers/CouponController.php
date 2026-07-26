<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Auth\Middleware;
use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class CouponController {
    public function list_coupons( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_coupon', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        $coupons = array_map( function ( $p ) {
            $m = json_decode( $p->post_content, true ) ?: [];
            return [
                'code' => strtoupper( $p->post_name ),
                'description' => $p->post_title,
                'type' => $m['type'] ?? 'percentage',
                'discount' => $m['discount'] ?? 0,
                'maxDiscount' => $m['maxDiscount'] ?? null,
                'minOrder' => $m['minOrder'] ?? 0,
                'usageLimit' => $m['usageLimit'] ?? null,
                'usedCount' => $m['usedCount'] ?? 0,
                'isActive' => $p->post_status === 'publish',
            ];
        }, $posts );
        return Response::success( $coupons );
    }

    public function validate( \WP_REST_Request $request ): \WP_REST_Response {
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );

        $code     = strtoupper( sanitize_text_field( $request->get_param( 'code' ) ?? '' ) );
        $subtotal = (float)( $request->get_param( 'subtotal' ) ?? 0 );

        if ( ! $code ) return Response::error( 'Coupon code required.' );

        $coupon = get_posts( [ 'post_type' => 'rh_coupon', 'name' => strtolower( $code ), 'post_status' => 'publish', 'numberposts' => 1 ] );
        if ( empty( $coupon ) ) return Response::success( [ 'valid' => false, 'error' => 'Invalid coupon code' ] );

        $meta = json_decode( $coupon[0]->post_content, true ) ?: [];
        if ( ! empty( $meta['minOrder'] ) && $subtotal < $meta['minOrder'] ) {
            return Response::success( [ 'valid' => false, 'error' => 'Minimum order of ₹' . $meta['minOrder'] . ' required' ] );
        }
        if ( ! empty( $meta['usageLimit'] ) && ( $meta['usedCount'] ?? 0 ) >= $meta['usageLimit'] ) {
            return Response::success( [ 'valid' => false, 'error' => 'Coupon usage limit reached' ] );
        }

        $discount = 0;
        $type = $meta['type'] ?? 'percentage';
        if ( $type === 'percentage' || $type === 'category_percent' ) {
            $discount = min( $subtotal * ( $meta['discount'] ?? 0 ) / 100, $meta['maxDiscount'] ?? PHP_FLOAT_MAX );
        } elseif ( $type === 'fixed' ) {
            $discount = min( $meta['discount'] ?? 0, $subtotal );
        }

        return Response::success( [
            'valid' => true,
            'coupon' => [ 'code' => $code, 'type' => $type, 'discount' => $meta['discount'] ?? 0 ],
            'discountAmount' => round( $discount ),
            'freeShipping' => $type === 'free_shipping',
        ]);
    }

    public function apply( \WP_REST_Request $request ): \WP_REST_Response {
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );

        $code = strtoupper( sanitize_text_field( $request->get_param( 'code' ) ?? '' ) );
        if ( ! $code ) return Response::error( 'Coupon code required.' );

        $coupon = get_posts( [ 'post_type' => 'rh_coupon', 'name' => strtolower( $code ), 'post_status' => 'publish', 'numberposts' => 1 ] );
        if ( empty( $coupon ) ) return Response::error( 'Invalid coupon code.' );

        $meta = json_decode( $coupon[0]->post_content, true ) ?: [];
        $meta['usedCount'] = ( $meta['usedCount'] ?? 0 ) + 1;
        wp_update_post( [ 'ID' => $coupon[0]->ID, 'post_content' => wp_json_encode( $meta ) ] );

        return Response::success( [ 'applied' => true, 'code' => $code ] );
    }
}
