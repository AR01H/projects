<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">
	<?php while ( have_posts() ) : the_post(); ?>

		<!-- ── Post Hero ──────────────────────────────────────────────────────── -->
		<div class="ch-page-hero">
			<div class="container">
				<?php $cats = get_the_category(); if ( $cats ) : ?>
					<div class="ch-eyebrow"><?php echo esc_html( $cats[0]->name ); ?></div>
				<?php endif; ?>
				<h1 class="ch-page-hero__title"><?php the_title(); ?></h1>
				<p class="ch-page-hero__desc" style="font-size:0.85rem;opacity:0.7;margin-top:0.8rem;">
					<?php echo esc_html( get_the_date( 'j F Y' ) ); ?>
					<?php
					$author = get_the_author();
					if ( $author ) echo ' · By ' . esc_html( $author );
					?>
				</p>
			</div>
		</div>

		<!-- ── Featured Image ─────────────────────────────────────────────────── -->
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="ch-post-thumb container">
				<?php the_post_thumbnail( 'ch-hero', [ 'loading' => 'eager' ] ); ?>
			</div>
		<?php endif; ?>

		<!-- ── Content ───────────────────────────────────────────────────────── -->
		<div class="ch-single-content">
			<?php the_content(); ?>

			<?php
			$pagination = wp_link_pages( [ 'echo' => 0, 'before' => '<nav class="ch-pagination">', 'after' => '</nav>' ] );
			if ( $pagination ) echo $pagination;
			?>
		</div>

		<!-- ── Back to Blog ───────────────────────────────────────────────────── -->
		<div style="text-align:center;padding:2rem 0 5rem;">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' ) ); ?>" class="btn-lime">← Back to Journal</a>
		</div>

	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
