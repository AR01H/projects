<?php
/**
 * search.php - WordPress search results template.
 */

defined( 'ABSPATH' ) || exit;

$_chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();
$_ctx    = array( 'chrome' => $_chrome );
$_query  = get_search_query();
$_count  = (int) $GLOBALS['wp_query']->found_posts;

$_hero_desc = '';
if ( $_query ) {
	$_hero_desc = $_count
		? $_count . ' result' . ( 1 !== $_count ? 's' : '' ) . ' found'
		: 'No results found - try different keywords';
} else {
	$_hero_desc = 'Search our guides, articles and tools';
}

adn_page_open( $_ctx );

adn_component( 'sections/page_hero', array(
	'hero'       => array(
		'eyebrow'     => 'Search',
		'title'       => $_query ? 'Results for "' . $_query . '"' : 'Search',
		'description' => $_hero_desc,
	),
	'breadcrumb' => array(
		array( 'label' => 'Home', 'url' => '/' ),
		array( 'label' => 'Search' ),
	),
) );

/* ── Sidebar data ── */
$_sidebar = function_exists( 'adn_service_post_sidebar_data' ) ? adn_service_post_sidebar_data() : array();

$_sidebar_news = array();
$_nq = new WP_Query( array(
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'no_found_rows'  => true,
) );
if ( $_nq->have_posts() ) {
	while ( $_nq->have_posts() ) {
		$_nq->the_post();
		$_sidebar_news[] = array(
			'icon'  => get_post_meta( get_the_ID(), '_adn_article_icon', true ) ?: '📰',
			'title' => get_the_title(),
			'date'  => get_the_date( 'M j, Y' ),
			'url'   => get_permalink(),
		);
	}
	wp_reset_postdata();
}

/* ── Collect posts into card data before template output ── */
$_cards = array();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$_type_obj  = get_post_type_object( get_post_type() );
		$_type_name = $_type_obj ? $_type_obj->labels->singular_name : '';
		$_thumb_url = get_the_post_thumbnail_url( null, 'medium' );

		if ( $_thumb_url ) {
			$_gradient = 'url(' . esc_url( $_thumb_url ) . ') center/cover no-repeat';
			$_icon     = '';
		} else {
			$_gradient = 'linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%)';
			$_icon     = 'post' === get_post_type() ? '📄' : '📋';
		}

		$_cards[] = array(
			'icon'        => $_icon,
			'gradient'    => $_gradient,
			'category'    => $_type_name,
			'title'       => get_the_title(),
			'description' => wp_trim_words( get_the_excerpt(), 18 ),
			'read_more'   => 'Read more →',
			'url'         => get_permalink(),
		);
	}
}
?>

<section class="search-page-body">
	<div class="container">
		<div class="article-layout search-results-layout">

			<?php /* ── MAIN COLUMN ── */ ?>
			<main class="search-main" id="main-content">

				<?php /* Search refinement bar */ ?>
				<div class="search-refine-bar">
					<form class="search-refine-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<span class="search-refine-icon" aria-hidden="true">🔍</span>
						<input type="search" name="s" class="search-refine-input"
						       placeholder="Search guides, articles&hellip;"
						       value="<?php echo esc_attr( $_query ); ?>"
						       aria-label="Search">
						<button type="submit" class="btn btn-primary btn-sm">Search</button>
					</form>
				</div>

				<?php if ( ! empty( $_cards ) ) : ?>

					<div class="guides-grid search-results-grid">
						<?php foreach ( $_cards as $_card ) : ?>
							<?php adn_component( 'cards/guide_card', array( 'card' => $_card ) ); ?>
						<?php endforeach; ?>
					</div>

					<?php if ( $GLOBALS['wp_query']->max_num_pages > 1 ) : ?>
						<nav class="search-pagination" aria-label="Search results pages">
							<?php the_posts_pagination( array(
								'prev_text' => '← Previous',
								'next_text' => 'Next →',
							) ); ?>
						</nav>
					<?php endif; ?>

				<?php else : ?>

					<div class="search-no-results">
						<span class="search-no-icon" aria-hidden="true">🔍</span>
						<h2 class="search-no-title">
							<?php if ( $_query ) : ?>
								No results for <span>"<?php echo esc_html( $_query ); ?>"</span>
							<?php else : ?>
								What are you looking for?
							<?php endif; ?>
						</h2>
						<p class="search-no-desc">Try different keywords, or browse our guides and articles.</p>
						<a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-primary search-no-browse">Browse all guides</a>
					</div>

				<?php endif; ?>
			</main>

			<?php /* ── SIDEBAR ── */ ?>
			<aside class="article-sidebar">

				<?php /* Calculators */ ?>
				<?php if ( ! empty( $_sidebar['calculators'] ) ) : ?>
					<?php adn_component( 'parts/post_sidebar_calcs', array( 'calculators' => $_sidebar['calculators'] ) ); ?>
				<?php endif; ?>

				<?php /* Latest news */ ?>
				<?php if ( ! empty( $_sidebar_news ) ) : ?>
					<?php adn_component( 'parts/post_sidebar_news', array( 'latest_news' => $_sidebar_news ) ); ?>
				<?php endif; ?>

				<?php /* Newsletter CTA */ ?>
				<?php adn_component( 'parts/post_sidebar_newsletter', array(
					'newsletter' => isset( $_sidebar['newsletter'] ) ? $_sidebar['newsletter'] : array(
						'icon'         => '✉️',
						'heading'      => 'Stay Informed',
						'description'  => 'Get the latest property news and guides delivered to your inbox.',
						'placeholder'  => 'Your email address',
						'button_label' => 'Subscribe',
						'note'         => 'No spam. Unsubscribe any time.',
					),
				) ); ?>

			</aside>

		</div>
	</div>
</section>

<?php adn_page_close( $_ctx ); ?>
