<?php
namespace CMS_ECOMMERCE\Controllers;

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
}
