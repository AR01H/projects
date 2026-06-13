<?php
/**
 * common/common_functions.php - Global Helper / Utility Functions
 *
 * RULE: Pure functions only. No hooks, no globals.
 *       Every function must be prefixed `npt_` to avoid collisions.
 *       Group functions by concern with section comments.
 */

defined( 'ABSPATH' ) || exit;

// ═══════════════════════════════════════════════════════════════════
// ── A. STRING HELPERS ───────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Truncate a string to N characters, appending an ellipsis.
 */
function npt_truncate( string $str, int $limit = 100, string $end = '…' ): string {
    return mb_strlen( $str ) > $limit
        ? mb_substr( $str, 0, $limit ) . $end
        : $str;
}

/**
 * Convert a slug/handle to a human-readable label.
 * e.g. "portfolio-category" → "Portfolio Category"
 */
function npt_slug_to_label( string $slug ): string {
    return ucwords( str_replace( [ '-', '_' ], ' ', $slug ) );
}

// ═══════════════════════════════════════════════════════════════════
// ── B. ARRAY HELPERS ────────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Pluck a single key from an array of arrays.
 * e.g. npt_pluck( $posts, 'ID' )
 */
function npt_pluck( array $items, string $key ): array {
    return array_column( $items, $key );
}

/**
 * Return a value from a nested array using dot notation.
 * e.g. npt_array_get( $data, 'meta.color', '#fff' )
 */
function npt_array_get( array $array, string $key, mixed $default = null ): mixed {
    foreach ( explode( '.', $key ) as $segment ) {
        if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
            return $default;
        }
        $array = $array[ $segment ];
    }
    return $array;
}

// ═══════════════════════════════════════════════════════════════════
// ── C. TEMPLATE HELPERS ─────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Load a component template with context variables.
 *
 * Usage: npt_component( 'cards/post-card', [ 'post' => $post ] );
 */
function npt_component( string $name, array $context = [] ): void {
    $file = get_template_directory() . "/components/{$name}.php";
    if ( ! file_exists( $file ) ) {
        // fail silently in production, log in dev
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( "[NPT] Component not found: {$file}" );
        }
        return;
    }
    // extract $context keys as local variables inside the component
    extract( $context, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
    include $file;
}

/**
 * Get a template part from the /pages/ folder.
 * Wraps get_template_part() with the theme's pages directory.
 */
function npt_get_page_template( string $slug, array $args = [] ): void {
    get_template_part( 'pages/page-' . $slug, null, $args );
}

// ═══════════════════════════════════════════════════════════════════
// ── D. QUERY HELPERS ────────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Build and return a WP_Query for a CPT with sensible defaults.
 *
 * @param string $post_type CPT slug
 * @param array  $overrides Additional WP_Query args
 */
function npt_get_posts( string $post_type, array $overrides = [] ): WP_Query {
    $defaults = [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'no_found_rows'  => false,
    ];
    return new WP_Query( array_merge( $defaults, $overrides ) );
}

// ═══════════════════════════════════════════════════════════════════
// ── E. ASSET HELPERS ────────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Return the versioned URI of a theme asset.
 * e.g. npt_asset( 'js/main.js' )
 */
function npt_asset( string $path ): string {
    return get_template_directory_uri() . '/assets/' . ltrim( $path, '/' );
}

/**
 * Return the absolute path to a theme asset.
 */
function npt_asset_path( string $path ): string {
    return get_template_directory() . '/assets/' . ltrim( $path, '/' );
}
