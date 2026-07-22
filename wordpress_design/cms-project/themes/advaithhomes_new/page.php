<?php
/**
 * page.php - WordPress standard page template.
 *
 * Renders any WP page (created via Pages → Add New) with the full site
 * header, hero and footer - identical chrome to every other theme page.
 * Content is whatever is saved in the WordPress editor (classic or Gutenberg).
 */

defined( 'ABSPATH' ) || exit;

$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

if ( ! have_posts() ) {
	adn_page_open( array( 'chrome' => $chrome ) );
	adn_page_close( array( 'chrome' => $chrome, 'skip_page_content' => true ) );
	return;
}

the_post();

$page_title = get_the_title();
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
			'description' => get_the_excerpt(),
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
