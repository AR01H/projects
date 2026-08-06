<?php
/**
 * Template Name: Reviews
 *
 * pages/PageReviews.php - Listing of active client reviews from
 * AH_Reviews_Model, organised into one section per card design (Spotlight /
 * Property Deck / Full Story / Case Study / With Photos / Client Reviews /
 * Quick Feedback) instead of a single interleaved list - so each design
 * reads as its own showcase rather than being scattered wherever an admin's
 * sort_order happened to place it. Section order/headings: see
 * REVIEW_SECTIONS below. Spotlight and Property Deck render via their own
 * section components (a synced slider/fan-carousel needs the whole group of
 * reviews at once); every other section loops AH_Reviews_Model::render_review()
 * per card, whatever layout was chosen for it in the CMS admin (Representing
 * field) - see AH_Reviews_Model::representing_variants().
 */

defined( 'ABSPATH' ) || exit;

/* get_header() runs after adn_seo_register() further down - adn_seo_head_output()
   is hooked to wp_head at priority 1, so registering later misses the head. */

// ── Page + reviews ────────────────────────────────────────────────────────
$page_id = get_queried_object_id();

$review_header = null;
if ( class_exists( 'AH_Reviews_Model' ) ) {
    try {
        $review_header = ( new AH_Reviews_Model() )->get_page_header( $page_id );
    } catch ( Throwable $e ) {
        $review_header = null;
    }
}

// One section per `representing` type, in display order. 'mini_card' tiles
// into a compact grid (reviews-grid); 'case_study'/'with_photos'/'big_box'
// are all fixed-size cards now (photo + short copy, not full-width text), so
// they tile into the same wider 3-col grid (reviews-grid-case); 'full_story'
// is the only type still long-form enough to need a full-width stacked row
// (reviews-grid-full). 'spotlight' and 'property_deck' are rendered by their
// own section components instead of the generic grid (the 'grid' value below
// is only used as a fallback/CSS class for them).
$REVIEW_SECTIONS = array(
    'spotlight'     => array( 'heading' => adn_term( 'reviews_page.section_spotlight',     'Client Spotlights' ),   'grid' => 'full' ),
    'property_deck' => array( 'heading' => adn_term( 'reviews_page.section_property_deck', 'Explore Our Success Stories' ), 'grid' => 'full' ),
    'full_story'    => array( 'heading' => adn_term( 'reviews_page.section_full_story',    'Full Stories' ),        'grid' => 'full' ),
    'case_study'    => array( 'heading' => adn_term( 'reviews_page.section_case_study',    'Case Studies' ),        'grid' => 'case' ),
    'with_photos'   => array( 'heading' => adn_term( 'reviews_page.section_with_photos',   'Reviews with Photos' ), 'grid' => 'full' ),
    'big_box'       => array( 'heading' => adn_term( 'reviews_page.section_standard',      'Client Reviews' ),      'grid' => 'case' ),
    'mini_card'     => array( 'heading' => adn_term( 'reviews_page.section_quick',         'Quick Feedback' ),      'grid' => 'mini' ),
);

$grouped = array_fill_keys( array_keys( $REVIEW_SECTIONS ), array() );
$items   = array();
if ( class_exists( 'AH_Reviews_Model' ) ) {
    try {
        $items = ( new AH_Reviews_Model() )->all( array(
            'where'    => "status = 'active'",
            'order_by' => 'sort_order',
            'order'    => 'ASC',
        ) );
        foreach ( $items as $_review ) {
            $_type = (string) ( $_review->representing ?? '' );
            if ( isset( $grouped[ $_type ] ) ) {
                $grouped[ $_type ][] = $_review;
            } else {
                // Unrecognised/empty representing value - render_review() itself
                // already falls back to Big Box for these, so group them there too.
                $grouped['big_box'][] = $_review;
            }
        }
    } catch ( Throwable $e ) {
        $items = array();
    }
}

$_header_heading     = is_object( $review_header ) && ! empty( $review_header->heading )     ? (string) $review_header->heading     : PAGE_TITLE_REVIEWS;
$_header_description = is_object( $review_header ) && ! empty( $review_header->description ) ? (string) $review_header->description : adn_term( 'reviews_page.hero_description', '' );

// ── Showcase Gallery (admin: CMS Plugin → Showcase Gallery) ─────────────────
// Reuses that feature's own "Visible" toggle (set on its Page Header tab) as
// the show/hide control here - no separate setting to keep in sync.
$showcase_heading     = '';
$showcase_description = '';
$showcase_images      = array();
$showcase_videos      = array();
if ( class_exists( 'AH_Client_Stories_Model' ) ) {
    try {
        $_showcase = ( new AH_Client_Stories_Model() )->get_reviews_page_gallery();
        $showcase_heading     = $_showcase['heading'];
        $showcase_description = $_showcase['description'];
        $showcase_images      = $_showcase['images'];
        $showcase_videos      = $_showcase['videos'];
    } catch ( Throwable $e ) {
        $showcase_images = array();
        $showcase_videos = array();
    }
}

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

get_header(); // Loads wp_head() which triggers wp_enqueue_scripts hook

adn_page_open( $ctx );
?>

<?php adn_component( 'sections/page_hero', array( 'hero' => $ctx['hero'], 'breadcrumb' => $ctx['breadcrumb'] ) ); ?>

<div class="section-reviews">
<div class="reviews-page-layout">

    <?php if ( empty( $items ) ) : ?>
        <p class="muted"><?php esc_html_e( 'No reviews available yet. Please check back soon.', ADN_TEXT_DOMAIN ); ?></p>
    <?php else : ?>
        <?php foreach ( $REVIEW_SECTIONS as $_type => $_section ) :
            $_reviews = $grouped[ $_type ];
            if ( empty( $_reviews ) ) continue; // no empty section headers
            $_grid_class = array( 'mini' => 'reviews-grid', 'case' => 'reviews-grid-case' )[ $_section['grid'] ] ?? 'reviews-grid-full';
        ?>
            <section class="reviews-section reviews-section--<?php echo esc_attr( $_type ); ?>">
                <?php if ( 'spotlight' === $_type ) : ?>
                    <?php adn_component( 'sections/reviews_spotlight_slider', array(
                        'reviews' => $_reviews,
                        'heading' => $_section['heading'],
                    ) ); ?>
                <?php elseif ( 'property_deck' === $_type ) : ?>
                    <?php adn_component( 'sections/reviews_property_deck', array(
                        'reviews' => $_reviews,
                        'heading' => $_section['heading'],
                    ) ); ?>
                <?php else : ?>
                    <?php adn_component( 'parts/section_headers/section_header', array(
                        'heading' => array( 'title' => $_section['heading'], 'link_label' => '', 'link_url' => '' ),
                        'tag'     => 'h2',
                    ) ); ?>
                    <div class="<?php echo esc_attr( $_grid_class ); ?>">
                        <?php foreach ( $_reviews as $_review ) : ?>
                            <div class="reviews-grid__item">
                                <?php echo AH_Reviews_Model::render_review( $_review ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_review() already escapes every field internally. ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ( ! empty( $showcase_images ) || ! empty( $showcase_videos ) ) : ?>
        <section class="reviews-section reviews-section--showcase-gallery">
            <?php adn_component( 'sections/showcase_gallery', array(
                'heading'     => $showcase_heading,
                'description' => $showcase_description,
                'images'      => $showcase_images,
                'videos'      => $showcase_videos,
            ) ); ?>
        </section>
    <?php endif; ?>

</div><!-- /.reviews-page-layout -->
</div><!-- /.section-reviews -->

<?php
adn_page_close( $ctx );

get_footer();
