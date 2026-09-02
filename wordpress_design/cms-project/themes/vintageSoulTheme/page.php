<?php

use VintageSoul\Controllers\PageController;
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
			$data = ( new PageController() )->prepare();
			$post_title = get_the_title();
			?>
			<!-- Subpage Hero for Standard WordPress Pages -->
			<?php
			View::component(
				'subpage-hero/subpage-hero',
				array(
					'id'    => 'page-hero-' . get_the_ID(),
					'tag'   => '✦ THE CANE HOUSE ✦',
					'title' => $post_title,
					'sub'   => 'Official Information & Policy Details',
					'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
				)
			);
			?>

			<!-- Standard Page Parchment Card Body -->
			<div class="section page-standard-section paper-rough">
				<div class="container container--narrow">
					<article <?php post_class( 'page-standard-card' ); ?> id="post-<?php the_ID(); ?>">
						<div class="page-standard-content entry-content">
							<?php the_content(); ?>
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
