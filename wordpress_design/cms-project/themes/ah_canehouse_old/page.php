<?php
/**
 * Generic page - renders native WP pages created in the CMS plugin
 * (admin.php?page=ah-pages). Pages with an assigned template use their own
 * page-{slug}.php; everything else falls back here.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">
	<?php while ( have_posts() ) : the_post(); ?>

		<div class="ch-page-hero">
			<div class="container">
				<h1 class="ch-page-hero__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="ch-page-hero__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="ch-post-thumb container">
				<?php the_post_thumbnail( 'ch-hero', [ 'loading' => 'eager' ] ); ?>
			</div>
		<?php endif; ?>

		<article class="ch-single-content">
			<?php the_content(); ?>
			<?php
			$pagination = wp_link_pages( [ 'echo' => 0, 'before' => '<nav class="ch-pagination">', 'after' => '</nav>' ] );
			if ( $pagination ) echo $pagination;
			?>
		</article>

	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
