<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Auth\Middleware;
use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class CartController {
    public function get_cart( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );

        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_cart WHERE user_id = %d", $user->ID ) );
        $items = array_map( function ( $row ) use ( $wpdb ) {
            $product = get_post( $row->product_id );
            return [
                'id' => $row->id, 'productId' => $row->product_id,
                'quantity' => (int) $row->quantity, 'variantId' => $row->variant_id,
                'product' => $product ? [ 'id' => (string) $product->ID, 'name' => $product->post_title, 'price' => (float)( json_decode( $product->post_content, true )['price'] ?? 0 ), 'thumbnail' => get_the_post_thumbnail_url( $product->ID, 'thumbnail' ) ?: '' ] : null,
            ];
        }, $rows );
        return Response::success( $items );
    }

    public function add_item( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );

        $product_id = $request->get_param( 'product_id' ) ?? $request->get_param( 'productId' ) ?? '';
        $quantity   = (int)( $request->get_param( 'quantity' ) ?? 1 );
        $variant_id = $request->get_param( 'variant_id' ) ?? $request->get_param( 'variantId' ) ?? '';

        if ( ! $product_id ) return Response::error( 'Product ID required.' );

        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_cart WHERE user_id = %d AND product_id = %s AND variant_id = %s", $user->ID, $product_id, $variant_id ) );

        if ( $existing ) {
            $wpdb->update( "{$wpdb->prefix}rh_cart", [ 'quantity' => $existing->quantity + $quantity ], [ 'id' => $existing->id ] );
        } else {
            $wpdb->insert( "{$wpdb->prefix}rh_cart", [ 'id' => wp_generate_uuid4(), 'user_id' => $user->ID, 'product_id' => $product_id, 'quantity' => $quantity, 'variant_id' => $variant_id ] );
        }

        return $this->get_cart( $request );
    }

    public function update_item( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );

        $id = $request->get_param( 'id' );
        $quantity = (int)( $request->get_param( 'quantity' ) ?? 1 );

        if ( $quantity <= 0 ) {
            $wpdb->delete( "{$wpdb->prefix}rh_cart", [ 'id' => $id, 'user_id' => $user->ID ] );
        } else {
            $wpdb->update( "{$wpdb->prefix}rh_cart", [ 'quantity' => $quantity ], [ 'id' => $id, 'user_id' => $user->ID ] );
        }
        return $this->get_cart( $request );
    }

    public function remove_item( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $wpdb->delete( "{$wpdb->prefix}rh_cart", [ 'id' => $request->get_param( 'id' ), 'user_id' => $user->ID ] );
        return $this->get_cart( $request );
    }

    public function clear_cart( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $wpdb->delete( "{$wpdb->prefix}rh_cart", [ 'user_id' => $user->ID ] );
        return Response::success( [] );
    }
}
