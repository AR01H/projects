<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Auth\Middleware;
use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class ReviewController {
    public function get_reviews( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_review', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        $reviews = array_map( function ( $post ) {
            $meta = json_decode( $post->post_content, true ) ?: [];
            return [
                'id' => (string) $post->ID, 'productId' => $meta['productId'] ?? '',
                'author' => $meta['author'] ?? '', 'rating' => (int)( $meta['rating'] ?? 5 ),
                'title' => $post->post_title, 'comment' => $meta['comment'] ?? '',
                'date' => $post->post_date, 'verified' => (bool)( $meta['verified'] ?? false ),
                'status' => $meta['status'] ?? 'approved',
            ];
        }, $posts );
        return Response::success( $reviews );
    }

    public function get_stats( \WP_REST_Request $request ): \WP_REST_Response {
        $reviews = $this->get_reviews( $request )->get_data()['data'] ?? [];
        $total = count( $reviews );
        $avg = $total > 0 ? array_sum( array_column( $reviews, 'rating' ) ) / $total : 0;
        $dist = [];
        for ( $i = 1; $i <= 5; $i++ ) $dist[] = [ 'star' => $i, 'count' => count( array_filter( $reviews, fn( $r ) => (int) $r['rating'] === $i ) ) ];
        return Response::success( [ 'totalReviews' => $total, 'avgRating' => round( $avg, 1 ), 'distribution' => $dist ] );
    }

    public function create_review( \WP_REST_Request $request ): \WP_REST_Response {
        $user = Middleware::get_user();
        if ( ! $user ) return Response::error( 'Unauthorized.', 401 );

        $data = $request->get_json_params();
        $product_id = sanitize_text_field( $data['productId'] ?? '' );
        $rating = max( 1, min( 5, (int)( $data['rating'] ?? 5 ) ) );
        $title = sanitize_text_field( $data['title'] ?? '' );
        $comment = sanitize_textarea_field( $data['comment'] ?? '' );
        $author = $user->display_name;

        if ( ! $product_id ) return Response::error( 'Product ID required.' );

        $content = wp_json_encode( [
            'productId' => $product_id, 'author' => $author, 'rating' => $rating,
            'comment' => $comment, 'verified' => true, 'status' => 'approved',
        ] );

        $id = wp_insert_post( [
            'post_type' => 'rh_review', 'post_title' => $title ?: "$author review",
            'post_content' => $content, 'post_status' => 'publish',
        ] );

        if ( is_wp_error( $id ) ) return Response::error( $id->get_error_message() );

        // Update product review count and rating
        $product = get_post( $product_id );
        if ( $product ) {
            $meta = json_decode( $product->post_content, true ) ?: [];
            $reviews = $meta['reviews'] ?? [];
            $reviews[] = [ 'id' => "rev-" . wp_generate_password( 4, false ), 'author' => $author, 'rating' => $rating, 'title' => $title, 'comment' => $comment, 'date' => current_time( 'mysql' ), 'verified' => true ];
            $meta['reviews'] = $reviews;
            $meta['reviewCount'] = count( $reviews );
            $meta['rating'] = round( array_sum( array_column( $reviews, 'rating' ) ) / count( $reviews ), 1 );
            wp_update_post( [ 'ID' => $product_id, 'post_content' => wp_json_encode( $meta ) ] );
        }

        return Response::success( [ 'id' => (string) $id, 'message' => 'Review submitted successfully' ], 201 );
    }
}
