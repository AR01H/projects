<?php
/**
 * components/parts/main_header.php — Component: Site Header
 *
 * Renders: logo, primary navigation (with multi-level submenus / dropdowns),
 *          a live site-search panel (WordPress core ?s= search), the header
 *          CTA and the mobile menu (with collapsible submenu accordions).
 *
 * Props:
 *   $chrome (array, from adn_service_site_chrome()):
 *     - logo       { icon, name, sub, url }
 *     - search     { placeholder, submit_label }
 *     - nav        [ { label, url, children[ { label, url } ] } ]
 *     - header_cta { label, url }
 *
 * Data source: data/json/site_chrome.json today; the same shape is produced by
 * the plugin's AH_Nav_Model::get_items_tree() (parent_id → children), so the
 * service layer can be swapped to the plugin without touching this template.
 *
 * Usage: adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) );
 */

defined( 'ABSPATH' ) || exit;

$chrome = isset( $chrome ) && is_array( $chrome ) ? $chrome : array();

$logo   = isset( $chrome['logo'] ) ? (array) $chrome['logo'] : array();
$nav    = isset( $chrome['nav'] ) ? (array) $chrome['nav'] : array();
$cta    = isset( $chrome['header_cta'] ) ? (array) $chrome['header_cta'] : array();
$search = isset( $chrome['search'] ) ? (array) $chrome['search'] : array();

$search_action      = esc_url( home_url( '/' ) );
$search_placeholder = isset( $search['placeholder'] ) ? $search['placeholder'] : 'Search…';
$search_label       = isset( $search['submit_label'] ) ? $search['submit_label'] : 'Search';
$search_value       = get_search_query();

// Live type-ahead uses the WordPress core REST search endpoint (no plugin
// needed): returns published posts/pages matching the typed query. Passed as a
// data-attribute so the JS works under both pretty and plain permalinks.
$search_suggest = function_exists( 'rest_url' ) ? esc_url( rest_url( 'wp/v2/search' ) ) : '';
?>
<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">

            <a href="<?php echo esc_url( adn_link( isset( $logo['url'] ) ? $logo['url'] : '/' ) ); ?>" class="logo">
                <div class="logo-icon"><?php echo esc_html( isset( $logo['icon'] ) ? $logo['icon'] : '' ); ?></div>
                <div class="logo-text">
                    <span class="logo-name"><?php echo esc_html( isset( $logo['name'] ) ? $logo['name'] : '' ); ?></span>
                    <span class="logo-sub"><?php echo esc_html( isset( $logo['sub'] ) ? $logo['sub'] : '' ); ?></span>
                </div>
            </a>

            <nav class="main-nav" aria-label="Main navigation">
                <?php foreach ( $nav as $item ) : ?>
                    <?php
                    $item     = (array) $item;
                    $label    = isset( $item['label'] ) ? $item['label'] : '';
                    $url      = esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) );
                    $children = isset( $item['children'] ) ? (array) $item['children'] : array();
                    ?>
                    <?php if ( ! empty( $children ) ) : ?>
                        <div class="nav-item has-dropdown">
                            <a href="<?php echo $url; ?>" class="nav-link" aria-haspopup="true" aria-expanded="false">
                                <?php echo esc_html( $label ); ?>
                                <span class="nav-caret" aria-hidden="true">▾</span>
                            </a>
                            <div class="nav-dropdown" role="menu" aria-label="<?php echo esc_attr( $label ); ?>">
                                <?php foreach ( $children as $child ) : ?>
                                    <?php $child = (array) $child; ?>
                                    <a href="<?php echo esc_url( adn_link( isset( $child['url'] ) ? $child['url'] : '' ) ); ?>"
                                       class="nav-dropdown-link" role="menuitem"><?php echo esc_html( isset( $child['label'] ) ? $child['label'] : '' ); ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <a href="<?php echo $url; ?>" class="nav-link"><?php echo esc_html( $label ); ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <div class="header-actions">
                <button type="button" class="btn-search" aria-label="Search" aria-expanded="false" aria-controls="headerSearch">🔍</button>
                <?php if ( ! empty( $cta['label'] ) ) : ?>
                    <a href="<?php echo esc_url( adn_link( isset( $cta['url'] ) ? $cta['url'] : '' ) ); ?>" class="btn btn-primary btn-sm header-cta"><?php echo esc_html( $cta['label'] ); ?></a>
                <?php endif; ?>
                <button type="button" class="mobile-menu-btn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu">☰</button>
            </div>
        </div>
    </div>

    <?php /* ---------- Site search panel (WordPress core search) ---------- */ ?>
    <div class="header-search" id="headerSearch" hidden>
        <div class="container">
            <div class="header-search-box">
                <form class="header-search-form" role="search" method="get" action="<?php echo $search_action; ?>" data-suggest="<?php echo $search_suggest; ?>">
                    <span class="header-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="s" class="header-search-input"
                           placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
                           value="<?php echo esc_attr( $search_value ); ?>"
                           aria-label="<?php echo esc_attr( $search_placeholder ); ?>"
                           autocomplete="off" role="combobox" aria-expanded="false"
                           aria-controls="headerSearchSuggest" aria-autocomplete="list">
                    <button type="submit" class="btn btn-primary btn-sm"><?php echo esc_html( $search_label ); ?></button>
                    <button type="button" class="header-search-close" aria-label="Close search">✕</button>
                </form>
                <div class="search-suggest js-suggest" id="headerSearchSuggest" role="listbox" hidden></div>
            </div>
        </div>
    </div>
</header>

<?php /* ============================== MOBILE MENU ============================== */ ?>
<div class="mobile-menu-overlay" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Mobile navigation">

    <div class="mobile-search-box">
        <form class="mobile-search-form" role="search" method="get" action="<?php echo $search_action; ?>" data-suggest="<?php echo $search_suggest; ?>">
            <input type="search" name="s" class="mobile-search-input"
                   placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
                   value="<?php echo esc_attr( $search_value ); ?>"
                   aria-label="<?php echo esc_attr( $search_placeholder ); ?>"
                   autocomplete="off" role="combobox" aria-expanded="false" aria-autocomplete="list">
            <button type="submit" class="mobile-search-btn" aria-label="<?php echo esc_attr( $search_label ); ?>">🔍</button>
        </form>
        <div class="search-suggest search-suggest--mobile js-suggest" role="listbox" hidden></div>
    </div>

    <?php foreach ( $nav as $item ) : ?>
        <?php
        $item     = (array) $item;
        $label    = isset( $item['label'] ) ? $item['label'] : '';
        $url      = esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) );
        $children = isset( $item['children'] ) ? (array) $item['children'] : array();
        ?>
        <?php if ( ! empty( $children ) ) : ?>
            <div class="mobile-nav-group">
                <button type="button" class="mobile-nav-toggle" aria-expanded="false">
                    <span><?php echo esc_html( $label ); ?></span>
                    <span class="mobile-nav-caret" aria-hidden="true">▾</span>
                </button>
                <div class="mobile-submenu" hidden>
                    <a href="<?php echo $url; ?>" class="mobile-subnav-link mobile-subnav-all"><?php echo esc_html( 'All ' . $label ); ?></a>
                    <?php foreach ( $children as $child ) : ?>
                        <?php $child = (array) $child; ?>
                        <a href="<?php echo esc_url( adn_link( isset( $child['url'] ) ? $child['url'] : '' ) ); ?>"
                           class="mobile-subnav-link"><?php echo esc_html( isset( $child['label'] ) ? $child['label'] : '' ); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <a href="<?php echo $url; ?>" class="mobile-nav-link"><?php echo esc_html( $label ); ?></a>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ( ! empty( $cta['label'] ) ) : ?>
        <div class="mobile-menu-cta">
            <a href="<?php echo esc_url( adn_link( isset( $cta['url'] ) ? $cta['url'] : '' ) ); ?>" class="btn btn-primary btn-lg"><?php echo esc_html( $cta['label'] ); ?></a>
        </div>
    <?php endif; ?>
</div>
