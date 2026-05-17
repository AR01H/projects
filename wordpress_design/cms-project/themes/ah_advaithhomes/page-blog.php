<?php
/**
 * Template Name: Blog Listing
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
$blog_query = new WP_Query( $args );

$wp_cats = get_categories( [ 'hide_empty' => true ] );
?>

<!-- ── Page Hero ─────────────────────────────────────────────────────────── -->
<section class="page-hero page-hero--sm" aria-label="Blog">
  <div class="container">
    <div class="page-hero__copy text-center" style="max-width:640px;margin-inline:auto" data-aos="fade-up">
      <span class="section__eyebrow">Insights &amp; Expertise</span>
      <h1 class="page-hero__title">The Advaith Homes<br><em>Blog</em></h1>
      <p class="page-hero__desc">
        Practical advice from buyer's agents — market insights, step-by-step guides,
        and everything you need to buy smarter.
      </p>
    </div>
  </div>
</section>

<!-- ── Category Filter ────────────────────────────────────────────────────── -->
<?php if ( $wp_cats ) : ?>
<div style="border-bottom:1px solid var(--border);background:var(--bg-alt)">
  <div class="container" style="padding-top:16px;padding-bottom:16px">
    <div class="filter-tabs" role="tablist" aria-label="Blog categories">
      <a href="<?php echo esc_url( get_permalink() ); ?>"
         class="filter-tab<?php if ( ! $active_cat ) echo ' filter-tab--active'; ?>"
         role="tab" aria-selected="<?php echo ! $active_cat ? 'true' : 'false'; ?>">
        All Posts
      </a>
      <?php foreach ( $wp_cats as $cat ) :
        $is_active = ( $active_cat === $cat->slug );
      ?>
      <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, get_permalink() ) ); ?>"
         class="filter-tab<?php if ( $is_active ) echo ' filter-tab--active'; ?>"
         role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
        <?php echo esc_html( $cat->name ); ?>
        <span style="font-size:.75rem;opacity:.7;margin-left:4px">(<?php echo esc_html( $cat->count ); ?>)</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── Blog Grid ──────────────────────────────────────────────────────────── -->
<section class="section" aria-label="Blog posts">
  <div class="container">

    <?php if ( $blog_query->have_posts() ) : ?>

      <?php
      // Pull the first post as a featured card on page 1 with no category filter
      $show_featured = ( $paged === 1 && ! $active_cat && $blog_query->found_posts > 1 );
      $first_done    = false;
      ?>

      <?php if ( $show_featured ) : $blog_query->the_post(); ?>
      <!-- Featured / latest post ─────────────────────── -->
      <article class="post-card post-card--featured" style="display:grid;grid-template-columns:1fr 1fr;gap:0;margin-bottom:40px;border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--shadow-md)" data-aos="fade-up">
        <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" style="display:block;overflow:hidden;aspect-ratio:auto">
          <?php the_post_thumbnail( 'large', [ 'style' => 'width:100%;height:100%;object-fit:cover;display:block' ] ); ?>
        </a>
        <?php endif; ?>
        <div class="post-card__body" style="padding:40px;justify-content:center">
          <?php $cats = get_the_category(); if ( $cats ) : ?>
          <div class="post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></div>
          <?php endif; ?>
          <div class="card__meta" style="margin-top:0">
            <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
            <span>·</span>
            <span><?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span>
          </div>
          <h2 class="post-card__title" style="font-size:1.5rem;line-height:1.25">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h2>
          <p class="post-card__excerpt" style="font-size:.95rem">
            <?php echo esc_html( wp_trim_words( get_the_excerpt(), 28, '…' ) ); ?>
          </p>
          <a href="<?php the_permalink(); ?>" class="btn btn-primary" style="align-self:flex-start;margin-top:8px">
            Read Article →
          </a>
        </div>
      </article>
      <?php $first_done = true; endif; ?>

      <!-- Card grid ──────────────────────────────────── -->
      <div class="post-grid">
        <?php
        if ( ! $first_done && $blog_query->have_posts() ) {
          // No featured post consumed — loop normally from start
        }
        while ( $blog_query->have_posts() ) :
          $blog_query->the_post();
        ?>
        <article class="post-card" data-aos="fade-up">
          <?php if ( has_post_thumbnail() ) : ?>
          <a href="<?php the_permalink(); ?>" class="post-card__img-wrap">
            <?php the_post_thumbnail( 'ah-card' ); ?>
          </a>
          <?php else : ?>
          <a href="<?php the_permalink(); ?>" class="post-card__img-wrap"
             style="background:var(--bg-alt);display:flex;align-items:center;justify-content:center;min-height:180px;font-size:3rem">
            📰
          </a>
          <?php endif; ?>
          <div class="post-card__body">
            <?php $cats = get_the_category(); if ( $cats ) : ?>
            <div class="post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></div>
            <?php endif; ?>
            <div class="card__meta">
              <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
              <span>·</span>
              <span><?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span>
            </div>
            <h2 class="post-card__title">
              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>
            <p class="post-card__excerpt">
              <?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?>
            </p>
            <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-ghost" style="margin-top:auto">
              Read More →
            </a>
          </div>
        </article>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>

      <!-- Pagination -->
      <?php if ( $blog_query->max_num_pages > 1 ) :
        $base_url = $active_cat
          ? add_query_arg( 'category', $active_cat, get_permalink() )
          : get_permalink();
        $links = paginate_links( [
          'base'      => trailingslashit( $base_url ) . '%_%',
          'format'    => '?paged=%#%',
          'current'   => $paged,
          'total'     => $blog_query->max_num_pages,
          'prev_text' => '← Prev',
          'next_text' => 'Next →',
          'type'      => 'array',
        ] );
        if ( $links ) :
      ?>
      <nav class="pagination" aria-label="Blog navigation" style="margin-top:48px">
        <ul class="pagination__list">
          <?php foreach ( $links as $link ) echo '<li class="pagination__item">' . $link . '</li>'; ?>
        </ul>
      </nav>
      <?php endif; endif; ?>

    <?php else : ?>
    <!-- Empty state -->
    <div class="text-center" style="padding:80px 24px">
      <div style="font-size:3.5rem;margin-bottom:16px">✍️</div>
      <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">No posts yet</h2>
      <p style="color:var(--text-secondary);margin-bottom:28px;max-width:400px;margin-inline:auto">
        <?php if ( $active_cat ) : ?>
          No posts in this category. Try another or view all.
        <?php else : ?>
          We're working on something great — check back soon.
        <?php endif; ?>
      </p>
      <?php if ( $active_cat ) : ?>
      <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-outline">View All Posts →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ── Newsletter CTA ─────────────────────────────────────────────────────── -->
<section class="section section--alt" aria-label="Newsletter">
  <div class="container container--sm">
    <div class="newsletter-block text-center" data-aos="fade-up">
      <span class="section__eyebrow">Stay Informed</span>
      <h2 class="section__title" style="font-size:1.75rem;margin-bottom:12px">
        Get Expert Insights Straight to Your Inbox
      </h2>
      <p style="color:var(--text-secondary);margin-bottom:28px">
        Market updates, new guides, and buyer tips — once a week. No spam, ever.
      </p>
      <form data-ah-newsletter class="ah-newsletter-form" novalidate>
        <div class="newsletter-inline">
          <input type="email" name="email" class="form-input" placeholder="Your email address" required>
          <button type="submit" class="btn btn-primary">Subscribe →</button>
        </div>
        <div class="ah-form__status" aria-live="polite"></div>
      </form>
    </div>
  </div>
</section>

<?php get_footer(); ?>
