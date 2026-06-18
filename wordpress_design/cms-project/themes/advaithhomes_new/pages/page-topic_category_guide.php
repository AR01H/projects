<?php
/**
 * pages/page-topic_category_guide.php
 *
 * Topic/category listing page - articles within one taxonomy term.
 *
 * Layout:
 *   1. Hero (term name, parent label, description)
 *   2. page-with-sidebar:
 *      Main - article grid (guide_listing_card) + pagination
 *      Sidebar - buying topics, calculators quick tools, expert help
 *   3. Related categories carousel (full-width)
 *   4. Help CTA banner
 *
 * Routed from includes/core_routing.php; query var `adn_guide_term_slug` carries the slug.
 * RULE: No hardcoded content and no data reads here - only structure.
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_topic_category_logical.php';
$ctx = adn_topic_category_get_context();

$term   = $ctx['term'];
$parent = $ctx['parent'];

$term_name = $term ? (string) $term->name : '';

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== HERO (breadcrumb renders inside) ============================== */ ?>
<?php adn_component( 'sections/page_hero', array(
	'hero'       => $ctx['hero'],
	'breadcrumb' => $ctx['breadcrumb'],
) ); ?>

<?php /* ============================== MAIN + SIDEBAR ============================== */ ?>
<div class="container">
    <div class="page-with-sidebar topic-listing-layout">

        <main class="topic-listing-main">

            <?php if ( ! empty( $ctx['articles'] ) ) : ?>

                <?php
                adn_component( 'parts/section_headers/section_header', array(
                    'heading' => array(
                        'title'      => $term_name . ' Guides',
                        'link_label' => '',
                        'link_url'   => '',
                    ),
                    'tag' => 'h2',
                ) );
                ?>

                <div class="topic-articles-grid">
                    <?php foreach ( $ctx['articles'] as $article ) : ?>
                        <?php adn_component( 'cards/guide_listing_card', array( 'item' => $article ) ); ?>
                    <?php endforeach; ?>
                </div>

                <?php /* ── Pagination ── */ ?>
                <?php
                $_pag = $ctx['pagination'];
                $_cur = isset( $_pag['current'] ) ? (int) $_pag['current'] : 1;
                $_tot = isset( $_pag['total'] )   ? (int) $_pag['total']   : 1;
                $_base = isset( $_pag['base_url'] ) ? trailingslashit( $_pag['base_url'] ) : '';
                if ( $_tot > 1 ) :
                    $links = paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%', $_base ),
                        'format'    => '',
                        'current'   => $_cur,
                        'total'     => $_tot,
                        'prev_text' => '&laquo; Previous',
                        'next_text' => 'Next &raquo;',
                        'type'      => 'array',
                        'end_size'  => 2,
                        'mid_size'  => 1,
                    ) );
                    if ( ! empty( $links ) ) :
                ?>
                <nav class="topic-pagination" aria-label="<?php esc_attr_e( 'Page navigation', ADN_TEXT_DOMAIN ); ?>">
                    <?php foreach ( $links as $link ) : ?>
                        <?php echo wp_kses( $link, array( 'a' => array( 'href' => true, 'class' => true, 'aria-current' => true ), 'span' => array( 'class' => true, 'aria-current' => true ) ) ); ?>
                    <?php endforeach; ?>
                </nav>
                <?php endif; endif; ?>

            <?php else : ?>
                <p class="cat-guide-empty"><?php esc_html_e( 'No guides found for this topic yet. Check back soon.', 'advaithhomes' ); ?></p>
            <?php endif; ?>

        </main>

        <aside class="sidebar-col topic-listing-sidebar">

            <?php /* ── Explore buying topics ── */ ?>
            <?php if ( ! empty( $ctx['sidebar']['buying_topics'] ) ) :
                $bt = $ctx['sidebar']['buying_topics'];
            ?>
            <div class="sidebar-card topic-topics-card">
                <?php if ( ! empty( $bt['heading'] ) ) : ?>
                    <div class="sidebar-card-title"><?php echo esc_html( $bt['heading'] ); ?></div>
                <?php endif; ?>
                <?php foreach ( (array) $bt['items'] as $titem ) : ?>
                    <a href="<?php echo esc_url( adn_link( isset( $titem['url'] ) ? $titem['url'] : '' ) ); ?>"
                       class="sidebar-link-item topic-topic-link<?php echo ! empty( $titem['is_active'] ) ? ' topic-topic-link--active' : ''; ?>">
                        <div>
                            <?php if ( ! empty( $titem['icon'] ) ) : ?>
                                <span class="sidebar-link-icon"><?php echo esc_html( $titem['icon'] ); ?></span>
                            <?php endif; ?>
                            <?php echo esc_html( isset( $titem['label'] ) ? $titem['label'] : '' ); ?>
                        </div>
                        <span class="sidebar-chevron">&rsaquo;</span>
                    </a>
                <?php endforeach; ?>
                <?php if ( ! empty( $bt['view_all']['label'] ) ) : ?>
                    <a href="<?php echo esc_url( adn_link( $bt['view_all']['url'] ) ); ?>" class="view-all-small">
                        <?php echo esc_html( $bt['view_all']['label'] ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php /* ── Quick tools / calculators ── */ ?>
            <?php if ( ! empty( $ctx['sidebar']['quick_tools'] ) ) : ?>
                <?php adn_component( 'parts/sidebar_quick_tools', array( 'quick_tools' => $ctx['sidebar']['quick_tools'] ) ); ?>
            <?php endif; ?>

            <?php /* ── Expert help ── */ ?>
            <?php if ( ! empty( $ctx['sidebar']['expert_help'] ) ) : ?>
                <?php adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $ctx['sidebar']['expert_help'] ) ); ?>
            <?php endif; ?>

        </aside>

    </div>
</div>

<?php /* ============================== MORE TOPICS (full-width) ============================== */ ?>
<?php if ( ! empty( $ctx['related_categories'] ) ) : ?>
<section class="cat-guide-related-section">
    <div class="container">
        <?php
        adn_component( 'parts/section_headers/section_header', array(
            'heading' => array(
                'title'      => 'More ' . ( $parent ? esc_html( $parent->name ) : '' ) . ' Topics',
                'link_label' => $parent ? 'View all ' . esc_html( $parent->name ) . ' guides →' : '',
                'link_url'   => $parent ? home_url( '/' . trim( $parent->slug, '/' ) . '/' ) : '',
            ),
            'tag' => 'h2',
        ) );
        ?>
        <?php adn_component( 'sections/guides', array( 'items' => $ctx['related_categories'] ) ); ?>
    </div>
</section>
<?php endif; ?>

<?php /* ============================== TOOLS (full-width) ============================== */ ?>
<?php if ( ! empty( $ctx['calculators']['items'] ) ) : ?>
<section class="cat-guide-tools-section">
    <div class="container">
        <?php
        adn_component( 'parts/section_headers/section_header', array(
            'heading' => $ctx['calculators']['heading'],
            'tag'     => 'h2',
        ) );
        ?>
        <div class="tool-grid tool-grid--7col">
            <?php foreach ( $ctx['calculators']['items'] as $card ) : ?>
                <?php adn_component( 'cards/tool_card', array( 'card' => $card ) ); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php /* ============================== HELP CTA ============================== */ ?>
<?php if ( ! empty( $ctx['cta_help']['title'] ) ) : ?>
<div class="">
    <?php adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_help'] ) ); ?>
</div>
<?php endif; ?>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
