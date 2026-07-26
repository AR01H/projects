<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MigrationController — Import JSON data from theme data/ folder into WordPress.
 * Supports: products, categories, collections, banners, blog_posts, blog_categories, tags, certifications, coupons, reviews
 */
class MigrationController {

    private array $importers = [
        'products'         => 'import_products',
        'categories'       => 'import_categories',
        'collections'      => 'import_collections',
        'banners'          => 'import_banners',
        'blog_posts'       => 'import_blog_posts',
        'blog_categories'  => 'import_blog_categories',
        'tags'             => 'import_tags',
        'certifications'   => 'import_certifications',
        'coupons'          => 'import_coupons',
        'reviews'          => 'import_reviews',
    ];

    public function import( \WP_REST_Request $request ): \WP_REST_Response {
        $type = $request->get_param( 'type' );
        if ( $type === 'all' ) return $this->import_all();
        if ( ! isset( $this->importers[ $type ] ) ) return Response::error( "Unknown type: $type" );

        $result = $this->{ $this->importers[ $type ] }();
        return Response::success( $result );
    }

    public function status( \WP_REST_Request $request ): \WP_REST_Response {
        $status = [];
        $status['products']        = wp_count_posts( 'rh_product' )->publish ?? 0;
        $status['categories']      = wp_count_terms( 'rh_category', [ 'hide_empty' => false ] ) instanceof \WP_Error ? 0 : (int) wp_count_terms( 'rh_category', [ 'hide_empty' => false ] );
        $status['collections']     = wp_count_posts( 'rh_collection' )->publish ?? 0;
        $status['banners']         = wp_count_posts( 'rh_banner' )->publish ?? 0;
        $status['blog_posts']      = wp_count_posts( 'rh_blog_post' )->publish ?? 0;
        $status['blog_categories'] = wp_count_terms( 'rh_blog_cat', [ 'hide_empty' => false ] ) instanceof \WP_Error ? 0 : (int) wp_count_terms( 'rh_blog_cat', [ 'hide_empty' => false ] );
        $status['certifications']  = count( get_option( 'cms_certifications', [] ) );
        return Response::success( $status );
    }

    public function clear( \WP_REST_Request $request ): \WP_REST_Response {
        $type = $request->get_param( 'type' );
        if ( $type === 'all' ) {
            $this->delete_all_posts( 'rh_product' );
            $this->delete_all_posts( 'rh_collection' );
            $this->delete_all_posts( 'rh_banner' );
            $this->delete_all_posts( 'rh_blog_post' );
            $this->delete_all_terms( 'rh_category' );
            $this->delete_all_terms( 'rh_blog_cat' );
            $this->delete_all_terms( 'rh_tag' );
            delete_option( 'cms_certifications' );
            return Response::success( 'All data cleared' );
        }
        return Response::success( "Cleared $type" );
    }

    private function import_all(): \WP_REST_Response {
        $results = [];
        foreach ( $this->importers as $type => $method ) {
            $results[ $type ] = $this->{ $method }();
        }
        return Response::success( $results );
    }

    // ── Individual importers ──

    private function import_products(): array {
        $json = $this->load_json( 'products' );
        $count = 0;
        foreach ( $json as $item ) {
            $existing = get_posts( [ 'post_type' => 'rh_product', 'name' => $item['slug'] ?? '', 'numberposts' => 1 ] );
            if ( ! empty( $existing ) ) continue;
            wp_insert_post( [ 'post_type' => 'rh_product', 'post_title' => $item['name'] ?? '', 'post_name' => $item['slug'] ?? '', 'post_content' => wp_json_encode( $item ), 'post_status' => 'publish' ] );
            $count++;
        }
        return [ 'imported' => $count, 'total' => count( $json ) ];
    }

    private function import_categories(): array {
        $json = $this->load_json( 'categories' );
        $count = 0;
        foreach ( $json as $item ) {
            $existing = get_term_by( 'slug', $item['slug'] ?? '', 'rh_category' );
            if ( $existing ) continue;
            wp_insert_term( $item['name'] ?? '', 'rh_category', [ 'slug' => $item['slug'] ?? '', 'description' => wp_json_encode( $item ) ] );
            $count++;
        }
        return [ 'imported' => $count, 'total' => count( $json ) ];
    }

    private function import_collections(): array {
        $json = $this->load_json( 'collections' );
        $count = 0;
        foreach ( $json as $item ) {
            $existing = get_posts( [ 'post_type' => 'rh_collection', 'name' => $item['slug'] ?? '', 'numberposts' => 1 ] );
            if ( ! empty( $existing ) ) continue;
            wp_insert_post( [ 'post_type' => 'rh_collection', 'post_title' => $item['name'] ?? '', 'post_name' => $item['slug'] ?? '', 'post_content' => wp_json_encode( $item ), 'post_status' => 'publish' ] );
            $count++;
        }
        return [ 'imported' => $count, 'total' => count( $json ) ];
    }

    private function import_banners(): array {
        $json = $this->load_json( 'banners' );
        $count = 0;
        // Import hero banners
        foreach ( $json['hero'] ?? [] as $item ) {
            $existing = get_posts( [ 'post_type' => 'rh_banner', 'name' => $item['id'] ?? '', 'numberposts' => 1 ] );
            if ( ! empty( $existing ) ) continue;
            wp_insert_post( [ 'post_type' => 'rh_banner', 'post_title' => $item['title'] ?? '', 'post_name' => $item['id'] ?? '', 'post_content' => wp_json_encode( array_merge( $item, [ 'position' => 'hero' ] ) ), 'post_status' => 'publish' ] );
            $count++;
        }
        return [ 'imported' => $count, 'total' => count( $json['hero'] ?? [] ) ];
    }

    private function import_blog_posts(): array {
        $json = $this->load_json( 'blogPosts' );
        $count = 0;
        foreach ( $json as $item ) {
            $existing = get_posts( [ 'post_type' => 'rh_blog_post', 'name' => $item['slug'] ?? '', 'numberposts' => 1 ] );
            if ( ! empty( $existing ) ) continue;
            wp_insert_post( [ 'post_type' => 'rh_blog_post', 'post_title' => $item['title'] ?? '', 'post_name' => $item['slug'] ?? '', 'post_content' => wp_json_encode( $item ), 'post_status' => 'publish' ] );
            $count++;
        }
        return [ 'imported' => $count, 'total' => count( $json ) ];
    }

    private function import_blog_categories(): array {
        $json = $this->load_json( 'blogCategories' );
        $count = 0;
        foreach ( $json as $item ) {
            $existing = get_term_by( 'slug', $item['slug'] ?? '', 'rh_blog_cat' );
            if ( $existing ) continue;
            wp_insert_term( $item['name'] ?? '', 'rh_blog_cat', [ 'slug' => $item['slug'] ?? '' ] );
            $count++;
        }
        return [ 'imported' => $count, 'total' => count( $json ) ];
    }

    private function import_tags(): array {
        $json = $this->load_json( 'tags' );
        $count = 0;
        foreach ( $json as $item ) {
            $existing = get_term_by( 'slug', $item['tag'] ?? '', 'rh_tag' );
            if ( $existing ) continue;
            wp_insert_term( $item['label'] ?? $item['tag'] ?? '', 'rh_tag', [ 'slug' => $item['tag'] ?? '' ] );
            $count++;
        }
        return [ 'imported' => $count, 'total' => count( $json ) ];
    }

    private function import_certifications(): array {
        $json = $this->load_json( 'certifications' );
        $existing = get_option( 'cms_certifications', [] );
        $existing_slugs = array_column( $existing, 'id' );
        $count = 0;
        foreach ( $json as $item ) {
            if ( in_array( $item['id'] ?? '', $existing_slugs ) ) continue;
            $existing[] = $item;
            $count++;
        }
        update_option( 'cms_certifications', $existing );
        return [ 'imported' => $count, 'total' => count( $json ) ];
    }

    private function import_coupons(): array {
        $json = $this->load_json( 'coupons' );
        $count = 0;
        foreach ( $json as $item ) {
            $code = strtolower( $item['code'] ?? '' );
            $existing = get_posts( [ 'post_type' => 'rh_coupon', 'name' => $code, 'numberposts' => 1 ] );
            if ( ! empty( $existing ) ) continue;
            wp_insert_post( [ 'post_type' => 'rh_coupon', 'post_title' => $item['description'] ?? $code, 'post_name' => $code, 'post_content' => wp_json_encode( $item ), 'post_status' => 'publish' ] );
            $count++;
        }
        return [ 'imported' => $count, 'total' => count( $json ) ];
    }

    private function import_reviews(): array {
        return [ 'imported' => 0, 'total' => 0, 'note' => 'Reviews are embedded in products' ];
    }

    // ── Helpers ──

    private function load_json( string $name ): mixed {
        // Try theme data directory first, then plugin data directory
        $paths = [
            get_template_directory() . "/data/$name.json",
            get_stylesheet_directory() . "/data/$name.json",
            CMS_ECOMMERCE_PATH . "data/$name.json",
        ];
        foreach ( $paths as $path ) {
            if ( file_exists( $path ) ) {
                $content = file_get_contents( $path );
                return json_decode( $content, true ) ?? [];
            }
        }
        return [];
    }

    private function delete_all_posts( string $post_type ): void {
        $posts = get_posts( [ 'post_type' => $post_type, 'posts_per_page' => -1, 'post_status' => 'any' ] );
        foreach ( $posts as $p ) wp_delete_post( $p->ID, true );
    }

    private function delete_all_terms( string $taxonomy ): void {
        $terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $t ) wp_delete_term( $t->term_id, $taxonomy );
        }
    }
}
