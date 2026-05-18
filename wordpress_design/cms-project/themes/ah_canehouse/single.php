<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">
	<?php while ( have_posts() ) : the_post(); ?>
		<div class="ch-page-hero">
			<div class="container">
				<div class="ch-post-meta">
					<span><?php echo esc_html( get_the_date() ); ?></span>
					<?php if ( has_category() ) : ?>
						<span class="ch-post-meta__sep">·</span>
						<?php the_category( ', ' ); ?>
					<?php endif; ?>
				</div>
				<h1 class="ch-page-hero__title"><?php the_title(); ?></h1>
			</div>
		</div>
		<div class="container ch-page-content">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="ch-post-thumb"><?php the_post_thumbnail( 'ch-hero' ); ?></div>
			<?php endif; ?>
			<div class="ch-post-body"><?php the_content(); ?></div>
		</div>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
