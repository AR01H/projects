<?php

use VintageSoul\Controllers\PageController;
use VintageSoul\Services\Plugins\PageBridgeService;
use VintageSoul\Services\Plugins\StaticPageBridgeService;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="main">
	<?php while ( have_posts() ) : the_post();
		$page_key = RouteService::key_for_current_page();
		$view     = $page_key ? VINTAGESOUL_DIR . "/pages/{$page_key}/view.php" : null;

		if ( $view && is_file( $view ) ) :
			require $view;
		else :
			$data       = ( new PageController() )->prepare();
			$post_slug  = (string) get_post_field( 'post_name', get_the_ID() );
			$hero_data  = PageBridgeService::resolve_hero(
				$post_slug ?: (int) get_the_ID(),
				array(
					'id'    => 'page-hero-' . get_the_ID(),
					'tag'   => '✦ THE CANE HOUSE ✦',
					'title' => get_the_title(),
					'sub'   => has_excerpt() ? get_the_excerpt() : 'Official Information & Policy Details',
					'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
				)
			);
			$static_html = StaticPageBridgeService::get_html( $post_slug );
			?>
			<!-- Subpage Hero for Standard & Static WordPress Pages -->
			<?php
			View::component(
				'subpage-hero/subpage-hero',
				$hero_data
			);
			?>

			<!-- Standard / Static Page Parchment Card Body -->
			<div class="section page-standard-section paper-rough">
				<div class="container container--narrow">
					<article <?php post_class( 'page-standard-card' . ( '' !== $static_html ? ' ah-static-content-wrapper' : '' ) ); ?> id="post-<?php the_ID(); ?>">
						<div class="page-standard-content entry-content">
							<?php
							if ( '' !== $static_html ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw HTML static page
								echo $static_html;
							} else {
								$standard_content = apply_filters( 'the_content', get_the_content() );
								$standard_content = preg_replace( '/<h1(\s+[^>]*)?>/i', '<h2$1>', (string) $standard_content );
								$standard_content = preg_replace( '/<\/h1>/i', '</h2>', (string) $standard_content );
								echo $standard_content; // phpcs:ignore
							}
							?>
						</div>
					</article>
					<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
				</div>
			</div>
		<?php endif;
	endwhile; ?>
</main>
<?php
get_footer();
