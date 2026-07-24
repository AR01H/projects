<?php
/**
 * search.php - WordPress search results template.
 */

defined( 'ABSPATH' ) || exit;

get_header();

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
		array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
		array( 'label' => 'Search' ),
	),
) );

/* ── Sidebar: latest news (CMS news, not WP posts) ── */
$_sidebar_news = array();
if ( function_exists( 'adn_cms_newsbar_items' ) ) {
	foreach ( adn_cms_newsbar_items( 4 ) as $_ni ) {
		$_ntitle = isset( $_ni->text ) ? (string) $_ni->text : '';
		if ( '' === $_ntitle ) { continue; }
		$_sidebar_news[] = array(
			'icon'  => '📰',
			'title' => $_ntitle,
			'date'  => ! empty( $_ni->start_date ) ? date_i18n( 'M j, Y', strtotime( $_ni->start_date ) ) : '',
			'url'   => ! empty( $_ni->link_url ) ? (string) $_ni->link_url : '#',
		);
	}
}
if ( empty( $_sidebar_news ) && function_exists( 'adn_cms_latest_news' ) ) {
	foreach ( adn_cms_latest_news( 4 ) as $_ni ) {
		$_ntitle = isset( $_ni->title ) ? (string) $_ni->title : ( isset( $_ni->text ) ? (string) $_ni->text : '' );
		if ( '' === $_ntitle ) { continue; }
		$_sidebar_news[] = array(
			'icon'  => '📰',
			'title' => $_ntitle,
			'date'  => ! empty( $_ni->published_date ) ? date_i18n( 'M j, Y', strtotime( $_ni->published_date ) ) : '',
			'url'   => ! empty( $_ni->url ) ? (string) $_ni->url : '#',
		);
	}
}

/* ── Sidebar: guide parent categories ── */
$_guide_parents_items = array();
if ( function_exists( 'adn_cms_guide_parents' ) ) {
	foreach ( adn_cms_guide_parents() as $_gp ) {
		$_guide_parents_items[] = array(
			'icon'  => ! empty( $_gp->icon_emoji ) ? (string) $_gp->icon_emoji : '📚',
			'label' => (string) $_gp->name,
			'url'   => home_url( '/' . trim( (string) $_gp->slug, '/' ) . '/' ),
			'count' => 0,
		);
	}
}

/* ── Sidebar: expert help ── */
$_eh_opt = get_option( 'adn_calculators_page', array() );
$_expert_help = array(
	'heading'  => ! empty( $_eh_opt['sidebar_help_title'] ) ? $_eh_opt['sidebar_help_title'] : adn_term( 'sidebar.expert_help_heading', 'Need Expert Help?' ),
	'subtitle' => ! empty( $_eh_opt['sidebar_help_text'] )  ? $_eh_opt['sidebar_help_text']  : adn_term( 'sidebar.expert_help_subtitle', 'Get personalised guidance from our experts.' ),
	'experts'  => array(),
	'cta'      => array(
		'label' => ! empty( $_eh_opt['sidebar_help_btn_label'] ) ? $_eh_opt['sidebar_help_btn_label'] : adn_term( 'sidebar.expert_help_cta', 'Talk to an Expert' ),
		'url'   => ! empty( $_eh_opt['sidebar_help_btn_url'] )   ? $_eh_opt['sidebar_help_btn_url']   : home_url( SITE_CONTACT_URL ),
	),
);

/* ── Sidebar: tools ── */
$_sidebar = function_exists( 'adn_service_post_sidebar_data' ) ? adn_service_post_sidebar_data() : array();

/* ── Collect posts into card data ── */
$_cards = array();
if ( have_posts() ) {
	$_i = 0;
	while ( have_posts() ) {
		the_post();
		$_thumb_url = get_the_post_thumbnail_url( null, 'medium' );
		$_word_count = str_word_count( wp_strip_all_tags( get_the_content() ) );
		$_read_mins  = max( 1, round( $_word_count / 200 ) );
		$_type_obj   = get_post_type_object( get_post_type() );

		$_cards[] = array(
			'icon'      => 'post' === get_post_type() ? '📄' : '📋',
			'img_class' => function_exists( 'adn_cms_gradient' ) ? adn_cms_gradient( $_i ) : '',
			'thumbnail' => $_thumb_url ?: '',
			'category'  => $_type_obj ? strtoupper( (string) $_type_obj->labels->singular_name ) : '',
			'title'     => get_the_title(),
			'desc'      => wp_trim_words( get_the_excerpt(), 15 ),
			'read_time' => $_read_mins . ' min read',
			'url'       => get_permalink(),
		);
		$_i++;
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
						<span class="search-refine-icon" aria-hidden="true"><i class="fa-solid fa-search"></i></span>
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
							<?php adn_component( 'cards/guide_listing_card', array( 'item' => $_card ) ); ?>
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

					<?php /* Browse more – fills empty space on later pages */ ?>
					<?php adn_component( 'parts/browse_more_cta', array( 'browse_cta' => array(
						'icon'        => '📚',
						'heading'     => adn_term( 'search.browse_more_heading', 'Looking for something specific?' ),
						'description' => adn_term( 'search.browse_more_desc', 'Browse our full library of guides and resources.' ),
						'links'       => array(
							array( 'label' => SITE_CONTENT_PLURAL, 'url' => home_url( SITE_GUIDES_URL ),      'primary' => true ),
							array( 'label' => SITE_NEWS_NOUN,      'url' => home_url( SITE_NEWS_URL ),        'primary' => false ),
							array( 'label' => SITE_TOOLS_PLURAL,   'url' => home_url( SITE_CALCULATORS_URL ), 'primary' => false ),
						),
					) ) ); ?>

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
						<a href="<?php echo esc_url( home_url( SITE_GUIDES_URL ) ); ?>" class="btn btn-primary search-no-browse">Browse all <?php echo esc_html( strtolower( SITE_CONTENT_PLURAL ) ); ?></a>
					</div>

				<?php endif; ?>
			</main>


		</div>
	</div>
</section>

<?php adn_page_close( $_ctx ); ?>

<?php get_footer(); ?>
