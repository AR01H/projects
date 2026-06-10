<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">
	<div class="ch-page-hero">
		<div class="container">
			<h1 class="ch-page-hero__title"><?php the_archive_title(); ?></h1>
		</div>
	</div>
	<div class="container ch-page-content">
		<?php if ( have_posts() ) : ?>
			<div class="ch-posts-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="ch-post-card">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="ch-post-card__img">
								<?php the_post_thumbnail( 'ch-card' ); ?>
							</a>
						<?php endif; ?>
						<div class="ch-post-card__body">
							<div class="ch-post-card__date"><?php echo esc_html( get_the_date() ); ?></div>
							<h2 class="ch-post-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
							<p class="ch-post-card__excerpt"><?php echo esc_html( ch_excerpt() ); ?></p>
							<a href="<?php the_permalink(); ?>" class="btn-lime-sm">Read More →</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php ch_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found.', 'ch-theme' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
