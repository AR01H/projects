<?php
/**
 * apis/models/page-sample.php — API Model: Data Shape & Formatter
 *
 * RULE: Models transform raw WP data into clean API-ready arrays.
 *       They do NOT run queries or register hooks.
 *       All meta keys come from static/page-sample.php constants.
 *
 * Pattern:
 *   npt_model_<post_type>( WP_Post $post ): array
 *
 * Redirect / Alternate URL logic:
 *   Every model checks for:
 *     NPT_META_REDIRECT   → npt_redirect_url   (hard redirect destination)
 *     NPT_META_ALT_URL    → npt_alternate_url   (alternate / alias URL)
 *     NPT_META_ALT_TITLE  → npt_alternate_title (alternate display name)
 *     NPT_META_ALT_SLUG   → npt_alternate_slug  (alternate slug / path alias)
 *     NPT_META_CANONICAL  → npt_canonical_url   (SEO canonical override)
 *
 *   Use npt_model_urls() to build the full URL block — call it from every model.
 */

defined( 'ABSPATH' ) || exit;

// ═══════════════════════════════════════════════════════════════════
// ── A. SHARED URL BLOCK BUILDER (used by every model) ───────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Build the standard URL block for any post/CPT.
 *
 * Returns:
 *   url          → canonical permalink (WP default)
 *   canonical    → SEO canonical override (or falls back to url)
 *   redirect_url → if set, clients/middleware should 301 here instead
 *   alternate    → [ url, title, slug ] alternate name/alias data
 *   has_redirect → bool shortcut for quick checks
 *
 * @param WP_Post $post
 * @return array
 */
function npt_model_urls( WP_Post $post ): array {
    $permalink    = get_permalink( $post );
    $redirect     = get_post_meta( $post->ID, NPT_META_REDIRECT,  true ) ?: null;
    $alt_url      = get_post_meta( $post->ID, NPT_META_ALT_URL,   true ) ?: null;
    $alt_title    = get_post_meta( $post->ID, NPT_META_ALT_TITLE, true ) ?: null;
    $alt_slug     = get_post_meta( $post->ID, NPT_META_ALT_SLUG,  true ) ?: null;
    $canonical    = get_post_meta( $post->ID, NPT_META_CANONICAL, true ) ?: $permalink;

    return [
        'url'          => $permalink,
        'canonical'    => $canonical,
        'redirect_url' => $redirect,
        'has_redirect' => ! empty( $redirect ),
        'alternate'    => [
            'url'   => $alt_url,
            'title' => $alt_title,
            'slug'  => $alt_slug,
        ],
    ];
}

// ═══════════════════════════════════════════════════════════════════
// ── B. POST MODEL ───────────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Format a standard WP post as an API response object.
 *
 * @param WP_Post $post
 * @return array
 */
function npt_model_post( WP_Post $post ): array {
    return [
        'id'        => $post->ID,
        'title'     => get_the_title( $post ),
        'slug'      => $post->post_name,
        'excerpt'   => get_the_excerpt( $post ),
        'content'   => apply_filters( 'the_content', $post->post_content ),
        'date'      => get_the_date( 'c', $post ),
        'modified'  => get_the_modified_date( 'c', $post ),
        'thumbnail' => get_the_post_thumbnail_url( $post, NPT_IMG_CARD ) ?: null,
        'author'    => get_the_author_meta( 'display_name', $post->post_author ),
        'categories' => npt_model_terms( $post->ID, 'category' ),
        'tags'       => npt_model_terms( $post->ID, 'post_tag' ),
        'urls'       => npt_model_urls( $post ),   // ← redirect + alternate block
    ];
}

// ═══════════════════════════════════════════════════════════════════
// ── C. PORTFOLIO MODEL ──────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Format a Portfolio CPT post.
 *
 * @param WP_Post $post
 * @return array
 */
function npt_model_portfolio( WP_Post $post ): array {
    return [
        'id'         => $post->ID,
        'title'      => get_the_title( $post ),
        'slug'       => $post->post_name,
        'excerpt'    => get_the_excerpt( $post ),
        'thumbnail'  => get_the_post_thumbnail_url( $post, NPT_IMG_HERO ) ?: null,
        'categories' => npt_model_terms( $post->ID, NPT_TAX_PORTFOLIO ),
        'client'     => get_post_meta( $post->ID, NPT_META_CLIENT, true ) ?: null,
        'year'       => get_post_meta( $post->ID, NPT_META_YEAR,   true ) ?: null,
        'urls'       => npt_model_urls( $post ),   // ← redirect + alternate block
    ];
}

// ═══════════════════════════════════════════════════════════════════
// ── D. TAXONOMY TERM MODEL ──────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Format taxonomy terms for a post as a flat array.
 *
 * @param int    $post_id
 * @param string $taxonomy
 * @return array
 */
function npt_model_terms( int $post_id, string $taxonomy ): array {
    $terms = get_the_terms( $post_id, $taxonomy );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return [];
    }
    return array_map( fn( $t ) => [
        'id'   => $t->term_id,
        'name' => $t->name,
        'slug' => $t->slug,
        'url'  => get_term_link( $t ),
    ], $terms );
}

// ═══════════════════════════════════════════════════════════════════
// ── E. REDIRECT HELPER (used by REST callbacks & page templates) ─────
// ═══════════════════════════════════════════════════════════════════

/**
 * Check if a model array has a redirect and perform it.
 *
 * Call this at the TOP of any REST callback or page template
 * immediately after formatting a model.
 *
 * In REST context  → returns a WP_REST_Response with 301 status.
 * In template context → calls wp_redirect() and exits.
 *
 * @param array  $model        The shaped model array (must have 'urls' key).
 * @param string $context      'rest' | 'template'
 * @param int    $status       HTTP status code (default 301).
 * @return WP_REST_Response|void
 */
function npt_maybe_redirect( array $model, string $context = 'template', int $status = 301 ) {
    if ( empty( $model['urls']['has_redirect'] ) ) {
        return; // no redirect — continue normally
    }

    $destination = $model['urls']['redirect_url'];

    if ( $context === 'rest' ) {
        $response = new WP_REST_Response( null, $status );
        $response->header( 'Location', esc_url_raw( $destination ) );
        return $response;
    }

    // template context
    wp_redirect( esc_url_raw( $destination ), $status );
    exit;
}
