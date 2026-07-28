<?php
/**
 * Template Name: Reviews
 *
 * pages/PageReviews.php - Paginated listing of active client reviews from
 * AH_Reviews_Model. Each card renders via AH_Reviews_Model::render_review(),
 * so the "big_box" / "mini_card" / "full_story" layout each review uses is
 * whatever was chosen for it in the CMS admin (Representing field) - the
 * page itself doesn't hardcode a single card design.
 */

defined( 'ABSPATH' ) || exit;

get_header(); // Loads wp_head() which triggers wp_enqueue_scripts hook

// ── Page + pagination ────────────────────────────────────────────────────
$page_id = get_queried_object_id();
$paged   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;

$review_header = null;
if ( class_exists( 'AH_Reviews_Model' ) ) {
    try {
        $review_header = ( new AH_Reviews_Model() )->get_page_header( $page_id );
    } catch ( Throwable $e ) {
        $review_header = null;
    }
}

$items = array();
$meta  = array( 'total' => 0, 'total_pages' => 1, 'current_page' => 1 );
if ( class_exists( 'AH_Reviews_Model' ) ) {
    try {
        $result = ( new AH_Reviews_Model() )->get_paginated( $paged, '', 'active' );
        $items  = isset( $result['items'] ) ? (array) $result['items'] : array();
        $meta   = isset( $result['meta'] )  ? (array) $result['meta']  : $meta;
    } catch ( Throwable $e ) {
        $items = array();
    }
}

$_header_heading     = is_object( $review_header ) && ! empty( $review_header->heading )     ? (string) $review_header->heading     : PAGE_TITLE_REVIEWS;
$_header_description = is_object( $review_header ) && ! empty( $review_header->description ) ? (string) $review_header->description : '';

// ── Page chrome ────────────────────────────────────────────────────────────
$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

$ctx = array(
    'meta' => array(
        'title'       => PAGE_TITLE_REVIEWS,
        'description' => $_header_description,
    ),
    'breadcrumb' => array(
        array( 'label' => adn_term( 'page_titles.home', 'Home' ), 'url' => home_url( '/' ) ),
        array( 'label' => PAGE_TITLE_REVIEWS, 'url' => home_url( SITE_REVIEWS_URL ) ),
    ),
    'hero' => array(
        'title'       => $_header_heading,
        'description' => $_header_description,
    ),
    'chrome' => $chrome,
);

// ── SEO ─────────────────────────────────────────────────────────────────────
adn_seo_register( array(
    'title'       => (string) $ctx['meta']['title'],
    'description' => wp_strip_all_tags( (string) $ctx['meta']['description'] ),
    'canonical'   => defined( 'SITE_REVIEWS_URL' ) ? home_url( SITE_REVIEWS_URL ) : '',
    'breadcrumb'  => $ctx['breadcrumb'],
) );

adn_page_open( $ctx );
?>

<?php adn_component( 'sections/page_hero', array( 'hero' => $ctx['hero'], 'breadcrumb' => $ctx['breadcrumb'] ) ); ?>

<div class="section-reviews">
<div class="reviews-page-layout">

    <?php if ( empty( $items ) ) : ?>
        <p class="muted"><?php esc_html_e( 'No reviews available yet. Please check back soon.', ADN_TEXT_DOMAIN ); ?></p>
    <?php else : ?>
        <?php
        // Mini Card reviews tile together in a compact multi-column grid; every
        // other layout (Big Box / With Photos / Full Story) is 100% width by
        // design, so each one gets its own full-width row instead of being
        // squeezed into a grid cell. Consecutive same-kind reviews share one
        // wrapper; a type change closes the previous wrapper and opens the next.
        $_open_group = null;
        foreach ( $items as $_review ) :
            $_group = ( 'mini_card' === ( $_review->representing ?? '' ) ) ? 'mini' : 'full';
            if ( $_group !== $_open_group ) :
                if ( $_open_group ) : ?></div><?php endif; ?>
                <div class="<?php echo 'mini' === $_group ? 'reviews-grid' : 'reviews-grid-full'; ?>">
                <?php $_open_group = $_group;
            endif; ?>
            <div class="reviews-grid__item">
                <?php echo AH_Reviews_Model::render_review( $_review ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_review() already escapes every field internally. ?>
            </div>
        <?php endforeach; ?>
        <?php if ( $_open_group ) : ?></div><?php endif; ?>

        <?php
        $_current = isset( $meta['current_page'] ) ? (int) $meta['current_page'] : 1;
        $_total   = isset( $meta['total_pages'] )  ? (int) $meta['total_pages']  : 1;
        ?>
        <?php if ( $_total > 1 ) : ?>
            <nav class="pagination reviews-pagination" role="navigation" aria-label="<?php echo esc_attr__( 'Pagination', ADN_TEXT_DOMAIN ); ?>">
                <?php for ( $p = 1; $p <= $_total; $p++ ) : ?>
                    <a
                        class="page-btn<?php echo $p === $_current ? ' active' : ''; ?>"
                        <?php echo $p === $_current ? 'aria-current="page"' : ''; ?>
                        href="<?php echo esc_url( add_query_arg( 'paged', $p, home_url( SITE_REVIEWS_URL ) ) ); ?>"
                    ><?php echo esc_html( (string) $p ); ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>

</div><!-- /.reviews-page-layout -->
</div><!-- /.section-reviews -->

<?php
adn_page_close( $ctx );

get_footer();
