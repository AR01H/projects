<?php
/**
 * pages/page-sample.php - Page Template Sample
 *
 * Template Name: Sample Archive Page
 *
 * PURPOSE: Shows how a page template ties the architecture together:
 *   1. Fetch data via npt_fetch_*() from includes/data_fetcher/
 *   2. Load components via npt_component() from components/
 *   3. Output clean, escaped HTML
 *
 * RULE: Page templates fetch and render. No business logic here.
 *       All queries live in data_fetcher/. All markup in components/.
 */

defined( 'ABSPATH' ) || exit;

get_header();

// ── 1. Fetch Data ─────────────────────────────────────────────────
$category_slug = get_query_var( 'portfolio_category' ) ?: null;
$current_page  = max( 1, get_query_var( 'paged' ) );

$portfolio_data = npt_fetch_portfolios( $category_slug, NPT_POSTS_PER_PAGE );
$categories     = npt_fetch_terms( NPT_TAX_PORTFOLIO );

$items = $portfolio_data['items'];
$total = $portfolio_data['total'];
$pages = $portfolio_data['pages'];

// ── 1b. Single-post redirect check (if this is a singular view) ───
// Uncomment and adjust when loading a single portfolio item.
// $single = npt_fetch_single( get_the_ID(), 'portfolio' );
// if ( $single ) npt_maybe_redirect( $single, 'template' );


// ── 2. Render ─────────────────────────────────────────────────────
?>

<main id="main-content" class="npt-page npt-page--archive">

    <header class="npt-page__header">
        <h1 class="npt-page__title">Portfolio</h1>
        <p class="npt-page__desc">
            <?php printf( '%d projects', $total ); ?>
        </p>
    </header>

    <?php /* ── Category filter tabs ── */ ?>
    <?php if ( $categories ) : ?>
    <nav class="npt-tabs" aria-label="Portfolio categories">
        <a href="<?php echo esc_url( get_post_type_archive_link( 'portfolio' ) ); ?>"
           class="npt-tabs__item <?php echo ! $category_slug ? 'is-active' : ''; ?>">
            All
        </a>
        <?php foreach ( $categories as $cat ) : ?>
        <a href="<?php echo esc_url( $cat['url'] ); ?>"
           class="npt-tabs__item <?php echo $category_slug === $cat['slug'] ? 'is-active' : ''; ?>">
            <?php echo esc_html( $cat['name'] ); ?>
            <span class="npt-tabs__count">(<?php echo esc_html( $cat['count'] ); ?>)</span>
        </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <?php /* ── Card Grid ── */ ?>
    <?php if ( $items ) : ?>
    <div class="npt-grid npt-grid--3col">
        <?php foreach ( $items as $item ) : ?>
            <?php npt_component( 'cards/page-sample', [ 'post' => $item ] ); ?>
        <?php endforeach; ?>
    </div>

    <?php /* ── Pagination ── */ ?>
    <?php if ( $pages > 1 ) : ?>
    <nav class="npt-pagination" aria-label="Page navigation">
        <?php
        echo paginate_links( [
            'total'   => $pages,
            'current' => $current_page,
            'type'    => 'list',
        ] );
        ?>
    </nav>
    <?php endif; ?>

    <?php else : ?>
    <p class="npt-empty">No portfolio items found.</p>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
