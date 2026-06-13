<?php
/**
 * includes/data_fetcher/page-sample.php - Data Fetcher Layer Sample
 *
 * PURPOSE: All WP_Query / get_posts calls live here.
 *          Returns clean, typed arrays - NOT WP_Post objects.
 *          Components and page templates call these functions.
 *
 * RULE: No HTML output. No hooks. Only data retrieval + formatting.
 *       Use npt_model_*() from apis/models/page-sample.php to shape data.
 */

defined( 'ABSPATH' ) || exit;

// ═══════════════════════════════════════════════════════════════════
// ── 1. Generic paged query (reusable base) ──────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Run a flexible WP_Query and return shaped results.
 *
 * @param string   $post_type    CPT slug.
 * @param array    $args         Extra WP_Query args.
 * @param callable $formatter    Optional callback to shape each post (default: raw WP_Post).
 * @return array   [ 'items' => [], 'total' => int, 'pages' => int ]
 */
function npt_fetch_posts( string $post_type, array $args = [], callable $formatter = null ): array {
    $query = new WP_Query( array_merge( [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'no_found_rows'  => false,
    ], $args ) );

    $items = $formatter
        ? array_map( $formatter, $query->posts )
        : $query->posts;

    return [
        'items' => $items,
        'total' => $query->found_posts,
        'pages' => $query->max_num_pages,
    ];
}

// ═══════════════════════════════════════════════════════════════════
// ── 2. Named fetchers per content type ─────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Fetch published blog posts (paginated).
 *
 * @param int $page    Page number.
 * @param int $per_page
 */
function npt_fetch_blog_posts( int $page = 1, int $per_page = 10 ): array {
    return npt_fetch_posts( 'post', [
        'paged'          => $page,
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ], 'npt_model_post' );
}

/**
 * Fetch portfolio items, optionally filtered by category slug.
 *
 * @param string|null $category_slug
 * @param int         $per_page
 */
function npt_fetch_portfolios( ?string $category_slug = null, int $per_page = 12 ): array {
    $args = [ 'posts_per_page' => $per_page ];

    if ( $category_slug ) {
        $args['tax_query'] = [ [
            'taxonomy' => 'portfolio-category',
            'field'    => 'slug',
            'terms'    => $category_slug,
        ] ];
    }

    return npt_fetch_posts( 'portfolio', $args, 'npt_model_portfolio' );
}

/**
 * Fetch a single post by ID or slug.
 *
 * @param int|string $identifier Post ID (int) or slug (string).
 * @param string     $post_type
 */
function npt_fetch_single( int|string $identifier, string $post_type = 'post' ): ?array {
    $args = is_int( $identifier )
        ? [ 'p' => $identifier ]
        : [ 'name' => $identifier ];

    $result = npt_fetch_posts( $post_type, array_merge( $args, [ 'posts_per_page' => 1 ] ) );
    return $result['items'][0] ?? null;
}

// ═══════════════════════════════════════════════════════════════════
// ── 3. Taxonomy term fetcher ────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Fetch all terms for a taxonomy as a clean array.
 *
 * @param string $taxonomy
 * @return array
 */
function npt_fetch_terms( string $taxonomy ): array {
    $terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return [];
    }

    return array_map( fn( $t ) => [
        'id'    => $t->term_id,
        'name'  => $t->name,
        'slug'  => $t->slug,
        'count' => $t->count,
        'url'   => get_term_link( $t ),
    ], $terms );
}
