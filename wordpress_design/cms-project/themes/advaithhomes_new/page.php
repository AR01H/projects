<?php
/**
 * page.php - WordPress standard page template.
 *
 * Renders any WP page (created via Pages → Add New) with the full site
 * header, hero and footer - identical chrome to every other theme page.
 * Content is whatever is saved in the WordPress editor (classic or Gutenberg).
 */

defined( 'ABSPATH' ) || exit;

get_header();

$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

if ( ! have_posts() ) {
	adn_page_open( array( 'chrome' => $chrome ) );
	adn_page_close( array( 'chrome' => $chrome, 'skip_page_content' => true ) );
	get_footer();
	return;
}

the_post();

$page_title = get_the_title();
$page_excerpt = get_the_excerpt();
$page_content = get_the_content();

// Fallback description when page has no content/excerpt
if ( '' === trim( $page_excerpt ) && '' === trim( strip_tags( $page_content ) ) ) {
	$page_excerpt = function_exists( 'adn_term' ) ? \adn_term( 'pages.default_description', 'Welcome to our site. Explore our guides, tools and resources to help you make better decisions.' ) : 'Welcome to our site.';
}

$breadcrumb = array(
	array( 'label' => 'Home', 'url' => home_url( '/' ) ),
	array( 'label' => $page_title, 'url' => '' ),
);

adn_page_open( array( 'chrome' => $chrome, 'breadcrumb' => array() ) );

if ( function_exists( 'adn_component' ) ) {
	adn_component( 'sections/page_hero', array(
		'hero'       => array(
			'eyebrow'     => '',
			'title'       => $page_title,
			'description' => $page_excerpt,
		),
		'breadcrumb' => $breadcrumb,
	) );
}
?>

<main class="adn-wp-page" style="min-height:40vh;">
	<div class="section section--md">
		<div class="container">
			<div class="adn-wp-page__content">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
</main>

<?php
adn_page_close( array( 'chrome' => $chrome, 'skip_page_content' => true ) );
get_footer();
