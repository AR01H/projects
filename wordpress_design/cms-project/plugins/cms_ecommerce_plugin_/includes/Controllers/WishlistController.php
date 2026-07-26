<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Auth\Middleware;
use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class WishlistController {
    public function get_wishlist( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_wishlist WHERE user_id = %d", $user->ID ) );
        return Response::success( $this->format_items( $rows ) );
    }

    public function add_item( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $product_id = $request->get_param( 'product_id' ) ?? $request->get_param( 'productId' ) ?? '';
        $notes = $request->get_param( 'notes' ) ?? '';
        if ( ! $product_id ) return Response::error( 'Product ID required.' );
        $exists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_wishlist WHERE user_id = %d AND product_id = %s", $user->ID, $product_id ) );
        if ( $exists ) {
            if ( $notes ) $wpdb->update( "{$wpdb->prefix}rh_wishlist", [ 'notes' => $notes ], [ 'id' => $exists->id ] );
        } else {
            $wpdb->insert( "{$wpdb->prefix}rh_wishlist", [ 'id' => wp_generate_uuid4(), 'user_id' => $user->ID, 'product_id' => $product_id, 'notes' => $notes ] );
        }
        return $this->get_wishlist( $request );
    }

    public function update_item( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $id = $request->get_param( 'id' );
        $notes = $request->get_param( 'notes' ) ?? '';
        $wpdb->update( "{$wpdb->prefix}rh_wishlist", [ 'notes' => $notes ], [ 'id' => $id, 'user_id' => $user->ID ] );
        return $this->get_wishlist( $request );
    }

    public function remove_item( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $wpdb->delete( "{$wpdb->prefix}rh_wishlist", [ 'user_id' => $user->ID, 'product_id' => $request->get_param( 'id' ) ] );
        return $this->get_wishlist( $request );
    }

    public function clear_wishlist( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );
        $wpdb->delete( "{$wpdb->prefix}rh_wishlist", [ 'user_id' => $user->ID ] );
        return Response::success( [] );
    }

    public function get_shared( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $ids = $request->get_json_params()['ids'] ?? [];
        if ( empty( $ids ) ) return Response::success( [] );
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%s' ) );
        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_wishlist WHERE product_id IN ($placeholders)", ...$ids ) );
        return Response::success( $this->format_items( $rows ) );
    }

    private function format_items( array $rows ): array {
        return array_map( function ( $row ) {
            $product = get_post( $row->product_id );
            return [
                'id' => $row->id, 'productId' => $row->product_id,
                'notes' => $row->notes ?? '',
                'product' => $product ? [ 'id' => (string) $product->ID, 'name' => $product->post_title, 'price' => (float)( json_decode( $product->post_content, true )['price'] ?? 0 ), 'thumbnail' => get_the_post_thumbnail_url( $product->ID, 'thumbnail' ) ?: '' ] : null,
            ];
        }, $rows );
    }
}
