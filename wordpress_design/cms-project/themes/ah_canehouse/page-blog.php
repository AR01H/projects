<?php
/**
 * Template Name: The Cane Journal
 */
get_header();

$active_cat = sanitize_text_field( $_GET['category'] ?? '' );
$paged      = max( 1, get_query_var( 'paged' ) ?: ( get_query_var( 'page' ) ?: 1 ) );

$args = [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 9,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
];
if ( $active_cat ) {
	$term = get_term_by( 'slug', $active_cat, 'category' );
	if ( $term ) {
		$args['cat'] = $term->term_id;
	}
}
$journal_query = new WP_Query( $args );
$wp_cats       = get_categories( [ 'hide_empty' => true ] );
?>

<main class="ch-main" id="main-content">

<!-- ── Page Header ────────────────────────────────────────────────────────── -->
<div class="ch-page-hero">
	<div class="container">
		<span class="ch-eyebrow">Tips · Recipes · Culture</span>
		<h1 class="ch-page-hero__title">The Cane <em>Journal</em></h1>
		<p class="ch-page-hero__desc">
			Health tips, sugarcane facts, seasonal recipes and stories from
			the heart of South Asian juice culture.
		</p>
	</div>
</div>

<!-- ── Category Filter ─────────────────────────────────────────────────────── -->
<?php if ( $wp_cats ) : ?>
<div class="ch-filter-bar">
	<div class="container">
		<div class="ch-filter-tabs" role="tablist" aria-label="Journal categories">
			<a href="<?php echo esc_url( get_permalink() ); ?>"
				class="ch-filter-tab<?php if ( ! $active_cat ) echo ' ch-filter-tab--active'; ?>"
				role="tab" aria-selected="<?php echo ! $active_cat ? 'true' : 'false'; ?>">
				All Articles
			</a>
			<?php foreach ( $wp_cats as $cat ) :
				$is_active = ( $active_cat === $cat->slug );
			?>
			<a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, get_permalink() ) ); ?>"
				class="ch-filter-tab<?php if ( $is_active ) echo ' ch-filter-tab--active'; ?>"
				role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
				<?php echo esc_html( $cat->name ); ?>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- ── Posts Grid ──────────────────────────────────────────────────────────── -->
<div class="container ch-page-content">

	<?php if ( $journal_query->have_posts() ) : ?>
		<div class="ch-posts-grid">
			<?php while ( $journal_query->have_posts() ) :
				$journal_query->the_post();
			?>
			<article class="ch-post-card fade-up">
				<?php if ( has_post_thumbnail() ) : ?>
					<a href="<?php the_permalink(); ?>" class="ch-post-card__img">
						<?php the_post_thumbnail( 'ch-card' ); ?>
					</a>
				<?php else : ?>
					<a href="<?php the_permalink(); ?>" class="ch-post-card__img ch-post-card__img--placeholder"
						style="display:flex;align-items:center;justify-content:center;min-height:200px;background:var(--ch-green-deep);font-size:3rem">
						🥤
					</a>
				<?php endif; ?>
				<div class="ch-post-card__body">
					<?php $cats = get_the_category(); if ( $cats ) : ?>
						<div class="ch-post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></div>
					<?php endif; ?>
					<div class="ch-post-card__date"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></div>
					<h2 class="ch-post-card__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<p class="ch-post-card__excerpt"><?php echo esc_html( ch_excerpt() ); ?></p>
					<a href="<?php the_permalink(); ?>" class="btn-lime-sm">Read Article →</a>
				</div>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<?php ch_pagination(); ?>

	<?php else : ?>
		<div style="text-align:center;padding:80px 24px">
			<div style="font-size:3rem;margin-bottom:16px">🌿</div>
			<h2 style="font-size:1.4rem;margin-bottom:12px">No articles yet</h2>
			<p style="opacity:.7;margin-bottom:24px">
				<?php if ( $active_cat ) : ?>
					Nothing in this category yet — browse all articles.
				<?php else : ?>
					We're brewing something great — check back soon.
				<?php endif; ?>
			</p>
			<?php if ( $active_cat ) : ?>
				<a href="<?php echo esc_url( get_permalink() ); ?>" class="ch-nav__cta-btn">View All Articles →</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</div>

</main>

<?php get_footer(); ?>
