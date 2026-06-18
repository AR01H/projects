<?php
/**
 * Template Name: FAQs
 *
 * pages/page-faqs.php - Renders FAQs from the CMS plugin (AH_Faqs_Model) or
 * falls back to the theme JSON service when the plugin isn't available.
 */

defined( 'ABSPATH' ) || exit;

// Collect data
$faqs       = array();
$page_id    = get_queried_object_id();
$faq_header = null;

if ( class_exists( 'AH_Faqs_Model' ) ) {
    try {
        $model = new AH_Faqs_Model();
        $faqs  = $model->get_for_page( $page_id );
        if ( empty( $faqs ) ) {
            $faqs = $model->get_global();
        }
        $faq_header = $model->get_faq_header( $page_id );
    } catch ( Throwable $e ) {
        $faqs = array();
    }
} else {
    // If CMS plugin isn't active we intentionally return no FAQs (DB-only policy).
    $faqs = array();
}

// Gather chrome (header/footer) so adn_page_close() can render footers.
$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

// Build a full page context so the header, footer and CTA render correctly.
$ctx = array(
    'meta' => array(
        'title'       => PAGE_TITLE_FAQS,
        'description' => is_object( $faq_header ) && ! empty( $faq_header->description ) ? (string) $faq_header->description : '',
    ),
    'breadcrumb' => array(
        array( 'label' => adn_term( 'page_titles.home', 'Home' ), 'url' => home_url( '/' ) ),
        array( 'label' => PAGE_TITLE_FAQS, 'url' => home_url( SITE_FAQS_URL ) ),
    ),
    'hero' => array(
        'title'       => PAGE_TITLE_FAQS,
        'description' => is_object( $faq_header ) && ! empty( $faq_header->description ) ? (string) $faq_header->description : '',
    ),
    // Provide chrome so adn_page_open()/adn_page_close() can render header/footer.
    'chrome' => $chrome,
    // Small CTA banner below content to match other pages.
    'cta_banner' => array(
        'icon'        => adn_term( 'icons.enquiry', '💬' ),
        'title'       => adn_term( 'content.need_help_title', 'Need Help With' ),
        'description' => adn_term( 'content.need_help_description', 'Speak to one of our experts and get personalised guidance.' ),
        'cta'         => array( 'label' => adn_term( 'content.need_help_cta', 'Talk to an Expert' ), 'url' => home_url( SITE_CONTACT_URL ) ),
        'trust_items' => array(),
    ),
);

adn_page_open( $ctx );
?>

<section class="section-faqs container">
    <?php if ( ! empty( $ctx['hero'] ) ) : ?>
        <?php adn_component( 'sections/page_hero', array( 'hero' => $ctx['hero'], 'breadcrumb' => $ctx['breadcrumb'] ) ); ?>
    <?php else : ?>
        <header class="faqs-header">
            <h1><?php echo esc_html( PAGE_TITLE_FAQS ); ?></h1>
            <?php if ( is_object( $faq_header ) && ! empty( $faq_header->description ) ) : ?>
                <p class="faqs-description"><?php echo esc_html( $faq_header->description ); ?></p>
            <?php endif; ?>
        </header>
    <?php endif; ?>

    <?php
    // Show CTA directly under the hero so it's visible above the FAQ list.
    if ( ! empty( $ctx['cta_banner'] ) ) {
        echo '<div class="container">';
        adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) );
        echo '</div>';
    }

    // Group FAQs by section if available (field 'section' on rows or attached taxonomy terms).
    $grouped = array();
    $use_groups = false;
    $ctm = class_exists( 'AH_Content_Taxonomy_Model' ) ? new AH_Content_Taxonomy_Model() : null;
    foreach ( $faqs as $faq ) {
        $id = is_object( $faq ) ? (int) ( $faq->id ?? 0 ) : (int) ( $faq['id'] ?? 0 );
        $section = '';
        if ( is_object( $faq ) && ! empty( $faq->section ) ) {
            $section = trim( (string) $faq->section );
        } elseif ( is_array( $faq ) && ! empty( $faq['section'] ) ) {
            $section = trim( (string) $faq['section'] );
        }
        if ( '' === $section && $ctm && $id ) {
            $terms = $ctm->get_terms( 'faq', $id );
            if ( ! empty( $terms ) ) {
                $found = null;
                foreach ( $terms as $t ) {
                    if ( ! empty( $t->type_slug ) && ( false !== strpos( $t->type_slug, 'section' ) || false !== strpos( $t->type_slug, 'faq' ) ) ) { $found = $t; break; }
                }
                $found = $found ?: $terms[0];
                $section = trim( (string) ( $found->name ?? '' ) );
            }
        }
        $section = $section ?: 'General';
        if ( 'General' !== $section ) { $use_groups = true; }
        $grouped[ $section ][] = $faq;
    }

    if ( empty( $faqs ) ) : ?>
        <p class="muted"><?php esc_html_e( 'No FAQs available yet. Please check back soon.', ADN_TEXT_DOMAIN ); ?></p>
    <?php else : ?>
        <?php if ( $use_groups ) : ?>
            <div class="faqs-list">
                <?php foreach ( $grouped as $topic => $items ) : ?>
                    <div class="faqs-section">
                        <h3><?php echo esc_html( $topic ); ?></h3>
                        <?php foreach ( $items as $faq ) :
                            $q = is_object( $faq ) ? (string) ( $faq->question ?? '' ) : (string) ( $faq['question'] ?? '' );
                            $a = is_object( $faq ) ? (string) ( $faq->answer ?? '' )   : (string) ( $faq['answer'] ?? '' );
                            $link_url  = is_object( $faq ) ? (string) ( $faq->link_url ?? '' )  : (string) ( $faq['link_url'] ?? '' );
                            $link_text = is_object( $faq ) ? (string) ( $faq->link_text ?? '' ) : (string) ( $faq['link_text'] ?? '' );
                            if ( '' === trim( $q ) ) { continue; }
                        ?>
                            <details class="faq-item">
                                <summary class="faq-q"><?php echo esc_html( $q ); ?></summary>
                                <div class="faq-a">
                                    <?php if ( '' !== trim( $a ) ) : ?>
                                        <div class="faq-a-body"><?php echo wp_kses_post( wpautop( wp_trim_words( $a, 500, '' ) ) ); ?></div>
                                    <?php endif; ?>
                                    <?php if ( '' !== trim( $link_url ) ) : ?>
                                        <p class="faq-link"><a href="<?php echo esc_url( adn_link( $link_url ) ); ?>"><?php echo esc_html( $link_text ?: $link_url ); ?></a></p>
                                    <?php endif; ?>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="faqs-list">
                <?php foreach ( $faqs as $faq ) :
                    $q = is_object( $faq ) ? (string) ( $faq->question ?? '' ) : (string) ( $faq['question'] ?? '' );
                    $a = is_object( $faq ) ? (string) ( $faq->answer ?? '' )   : (string) ( $faq['answer'] ?? '' );
                    $link_url  = is_object( $faq ) ? (string) ( $faq->link_url ?? '' )  : (string) ( $faq['link_url'] ?? '' );
                    $link_text = is_object( $faq ) ? (string) ( $faq->link_text ?? '' ) : (string) ( $faq['link_text'] ?? '' );
                    if ( '' === trim( $q ) ) { continue; }
                ?>
                    <details class="faq-item">
                        <summary class="faq-q"><?php echo esc_html( $q ); ?></summary>
                        <div class="faq-a">
                            <?php if ( '' !== trim( $a ) ) : ?>
                                <div class="faq-a-body"><?php echo wp_kses_post( wpautop( wp_trim_words( $a, 500, '' ) ) ); ?></div>
                            <?php endif; ?>
                            <?php if ( '' !== trim( $link_url ) ) : ?>
                                <p class="faq-link"><a href="<?php echo esc_url( adn_link( $link_url ) ); ?>"><?php echo esc_html( $link_text ?: $link_url ); ?></a></p>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- FAQ interactions moved to /assets/js/faqs.js for caching -->

    <p class="faqs-back-link"><a href="<?php echo esc_url( home_url( SITE_CONTACT_URL ) ); ?>">&larr; <?php esc_html_e( 'Back to contact', ADN_TEXT_DOMAIN ); ?></a></p>
</section>

<?php adn_page_close( $ctx );
