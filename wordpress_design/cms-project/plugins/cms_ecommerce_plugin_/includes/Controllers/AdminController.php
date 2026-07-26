<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;
use CMS_ECOMMERCE\Helpers\Validator;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AdminController — Generic CRUD for ALL admin resources.
 * Handles products, categories, collections, banners, coupons, blog, tags, reviews, certifications.
 */
class AdminController {

    // ═══════════════════════════════════════
    //  PRODUCTS
    // ═══════════════════════════════════════
    public function list( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_product', 'posts_per_page' => -1, 'post_status' => 'any', 'orderby' => 'date', 'order' => 'DESC' ] );
        return Response::success( array_map( [ $this, 'format_product' ], $posts ) );
    }

    public function create( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $id = wp_insert_post( [ 'post_type' => 'rh_product', 'post_title' => $data['name'] ?? '', 'post_name' => sanitize_title( $data['slug'] ?? $data['name'] ?? '' ), 'post_content' => wp_json_encode( $data ), 'post_status' => 'publish' ] );
        if ( is_wp_error( $id ) ) return Response::error( $id->get_error_message() );
        return Response::success( $this->format_product( get_post( $id ) ), 201 );
    }

    public function update( \WP_REST_Request $request ): \WP_REST_Response {
        $id   = $request->get_param( 'id' );
        $data = $request->get_json_params();
        wp_update_post( [ 'ID' => $id, 'post_title' => $data['name'] ?? '', 'post_content' => wp_json_encode( $data ) ] );
        return Response::success( $this->format_product( get_post( $id ) ) );
    }

    public function delete( \WP_REST_Request $request ): \WP_REST_Response {
        $deleted = wp_delete_post( $request->get_param( 'id' ), true );
        return Response::success( $deleted !== false );
    }

    public function update_stock( \WP_REST_Request $request ): \WP_REST_Response {
        $id    = $request->get_param( 'id' );
        $stock = (int)( $request->get_json_params()['stock'] ?? 0 );
        $post  = get_post( $id );
        if ( ! $post ) return Response::error( 'Not found.', 404 );
        $meta = json_decode( $post->post_content, true ) ?: [];
        $meta['stock'] = $stock;
        wp_update_post( [ 'ID' => $id, 'post_content' => wp_json_encode( $meta ) ] );
        return Response::success( [ 'id' => (string) $id, 'stock' => $stock ] );
    }

    public function bulk_delete( \WP_REST_Request $request ): \WP_REST_Response {
        $ids = $request->get_json_params()['ids'] ?? [];
        $count = 0;
        foreach ( $ids as $id ) {
            if ( wp_delete_post( $id, true ) ) $count++;
        }
        return Response::success( $count );
    }

    private function format_product( \WP_Post $post ): array {
        $meta = json_decode( $post->post_content, true ) ?: [];
        return [
            'id' => (string) $post->ID, 'slug' => $post->post_name, 'name' => $post->post_title,
            'shortDescription' => $meta['shortDescription'] ?? '', 'description' => $meta['description'] ?? '',
            'categoryId' => $meta['categoryId'] ?? '', 'categorySlug' => $meta['categorySlug'] ?? '',
            'collectionIds' => $meta['collectionIds'] ?? [], 'price' => (float)( $meta['price'] ?? 0 ),
            'compareAtPrice' => $meta['compareAtPrice'] ?? null, 'currency' => $meta['currency'] ?? 'INR',
            'images' => $meta['images'] ?? [], 'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: '',
            'sku' => $meta['sku'] ?? '', 'specs' => $meta['specs'] ?? [], 'qualityBadges' => $meta['qualityBadges'] ?? [],
            'makerName' => $meta['makerName'] ?? null, 'stock' => (int)( $meta['stock'] ?? 0 ),
            'rating' => (float)( $meta['rating'] ?? 0 ), 'reviewCount' => (int)( $meta['reviewCount'] ?? 0 ),
            'reviews' => $meta['reviews'] ?? [], 'variants' => $meta['variants'] ?? [], 'tags' => $meta['tags'] ?? [],
            'isBestSeller' => (bool)( $meta['isBestSeller'] ?? false ), 'isNewArrival' => (bool)( $meta['isNewArrival'] ?? false ),
            'isFeatured' => (bool)( $meta['isFeatured'] ?? false ), 'isLimitedEdition' => (bool)( $meta['isLimitedEdition'] ?? false ),
            'isFestive' => (bool)( $meta['isFestive'] ?? false ), 'createdAt' => $post->post_date,
            'status' => $post->post_status === 'publish' ? 'active' : 'draft',
        ];
    }

    // ═══════════════════════════════════════
    //  ORDERS
    // ═══════════════════════════════════════
    public function list_orders( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $orders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rh_orders ORDER BY created_at DESC" );
        return Response::success( $orders );
    }

    public function get_order( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_orders WHERE id = %s", $request->get_param( 'id' ) ) );
        if ( ! $order ) return Response::error( 'Not found.', 404 );
        return Response::success( $order );
    }

    public function update_order_status( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $status = sanitize_text_field( $request->get_json_params()['status'] ?? '' );
        $wpdb->update( "{$wpdb->prefix}rh_orders", [ 'status' => $status, 'updated_at' => current_time( 'mysql' ) ], [ 'id' => $request->get_param( 'id' ) ] );
        $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rh_orders WHERE id = %s", $request->get_param( 'id' ) ) );
        return Response::success( $order );
    }

    // ═══════════════════════════════════════
    //  CATEGORIES (taxonomy)
    // ═══════════════════════════════════════
    public function list_categories( \WP_REST_Request $request ): \WP_REST_Response {
        $terms = get_terms( [ 'taxonomy' => 'rh_category', 'hide_empty' => false ] );
        if ( is_wp_error( $terms ) ) $terms = [];
        return Response::success( array_map( [ $this, 'format_category' ], $terms ) );
    }

    public function create_category( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $term_id = wp_insert_term( $data['name'] ?? '', 'rh_category', [ 'slug' => $data['slug'] ?? sanitize_title( $data['name'] ?? '' ), 'description' => wp_json_encode( $data ) ] );
        if ( is_wp_error( $term_id ) ) return Response::error( $term_id->get_error_message() );
        return Response::success( $this->format_category( get_term( $term_id['term_id'], 'rh_category' ) ), 201 );
    }

    public function update_category( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        wp_update_term( $request->get_param( 'id' ), 'rh_category', [ 'name' => $data['name'] ?? '', 'description' => wp_json_encode( $data ) ] );
        return Response::success( $this->format_category( get_term( $request->get_param( 'id' ), 'rh_category' ) ) );
    }

    public function delete_category( \WP_REST_Request $request ): \WP_REST_Response {
        $deleted = wp_delete_term( $request->get_param( 'id' ), 'rh_category' );
        return Response::success( $deleted !== false );
    }

    private function format_category( \WP_Term $term ): array {
        $meta = json_decode( $term->description, true ) ?: [];
        return [
            'id' => (string) $term->term_id, 'slug' => $term->slug, 'name' => $term->name,
            'description' => $meta['description'] ?? '', 'image' => $meta['image'] ?? '',
            'icon' => $meta['icon'] ?? null, 'productCount' => (int) $term->count,
            'featured' => (bool)( $meta['featured'] ?? false ),
            'parentSlug' => $term->parent ? ( get_term( $term->parent, 'rh_category' )->slug ?? null ) : null,
            'status' => 'active',
        ];
    }

    // ═══════════════════════════════════════
    //  COLLECTIONS
    // ═══════════════════════════════════════
    public function list_collections( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_collection', 'posts_per_page' => -1, 'post_status' => 'any' ] );
        return Response::success( array_map( function ( $p ) {
            $m = json_decode( $p->post_content, true ) ?: [];
            return [ 'id' => (string) $p->ID, 'slug' => $p->post_name, 'name' => $p->post_title, 'description' => $m['description'] ?? '', 'image' => get_the_post_thumbnail_url( $p->ID, 'large' ) ?: '', 'productIds' => $m['productIds'] ?? [], 'status' => $p->post_status === 'publish' ? 'active' : 'draft' ];
        }, $posts ) );
    }

    public function create_collection( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $id = wp_insert_post( [ 'post_type' => 'rh_collection', 'post_title' => $data['name'] ?? '', 'post_name' => sanitize_title( $data['slug'] ?? '' ), 'post_content' => wp_json_encode( $data ), 'post_status' => 'publish' ] );
        return Response::success( [ 'id' => (string) $id ], 201 );
    }

    public function update_collection( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        wp_update_post( [ 'ID' => $request->get_param( 'id' ), 'post_title' => $data['name'] ?? '', 'post_content' => wp_json_encode( $data ) ] );
        return Response::success( [ 'id' => $request->get_param( 'id' ) ] );
    }

    public function delete_collection( \WP_REST_Request $request ): \WP_REST_Response {
        return Response::success( wp_delete_post( $request->get_param( 'id' ), true ) !== false );
    }

    // ═══════════════════════════════════════
    //  BANNERS
    // ═══════════════════════════════════════
    public function list_banners( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_banner', 'posts_per_page' => -1, 'post_status' => 'any', 'orderby' => 'menu_order', 'order' => 'ASC' ] );
        return Response::success( array_map( function ( $p ) {
            $m = json_decode( $p->post_content, true ) ?: [];
            return [ 'id' => (string) $p->ID, 'title' => $p->post_title, 'subtitle' => $m['subtitle'] ?? '', 'ctaLabel' => $m['ctaLabel'] ?? '', 'ctaLink' => $m['ctaLink'] ?? '', 'image' => get_the_post_thumbnail_url( $p->ID, 'full' ) ?: '', 'theme' => $m['theme'] ?? 'light', 'position' => $m['position'] ?? 'hero', 'sortOrder' => (int) $p->menu_order, 'status' => $p->post_status === 'publish' ? 'active' : 'draft' ];
        }, $posts ) );
    }

    public function create_banner( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $id = wp_insert_post( [ 'post_type' => 'rh_banner', 'post_title' => $data['title'] ?? '', 'post_content' => wp_json_encode( $data ), 'menu_order' => $data['sortOrder'] ?? 0, 'post_status' => 'publish' ] );
        return Response::success( [ 'id' => (string) $id ], 201 );
    }

    public function update_banner( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        wp_update_post( [ 'ID' => $request->get_param( 'id' ), 'post_title' => $data['title'] ?? '', 'post_content' => wp_json_encode( $data ), 'menu_order' => $data['sortOrder'] ?? 0 ] );
        return Response::success( [ 'id' => $request->get_param( 'id' ) ] );
    }

    public function delete_banner( \WP_REST_Request $request ): \WP_REST_Response {
        return Response::success( wp_delete_post( $request->get_param( 'id' ), true ) !== false );
    }

    public function reorder_banners( \WP_REST_Request $request ): \WP_REST_Response {
        $ids = $request->get_json_params()['ids'] ?? [];
        foreach ( $ids as $i => $id ) wp_update_post( [ 'ID' => $id, 'menu_order' => $i ] );
        return Response::success( true );
    }

    // ═══════════════════════════════════════
    //  COUPONS (by code, not ID)
    // ═══════════════════════════════════════
    public function list_coupons( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_coupon', 'posts_per_page' => -1, 'post_status' => 'any' ] );
        return Response::success( array_map( function ( $p ) {
            $m = json_decode( $p->post_content, true ) ?: [];
            return array_merge( [ 'code' => strtoupper( $p->post_name ), 'description' => $p->post_title, 'active' => $p->post_status === 'publish' ], $m );
        }, $posts ) );
    }

    public function create_coupon( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $code = strtoupper( $data['code'] ?? '' );
        $id = wp_insert_post( [ 'post_type' => 'rh_coupon', 'post_title' => $data['description'] ?? $code, 'post_name' => strtolower( $code ), 'post_content' => wp_json_encode( $data ), 'post_status' => ( $data['active'] ?? true ) ? 'publish' : 'draft' ] );
        return Response::success( array_merge( [ 'code' => $code ], $data ), 201 );
    }

    public function update_coupon( \WP_REST_Request $request ): \WP_REST_Response {
        $code = strtolower( $request->get_param( 'code' ) );
        $data = $request->get_json_params();
        $post = get_posts( [ 'post_type' => 'rh_coupon', 'name' => $code, 'numberposts' => 1 ] );
        if ( empty( $post ) ) return Response::error( 'Coupon not found.', 404 );
        wp_update_post( [ 'ID' => $post[0]->ID, 'post_content' => wp_json_encode( $data ), 'post_status' => ( $data['active'] ?? true ) ? 'publish' : 'draft' ] );
        return Response::success( array_merge( [ 'code' => strtoupper( $code ) ], $data ) );
    }

    public function delete_coupon( \WP_REST_Request $request ): \WP_REST_Response {
        $post = get_posts( [ 'post_type' => 'rh_coupon', 'name' => strtolower( $request->get_param( 'code' ) ), 'numberposts' => 1 ] );
        if ( empty( $post ) ) return Response::error( 'Not found.', 404 );
        return Response::success( wp_delete_post( $post[0]->ID, true ) !== false );
    }

    public function toggle_coupon( \WP_REST_Request $request ): \WP_REST_Response {
        $post = get_posts( [ 'post_type' => 'rh_coupon', 'name' => strtolower( $request->get_param( 'code' ) ), 'numberposts' => 1 ] );
        if ( empty( $post ) ) return Response::error( 'Not found.', 404 );
        $new_status = $post[0]->post_status === 'publish' ? 'draft' : 'publish';
        wp_update_post( [ 'ID' => $post[0]->ID, 'post_status' => $new_status ] );
        return Response::success( [ 'code' => strtoupper( $post[0]->post_name ), 'active' => $new_status === 'publish' ] );
    }

    // ═══════════════════════════════════════
    //  BLOG
    // ═══════════════════════════════════════
    public function list_blog_posts( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_blog_post', 'posts_per_page' => -1, 'post_status' => 'any' ] );
        return Response::success( array_map( function ( $p ) {
            $m = json_decode( $p->post_content, true ) ?: [];
            return [ 'id' => (string) $p->ID, 'slug' => $p->post_name, 'title' => $p->post_title, 'excerpt' => $m['excerpt'] ?? '', 'categorySlug' => $m['categorySlug'] ?? '', 'status' => $p->post_status === 'publish' ? 'published' : 'draft' ];
        }, $posts ) );
    }

    public function create_blog_post( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $id = wp_insert_post( [ 'post_type' => 'rh_blog_post', 'post_title' => $data['title'] ?? '', 'post_name' => sanitize_title( $data['slug'] ?? '' ), 'post_content' => wp_json_encode( $data ), 'post_status' => ( $data['status'] ?? 'published' ) === 'published' ? 'publish' : 'draft' ] );
        return Response::success( [ 'id' => (string) $id ], 201 );
    }

    public function update_blog_post( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        wp_update_post( [ 'ID' => $request->get_param( 'id' ), 'post_title' => $data['title'] ?? '', 'post_content' => wp_json_encode( $data ), 'post_status' => ( $data['status'] ?? 'published' ) === 'published' ? 'publish' : 'draft' ] );
        return Response::success( [ 'id' => $request->get_param( 'id' ) ] );
    }

    public function delete_blog_post( \WP_REST_Request $request ): \WP_REST_Response {
        return Response::success( wp_delete_post( $request->get_param( 'id' ), true ) !== false );
    }

    public function list_blog_categories( \WP_REST_Request $request ): \WP_REST_Response {
        $terms = get_terms( [ 'taxonomy' => 'rh_blog_cat', 'hide_empty' => false ] );
        if ( is_wp_error( $terms ) ) $terms = [];
        return Response::success( array_map( function ( $t ) {
            return [ 'id' => (string) $t->term_id, 'slug' => $t->slug, 'name' => $t->name ];
        }, $terms ) );
    }

    // ═══════════════════════════════════════
    //  TAGS
    // ═══════════════════════════════════════
    public function list_tags( \WP_REST_Request $request ): \WP_REST_Response {
        $terms = get_terms( [ 'taxonomy' => 'rh_tag', 'hide_empty' => false ] );
        if ( is_wp_error( $terms ) ) $terms = [];
        return Response::success( array_map( function ( $t ) {
            return [ 'tag' => $t->slug, 'label' => $t->name, 'parentTag' => $t->parent ? ( get_term( $t->parent, 'rh_tag' )->slug ?? null ) : null ];
        }, $terms ) );
    }

    public function create_tag( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $term_id = wp_insert_term( $data['label'] ?? $data['tag'] ?? '', 'rh_tag', [ 'slug' => $data['tag'] ?? '' ] );
        if ( is_wp_error( $term_id ) ) return Response::error( $term_id->get_error_message() );
        return Response::success( [ 'tag' => $data['tag'] ?? '', 'label' => $data['label'] ?? '' ], 201 );
    }

    public function update_tag( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $term = get_term_by( 'slug', $request->get_param( 'tag' ), 'rh_tag' );
        if ( ! $term ) return Response::error( 'Not found.', 404 );
        wp_update_term( $term->term_id, 'rh_tag', [ 'name' => $data['label'] ?? $term->name ] );
        return Response::success( [ 'tag' => $request->get_param( 'tag' ), 'label' => $data['label'] ?? $term->name ] );
    }

    public function delete_tag( \WP_REST_Request $request ): \WP_REST_Response {
        $term = get_term_by( 'slug', $request->get_param( 'tag' ), 'rh_tag' );
        if ( ! $term ) return Response::error( 'Not found.', 404 );
        return Response::success( wp_delete_term( $term->term_id, 'rh_tag' ) !== false );
    }

    // ═══════════════════════════════════════
    //  REVIEWS (admin delete)
    // ═══════════════════════════════════════
    public function list_reviews( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_review', 'posts_per_page' => -1, 'post_status' => 'any' ] );
        return Response::success( array_map( function ( $p ) {
            $m = json_decode( $p->post_content, true ) ?: [];
            return [ 'id' => (string) $p->ID, 'productId' => $m['productId'] ?? '', 'author' => $m['author'] ?? '', 'rating' => (int)( $m['rating'] ?? 5 ), 'title' => $p->post_title, 'comment' => $m['comment'] ?? '', 'date' => $p->post_date, 'verified' => (bool)( $m['verified'] ?? false ), 'status' => $m['status'] ?? 'approved' ];
        }, $posts ) );
    }

    public function delete_review( \WP_REST_Request $request ): \WP_REST_Response {
        return Response::success( wp_delete_post( $request->get_param( 'id' ), true ) !== false );
    }

    // ═══════════════════════════════════════
    //  CERTIFICATIONS
    // ═══════════════════════════════════════

    // ═══════════════════════════════════════
    //  CUSTOMERS
    // ═══════════════════════════════════════
    public function list_customers( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;
        $customers = $wpdb->get_results(
            "SELECT u.ID as id, u.display_name as name, u.user_email as email, u.user_registered as createdAt,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}rh_orders WHERE user_id = u.ID) as orderCount,
                    (SELECT COALESCE(SUM(total),0) FROM {$wpdb->prefix}rh_orders WHERE user_id = u.ID AND status != 'cancelled') as totalSpent
             FROM {$wpdb->users} u
             ORDER BY u.user_registered DESC"
        );
        $result = array_map( function ( $c ) {
            return [
                'id' => (string) $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'createdAt' => $c->createdAt,
                'orderCount' => (int) $c->orderCount,
                'totalSpent' => (float) $c->totalSpent,
            ];
        }, $customers );
        return Response::success( $result );
    }

    // ═══════════════════════════════════════
    //  CERTIFICATIONS
    // ═══════════════════════════════════════
    public function list_certifications( \WP_REST_Request $request ): \WP_REST_Response {
        return Response::success( get_option( 'cms_certifications', [] ) );
    }

    public function create_certification( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $certs = get_option( 'cms_certifications', [] );
        $data['id'] = wp_generate_uuid4();
        $certs[] = $data;
        update_option( 'cms_certifications', $certs );
        return Response::success( $data, 201 );
    }

    public function update_certification( \WP_REST_Request $request ): \WP_REST_Response {
        $id   = $request->get_param( 'id' );
        $data = $request->get_json_params();
        $certs = get_option( 'cms_certifications', [] );
        foreach ( $certs as &$c ) { if ( $c['id'] === $id ) { $c = array_merge( $c, $data ); break; } }
        update_option( 'cms_certifications', $certs );
        return Response::success( $data );
    }

    public function delete_certification( \WP_REST_Request $request ): \WP_REST_Response {
        $id = $request->get_param( 'id' );
        $certs = get_option( 'cms_certifications', [] );
        $certs = array_filter( $certs, fn( $c ) => $c['id'] !== $id );
        update_option( 'cms_certifications', array_values( $certs ) );
        return Response::success( true );
    }
}
