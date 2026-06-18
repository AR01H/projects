<?php
/**
 * Template Name: FAQs
 *
 * pages/page-faqs.php - Renders FAQs from the CMS plugin (AH_Faqs_Model) or
 * falls back to the theme JSON service when the plugin isn't available.
 */

defined( 'ABSPATH' ) || exit;

// Ensure page-specific assets always load
wp_enqueue_style( 'adn-page-faqs-style',    get_template_directory_uri() . '/assets/css/faqs.css',    array(), ADN_THEME_VERSION );
wp_enqueue_style( 'adn-page-contact-style', get_template_directory_uri() . '/assets/css/contact.css', array(), ADN_THEME_VERSION );
wp_enqueue_script( 'adn-page-faqs-script',  get_template_directory_uri() . '/assets/js/faqs.js',      array(), ADN_THEME_VERSION, true );

// ── FAQ data ───────────────────────────────────────────────────────────────
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
}

// ── Sidebar data ──────────────────────────────────────────────────────────
// Contact for Help — FAQs excluded (whole page is already FAQs)
$page_sidebar = function_exists( 'adn_get_page_sidebar_data' ) ? adn_get_page_sidebar_data( 0 ) : array();
unset( $page_sidebar['faqs'] );

// News Bar items (from ah-news-bar admin)
$sb_news   = function_exists( 'adn_cms_newsbar_items' ) ? adn_cms_newsbar_items( 4 ) : array();

// Hot Topics (guide parent terms)
$sb_topics = function_exists( 'adn_cms_guide_parents' ) ? adn_cms_guide_parents( 6 ) : array();

// ── Page chrome ────────────────────────────────────────────────────────────
$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

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
    'chrome' => $chrome,
);

adn_page_open( $ctx );
?>

<?php /* Hero - full-width */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
    <?php adn_component( 'sections/page_hero', array( 'hero' => $ctx['hero'], 'breadcrumb' => $ctx['breadcrumb'] ) ); ?>
<?php endif; ?>

<div class="section-faqs">
<div class="faqs-page-layout">

    <?php /* ── Main: FAQ list ── */ ?>
    <div class="faqs-main">

        <?php if ( empty( $ctx['hero'] ) ) : ?>
            <header class="faqs-header">
                <h1><?php echo esc_html( PAGE_TITLE_FAQS ); ?></h1>
                <?php if ( is_object( $faq_header ) && ! empty( $faq_header->description ) ) : ?>
                    <p class="faqs-description"><?php echo esc_html( $faq_header->description ); ?></p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <?php if ( empty( $faqs ) ) : ?>
            <p class="muted"><?php esc_html_e( 'No FAQs available yet. Please check back soon.', ADN_TEXT_DOMAIN ); ?></p>
        <?php else : ?>
            <div class="faqs-list">
                <?php foreach ( $faqs as $faq ) :
                    $id        = is_object( $faq ) ? (int) ( $faq->id        ?? 0 ) : (int) ( $faq['id']        ?? 0 );
                    $q         = is_object( $faq ) ? (string) ( $faq->question ?? '' ) : (string) ( $faq['question'] ?? '' );
                    $a         = is_object( $faq ) ? (string) ( $faq->answer   ?? '' ) : (string) ( $faq['answer']   ?? '' );
                    $link_url  = is_object( $faq ) ? (string) ( $faq->link_url  ?? '' ) : (string) ( $faq['link_url']  ?? '' );
                    $link_text = is_object( $faq ) ? (string) ( $faq->link_text ?? '' ) : (string) ( $faq['link_text'] ?? '' );
                    if ( '' === trim( $q ) ) { continue; }

                ?>
                    <details class="faq-item">
                        <summary class="faq-q">
                            <span class="faq-q-text"><?php echo esc_html( $q ); ?></span>
                        </summary>
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


    </div><!-- /.faqs-main -->

    <?php /* ── Sidebar ── */ ?>
    <div class="faqs-sidebar">

        <?php /* 1. Contact for Help — existing reusable component */ ?>
        <?php adn_component( 'parts/contact_sidebar', array( 'page_sidebar' => $page_sidebar ) ); ?>

        <?php /* 2. Latest News */ ?>
        <?php if ( ! empty( $sb_news ) ) : ?>
        <div class="mini_card_container_design">
            <h3 class="faqs-sb-heading"><?php echo esc_html( SITE_LABEL_LATEST_NEWS ); ?></h3>
            <div class="faqs-sb-news-list">
                <?php foreach ( $sb_news as $_n ) :
                    $_n_id      = is_object( $_n ) ? (int)    ( $_n->id          ?? 0  ) : (int)    ( $_n['id']          ?? 0  );
                    $_n_title   = is_object( $_n ) ? (string) ( $_n->text        ?? '' ) : (string) ( $_n['text']        ?? '' );
                    $_n_desc    = is_object( $_n ) ? (string) ( $_n->content     ?? '' ) : (string) ( $_n['content']     ?? '' );
                    $_n_href    = is_object( $_n ) ? (string) ( $_n->link_url    ?? '' ) : (string) ( $_n['link_url']    ?? '' );
                    $_n_target  = is_object( $_n ) ? (string) ( $_n->link_target ?? '' ) : (string) ( $_n['link_target'] ?? '' );
                    if ( '' === trim( $_n_title ) ) { continue; }
                    $_n_url     = '' !== trim( $_n_href ) ? $_n_href : ( $_n_id > 0 ? adn_newsbar_item_url( $_n_id ) : home_url( SITE_NEWS_URL ) );
                    $_n_target  = in_array( $_n_target, array( '_blank', '_self' ), true ) ? $_n_target : '_self';
                ?>
                    <a href="<?php echo esc_url( $_n_url ); ?>" target="<?php echo esc_attr( $_n_target ); ?>"
                       class=" faqs-sb-news-card"
                       <?php if ( '_blank' === $_n_target ) : ?>rel="noopener noreferrer"<?php endif; ?>>
                        <span class="card-title-highlight"><?php echo esc_html( $_n_title ); ?></span>
                        <?php if ( '' !== trim( $_n_desc ) ) : ?>
                            <span class="card-desc-text"><?php echo esc_html( wp_trim_words( $_n_desc, 12, '…' ) ); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo esc_url( home_url( SITE_NEWS_URL ) ); ?>" class="read-more faqs-sb-readmore">
                <?php printf( esc_html__( 'All %s', ADN_TEXT_DOMAIN ), esc_html( SITE_NEWS_NOUN ) ); ?> <span aria-hidden="true">&#8594;</span>
            </a>
        </div>
        <?php endif; ?>

        <?php /* 3. Hot Topics — guide parent terms */ ?>
        <?php if ( ! empty( $sb_topics ) ) : ?>
        <div class="mini_card_container_design">
            <h3 class="faqs-sb-heading"><?php esc_html_e( 'Hot Topics', ADN_TEXT_DOMAIN ); ?></h3>
            <ul class="faqs-sb-topics-list">
                <?php foreach ( $sb_topics as $_t ) :
                    $_t_name = is_object( $_t ) ? (string) ( $_t->name       ?? '' ) : (string) ( $_t['name']       ?? '' );
                    $_t_slug = is_object( $_t ) ? (string) ( $_t->slug       ?? '' ) : (string) ( $_t['slug']       ?? '' );
                    $_t_icon = is_object( $_t ) ? (string) ( $_t->icon_emoji ?? '' ) : (string) ( $_t['icon_emoji'] ?? '' );
                    if ( '' === trim( $_t_name ) || '' === trim( $_t_slug ) ) { continue; }
                ?>
                    <li>
                        <a href="<?php echo esc_url( home_url( '/' . $_t_slug . '/' ) ); ?>" class="faqs-sb-topic-link">
                            <?php if ( '' !== $_t_icon ) : ?>
                                <span class="faqs-sb-topic-icon" aria-hidden="true"><?php echo adn_icon( $_t_icon ); ?></span>
                            <?php endif; ?>
                            <span class="faqs-sb-topic-name"><?php echo esc_html( $_t_name ); ?></span>
                            <span class="faqs-sb-topic-arrow" aria-hidden="true">&#8594;</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

    </div><!-- /.faqs-sidebar -->

</div><!-- /.faqs-page-layout -->
</div><!-- /.section-faqs -->

<?php adn_page_close( $ctx );
