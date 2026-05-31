<?php
/**
 * Single post - The Cane Journal article view.
 * Posts are authored in the CMS plugin (admin.php?page=ah-posts) as native WP
 * posts, so the loop below picks them up exactly the way page-blog.php does.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main ch-single" id="main-content">
	<?php while ( have_posts() ) : the_post();

		$cats        = get_the_category();
		$primary_cat = $cats ? $cats[0] : null;
		$author      = get_the_author();
		$word_count  = str_word_count( wp_strip_all_tags( get_the_content() ) );
		$read_min    = max( 1, (int) ceil( $word_count / 200 ) );
		$permalink   = get_permalink();
		$share_title = rawurlencode( get_the_title() );
		$share_url   = rawurlencode( $permalink );
	?>

		<!-- ── Article Hero ───────────────────────────────────────────────────── -->
		<div class="ch-page-hero ch-single-hero">
			<div class="container">
				<nav class="ch-single-breadcrumb" aria-label="Breadcrumb">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
					<span aria-hidden="true">›</span>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' ) ); ?>">Journal</a>
					<?php if ( $primary_cat ) : ?>
						<span aria-hidden="true">›</span>
						<a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>"><?php echo esc_html( $primary_cat->name ); ?></a>
					<?php endif; ?>
				</nav>

				<?php if ( $primary_cat ) : ?>
					<div class="ch-eyebrow"><?php echo esc_html( $primary_cat->name ); ?></div>
				<?php endif; ?>

				<h1 class="ch-page-hero__title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="ch-page-hero__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<!-- ── Featured Image ─────────────────────────────────────────────────── -->
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="ch-post-thumb container">
				<?php the_post_thumbnail( 'ch-hero', [ 'loading' => 'eager', 'class' => 'ch-single-thumb-img' ] ); ?>
			</div>
		<?php endif; ?>

		<!-- ── Content ────────────────────────────────────────────────────────── -->
		<article class="ch-single-content">
			<?php the_content(); ?>

			<?php
			$pagination = wp_link_pages( [ 'echo' => 0, 'before' => '<nav class="ch-pagination">', 'after' => '</nav>' ] );
			if ( $pagination ) echo $pagination;
			?>

			<!-- Tags + Share -->
			<div class="ch-single-footer">
				<?php $tags = get_the_tags(); if ( $tags ) : ?>
					<div class="ch-single-tags">
						<?php foreach ( $tags as $tag ) : ?>
							<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="ch-single-tag">#<?php echo esc_html( $tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="ch-single-share">
					<span class="ch-single-share__label">Share</span>
					<a class="ch-single-share__btn" target="_blank" rel="noopener" aria-label="Share on WhatsApp"
						href="<?php echo esc_url( 'https://wa.me/?text=' . $share_title . '%20' . $share_url ); ?>">💬</a>
					<a class="ch-single-share__btn" target="_blank" rel="noopener" aria-label="Share on Facebook"
						href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url ); ?>">👍</a>
					<a class="ch-single-share__btn" target="_blank" rel="noopener" aria-label="Share on X"
						href="<?php echo esc_url( 'https://twitter.com/intent/tweet?text=' . $share_title . '&url=' . $share_url ); ?>">✦</a>
				</div>
			</div>
		</article>

		<!-- ── Related Articles ───────────────────────────────────────────────── -->
		<?php
		$related_args = [
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 3,
			'post__not_in'        => [ get_the_ID() ],
			'orderby'             => 'rand',
			'ignore_sticky_posts' => true,
		];
		if ( $primary_cat ) $related_args['cat'] = $primary_cat->term_id;
		$related = new WP_Query( $related_args );
		if ( $related->have_posts() ) :
		?>
			<section class="ch-single-related">
				<div class="container">
					<div class="ch-section-center fade-up">
						<div class="section-tag">Keep Reading</div>
						<h2 class="section-title">More from the <span class="accent">Journal</span></h2>
					</div>
					<div class="ch-posts-grid">
						<?php while ( $related->have_posts() ) : $related->the_post(); ?>
							<article class="ch-post-card fade-up">
								<a href="<?php the_permalink(); ?>" class="ch-post-card__img<?php echo has_post_thumbnail() ? '' : ' ch-post-card__img--placeholder'; ?>"
									<?php echo has_post_thumbnail() ? '' : 'style="display:flex;align-items:center;justify-content:center;min-height:200px;background:var(--ch-green-deep);font-size:3rem;"'; ?>>
									<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'ch-card' ); else echo '🥤'; ?>
								</a>
								<div class="ch-post-card__body">
									<div class="ch-post-card__date"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></div>
									<h3 class="ch-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="ch-post-card__excerpt"><?php echo esc_html( ch_excerpt() ); ?></p>
									<a href="<?php the_permalink(); ?>" class="btn-lime-sm">Read Article →</a>
								</div>
							</article>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- ── CTA ────────────────────────────────────────────────────────────── -->
		<section class="ch-inner-cta">
			<div class="container">
				<div class="ch-inner-cta__box fade-up">
					<h2>Thirsty for the Real Thing?</h2>
					<p>Book our live sugarcane juice stall for your next event, or explore our fresh-pressed menu.</p>
					<div class="ch-inner-cta__btns">
						<a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="btn-lime">🎪 Book an Event</a>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' ) ); ?>" class="btn-outline ch-btn-outline-light">← Back to Journal</a>
					</div>
				</div>
			</div>
		</section>

	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
