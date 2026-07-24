<?php
/**
 * Template Name: FAQs
 *
 * pages/PageFaqs.php - Renders FAQs from the CMS plugin (AH_Faqs_Model) or
 * falls back to the theme JSON service when the plugin isn't available.
 */

defined( 'ABSPATH' ) || exit;

get_header(); // Loads wp_head() which triggers wp_enqueue_scripts hook

// CSS/JS now loaded centrally via AssetLoader (wp_enqueue_scripts hook)

// ── FAQ data (Global only, grouped by section) ──────────────────────────────
$page_id    = get_queried_object_id();
$faq_groups = function_exists( 'adn_get_page_faqs_grouped' ) ? adn_get_page_faqs_grouped( 0 ) : array();
$faqs       = array();
foreach ( $faq_groups as $_faq_group_items ) {
    $faqs = array_merge( $faqs, $_faq_group_items );
}
unset( $_faq_group_items );

$faq_header = null;
if ( class_exists( 'AH_Faqs_Model' ) ) {
    try {
        $faq_header = ( new AH_Faqs_Model() )->get_faq_header( $page_id );
    } catch ( Throwable $e ) {
        $faq_header = null;
    }
}

// ── Sidebar data ──────────────────────────────────────────────────────────
// Contact for Help - FAQs excluded (whole page is already FAQs)
$page_sidebar = function_exists( 'adn_get_page_sidebar_data' ) ? adn_get_page_sidebar_data( 0 ) : array();
unset( $page_sidebar['faqs'] );

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

// ── SEO ─────────────────────────────────────────────────────────────────────
$_faq_seo_items = array();
foreach ( (array) $faqs as $_faq ) {
	$_fq = trim( (string) ( isset( $_faq->question ) ? $_faq->question : ( isset( $_faq['question'] ) ? $_faq['question'] : '' ) ) );
	$_fa = trim( (string) ( isset( $_faq->answer )   ? $_faq->answer   : ( isset( $_faq['answer'] )   ? $_faq['answer']   : '' ) ) );
	if ( '' !== $_fq && '' !== $_fa ) {
		$_faq_seo_items[] = array( 'question' => $_fq, 'answer' => $_fa );
	}
}
adn_seo_register( array(
	'title'       => isset( $ctx['meta']['title'] )       ? (string) $ctx['meta']['title']       : PAGE_TITLE_FAQS,
	'description' => isset( $ctx['meta']['description'] ) ? wp_strip_all_tags( (string) $ctx['meta']['description'] ) : '',
	'canonical'   => defined( 'SITE_FAQS_URL' ) ? home_url( SITE_FAQS_URL ) : '',
	'breadcrumb'  => isset( $ctx['breadcrumb'] )          ? $ctx['breadcrumb']                   : array(),
	'schema_faqs' => $_faq_seo_items,
) );

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

        <?php if ( empty( $faq_groups ) ) : ?>
            <p class="muted"><?php esc_html_e( 'No FAQs available yet. Please check back soon.', ADN_TEXT_DOMAIN ); ?></p>
        <?php else : ?>
            <?php foreach ( $faq_groups as $_faq_section_label => $_faq_section_items ) : ?>
                <?php adn_component( 'parts/faq_list', array(
                    'faqs'    => $_faq_section_items,
                    'heading' => (string) $_faq_section_label,
                ) ); ?>
            <?php endforeach; ?>
        <?php endif; ?>


    </div><!-- /.faqs-main -->

    <?php /* ── Sidebar ── */ ?>
    <div class="faqs-sidebar">

        <?php /* 1. Contact for Help */ ?>
        <?php adn_component( 'parts/contact_sidebar', array( 'page_sidebar' => $page_sidebar ) ); ?>

        <?php /* Hot Topics - reuse sidebar_guide_parents */ ?>
        <?php if ( ! empty( $sb_topics ) ) :
            $_sb_topic_items = array();
            foreach ( $sb_topics as $_t ) {
                $_t_name = is_object( $_t ) ? (string) ( $_t->name       ?? '' ) : (string) ( $_t['name']       ?? '' );
                $_t_slug = is_object( $_t ) ? (string) ( $_t->slug       ?? '' ) : (string) ( $_t['slug']       ?? '' );
                $_t_icon = is_object( $_t ) ? (string) ( $_t->icon_emoji ?? '' ) : (string) ( $_t['icon_emoji'] ?? '' );
                if ( '' === trim( $_t_name ) ) { continue; }
                $_sb_topic_items[] = array( 'icon' => $_t_icon, 'label' => $_t_name, 'url' => home_url( '/' . trim( $_t_slug, '/' ) . '/' ), 'count' => 0 );
            }
            if ( ! empty( $_sb_topic_items ) ) :
                adn_component( 'parts/sidebar_guide_parents', array( 'guide_parents' => array(
                    'heading' => defined( 'ADN_TEXT_DOMAIN' ) ? __( 'Hot Topics', ADN_TEXT_DOMAIN ) : 'Hot Topics',
                    'items'   => $_sb_topic_items,
                ) ) );
            endif;
        endif; ?>

    </div><!-- /.faqs-sidebar -->

</div><!-- /.faqs-page-layout -->
</div><!-- /.section-faqs -->

<?php adn_page_close( $ctx );

get_footer();
