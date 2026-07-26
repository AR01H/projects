<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Auth\Middleware;
use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class OrderController {
    public function create_order( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );

        $data = $request->get_json_params();
        $order_id = 'ORD-' . strtoupper( wp_generate_password( 8, false ) );

        $wpdb->insert( "{$wpdb->prefix}rh_orders", [
            'id' => $order_id, 'user_id' => $user->ID, 'status' => 'placed',
            'subtotal' => $data['subtotal'] ?? 0, 'shipping' => $data['shipping'] ?? 0,
            'discount' => $data['discount'] ?? 0, 'cod_charge' => $data['codCharge'] ?? 0,
            'total' => $data['total'] ?? 0, 'address' => wp_json_encode( $data['address'] ?? [] ),
            'payment_method' => $data['paymentMethod'] ?? 'cod',
            'coupon_code' => $data['couponCode'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Save order items
        $items = $data['items'] ?? [];
        foreach ( $items as $item ) {
            $product = $item['product'] ?? $item;
            $wpdb->insert( "{$wpdb->prefix}rh_order_items", [
                'id' => wp_generate_uuid4(),
                'order_id' => $order_id,
                'product_id' => $product['id'] ?? $item['productId'] ?? '',
                'quantity' => $item['quantity'] ?? 1,
                'variant_id' => $item['variantId'] ?? null,
            ] );
        }

        // Clear cart
        $wpdb->delete( "{$wpdb->prefix}rh_cart", [ 'user_id' => $user->ID ] );

        // Return full order
        $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_orders WHERE id = %s", $order_id ) );
        $order->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_order_items WHERE order_id = %s", $order_id ) );

        return Response::success( $order, 201 );
    }

    public function get_orders( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_orders WHERE user_id = %d ORDER BY created_at DESC", $user->ID ) );
        foreach ( $orders as &$order ) {
            $order->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_order_items WHERE order_id = %s", $order->id ) );
        }
        return Response::success( $orders );
    }

    public function get_order( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_orders WHERE id = %s AND user_id = %d", $request->get_param( 'id' ), $user->ID ) );
        if ( ! $order ) return Response::error( 'Order not found.', 404 );
        $order->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_order_items WHERE order_id = %s", $order->id ) );
        return Response::success( $order );
    }

    public function cancel_order( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $wpdb->update( "{$wpdb->prefix}rh_orders", [ 'status' => 'cancelled', 'updated_at' => current_time( 'mysql' ) ], [ 'id' => $request->get_param( 'id' ), 'user_id' => $user->ID ] );
        $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_orders WHERE id = %s", $request->get_param( 'id' ) ) );
        $order->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_order_items WHERE order_id = %s", $order->id ) );
        return Response::success( $order );
    }
}
