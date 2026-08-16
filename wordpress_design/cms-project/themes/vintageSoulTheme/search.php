<?php

use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="main">
	<div class="container loop">
		<h1 class="archive__title">
			<?php
			printf(

				esc_html__( 'Search results for: %s', 'vintagesoul' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php View::part( 'content' ); ?>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found.', 'vintagesoul' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
