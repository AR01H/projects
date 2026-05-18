<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">
	<div class="ch-page-hero">
		<div class="container">
			<h1 class="ch-page-hero__title">
				<?php printf( esc_html__( 'Search results for: %s', 'ch-theme' ), '<em>' . esc_html( get_search_query() ) . '</em>' ); ?>
			</h1>
		</div>
	</div>
	<div class="container ch-page-content">
		<?php if ( have_posts() ) : ?>
			<div class="ch-posts-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="ch-post-card">
						<div class="ch-post-card__body">
							<h2 class="ch-post-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
							<p class="ch-post-card__excerpt"><?php echo esc_html( ch_excerpt() ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php ch_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No results found. Try a different search.', 'ch-theme' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
