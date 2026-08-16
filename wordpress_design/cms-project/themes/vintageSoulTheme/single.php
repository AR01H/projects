<?php

use VintageSoul\Controllers\SingleController;

defined( 'ABSPATH' ) || exit;

$data = ( new SingleController() )->prepare();

get_header();
?>
<main id="main" class="main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'post' ); ?> id="post-<?php the_ID(); ?>">
			<header class="post__header">
				<h1 class="post__title"><?php echo esc_html( $data['title'] ); ?></h1>
				<p class="post__meta">
					<?php
					printf(

						esc_html__( '%d min read', 'vintagesoul' ),
						(int) $data['reading_time']
					);
					?>
				</p>
			</header>
			<div class="post__content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
	<?php endwhile; ?>
</main>
<?php
get_footer();
