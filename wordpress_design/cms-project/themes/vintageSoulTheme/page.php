<?php

use VintageSoul\Controllers\PageController;
use VintageSoul\Services\RouteService;

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
			?>
			<article <?php post_class( 'page' ); ?> id="post-<?php the_ID(); ?>">
				<div class="section">
					<div class="container container--narrow">
						<header class="page__header">
							<h1 class="page__title"><?php echo esc_html( $data['title'] ); ?></h1>
						</header>
						<div class="page__content">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</article>
			<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
		<?php endif;
	endwhile; ?>
</main>
<?php
get_footer();
