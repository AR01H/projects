<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">
	<div class="container" style="padding:6rem 2rem 4rem;">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'ch-post-card' ); ?>>
					<h2 class="ch-post-card__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<div class="ch-post-card__excerpt"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
			<?php ch_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No content found.', 'ch-theme' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
