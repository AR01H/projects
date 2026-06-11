<?php
/**
 * includes/rules_conditions.php — Global Filters & Conditional Rules
 *
 * RULE: Group related filters together.
 *       Use arrays of callbacks so rules can be added/removed easily.
 *       No business logic — only hook wiring.
 *
 * Pattern:
 *   $filters = [ [ 'hook', 'callback', priority, args ], … ]
 *   foreach loop registers them all.
 */

defined( 'ABSPATH' ) || exit;

// ── 1. Define all filters in one place ───────────────────────────
$npt_filters = [
    // [ hook,               callback,                       priority, args ]
    [ 'the_content',        'npt_filter_content',            10,       1 ],
    [ 'excerpt_length',     'npt_filter_excerpt_length',     10,       1 ],
    [ 'excerpt_more',       'npt_filter_excerpt_more',       10,       1 ],
    [ 'body_class',         'npt_filter_body_class',         10,       1 ],
    [ 'document_title_parts', 'npt_filter_title',            10,       1 ],
];

foreach ( $npt_filters as [ $hook, $cb, $prio, $args ] ) {
    add_filter( $hook, $cb, $prio, $args );
}

// ── 2. Filter callbacks ───────────────────────────────────────────

/** Append a custom wrapper class to the content */
function npt_filter_content( string $content ): string {
    // stub — wrap content or modify here
    return $content;
}

/** Control excerpt word count */
function npt_filter_excerpt_length( int $length ): int {
    return 30; // words
}

/** Change the […] more string */
function npt_filter_excerpt_more( string $more ): string {
    return '&hellip;';
}

/** Add conditional body classes */
function npt_filter_body_class( array $classes ): array {
    if ( is_singular( 'portfolio' ) ) {
        $classes[] = 'is-portfolio';
    }
    return $classes;
}

/** Modify document <title> parts */
function npt_filter_title( array $parts ): array {
    // stub — modify $parts['title'], $parts['tagline'], etc.
    return $parts;
}
