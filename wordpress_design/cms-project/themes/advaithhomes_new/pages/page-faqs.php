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
// Contact for Help - FAQs excluded (whole page is already FAQs)
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

        <?php if ( empty( $faqs ) ) : ?>
            <p class="muted"><?php esc_html_e( 'No FAQs available yet. Please check back soon.', ADN_TEXT_DOMAIN ); ?></p>
        <?php else : ?>
            <?php adn_component( 'parts/faq_list', array( 'faqs' => $faqs ) ); ?>
        <?php endif; ?>


    </div><!-- /.faqs-main -->

    <?php /* ── Sidebar ── */ ?>
    <div class="faqs-sidebar">

        <?php /* 1. Contact for Help */ ?>
        <?php adn_component( 'parts/contact_sidebar', array( 'page_sidebar' => $page_sidebar ) ); ?>

        <?php /* 2. Latest News - reuse sidebar_news_mini */ ?>
        <?php if ( ! empty( $sb_news ) ) :
            $_sb_news_items = array();
            foreach ( $sb_news as $_i => $_n ) {
                $_n_id     = is_object( $_n ) ? (int)    ( $_n->id       ?? 0  ) : (int)    ( $_n['id']       ?? 0  );
                $_n_title  = is_object( $_n ) ? (string) ( $_n->text     ?? '' ) : (string) ( $_n['text']     ?? '' );
                $_n_href   = is_object( $_n ) ? (string) ( $_n->link_url ?? '' ) : (string) ( $_n['link_url'] ?? '' );
                if ( '' === trim( $_n_title ) ) { continue; }
                $_sb_news_items[] = array(
                    'title'    => $_n_title,
                    'date'     => '',
                    'tag'      => 'NEWS',
                    'gradient' => function_exists( 'adn_cms_gradient' ) ? adn_cms_gradient( $_i ) : '',
                    'url'      => '' !== trim( $_n_href ) ? $_n_href : ( $_n_id > 0 && function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $_n_id ) : SITE_NEWS_URL ),
                );
            }
            if ( ! empty( $_sb_news_items ) ) :
                adn_component( 'parts/sidebar_news_mini', array( 'news_mini' => array(
                    'heading'  => SITE_LABEL_LATEST_NEWS,
                    'items'    => $_sb_news_items,
                    'view_all' => array( 'label' => defined( 'CONTENT_VIEW_ALL_NEWS' ) ? CONTENT_VIEW_ALL_NEWS : 'All News →', 'url' => SITE_NEWS_URL ),
                ) ) );
            endif;
        endif; ?>

        <?php /* 3. Hot Topics - reuse sidebar_guide_parents */ ?>
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

<?php $_faq_news_items = function_exists( 'adn_shared_latest_news_items' ) ? adn_shared_latest_news_items( 3 ) : array(); ?>
<?php if ( ! empty( $_faq_news_items ) ) : ?>
<section class="page-latest-news">
	<div class="container">
		<?php adn_component( 'parts/news_widget', array( 'widget' => array(
			'heading' => array(
				'title'      => adn_term( 'labels.latest_news', 'Latest News' ),
				'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
				'link_url'   => defined( 'SITE_NEWS_URL' ) ? SITE_NEWS_URL : '/',
			),
			'items' => $_faq_news_items,
		) ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php adn_page_close( $ctx );
