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

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Insights & Expertise',
	'title'      => 'The Advaith Homes',
	'title_em'   => 'Blog',
	'desc'       => 'Practical advice from buyer\'s agents - market insights, step-by-step guides, and everything you need to buy smarter.',
	'breadcrumb' => [
		[ 'Home', home_url( '/' ) ],
		[ 'Blog', '' ],
	],
] ); ?>

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
        while ( $blog_query->have_posts() ) :
          $blog_query->the_post();
          $thumb_url  = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'ah-card' ) : '';
          $post_url   = get_permalink();
          $post_title = get_the_title();
          $cats       = get_the_category();
        ?>
        <article class="post-card post-card--overlay" data-aos="fade-up">
          <div class="post-card__bg"<?php if ( $thumb_url ) echo ' style="background-image:url(' . esc_url( $thumb_url ) . ')"'; ?>></div>

          <div class="post-card__content">
            <div class="post-card__top">
              <?php if ( $cats ) : ?>
              <span class="post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
              <?php else : ?>
              <span></span>
              <?php endif; ?>

              <div class="post-share">
                <button class="post-share__btn" aria-label="Share this post" aria-expanded="false">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                  </svg>
                </button>
                <div class="post-share__popover" role="dialog" aria-label="Share options">
                  <span class="post-share__label">Share</span>
                  <div class="post-share__icons">
                    <a href="https://wa.me/?text=<?php echo rawurlencode( $post_title . ' ' . $post_url ); ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="post-share__icon post-share__icon--wa" aria-label="Share on WhatsApp">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                    </a>
                    <button class="post-share__icon post-share__icon--copy"
                            data-url="<?php echo esc_attr( $post_url ); ?>"
                            aria-label="Copy link">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                    </button>
                    <button class="post-share__icon post-share__icon--native"
                            data-url="<?php echo esc_attr( $post_url ); ?>"
                            data-title="<?php echo esc_attr( $post_title ); ?>"
                            aria-label="More share options">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="post-card__info">
              <div class="card__meta">
                <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
                <span>·</span>
                <span><?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span>
              </div>
              <h2 class="post-card__title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              </h2>
              <p class="post-card__excerpt">
                <?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?>
              </p>
              <a href="<?php the_permalink(); ?>" class="post-card__read-btn">
                Read <span aria-hidden="true">→</span>
              </a>
            </div>
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
          We're working on something great - check back soon.
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
<section class="section section--pattern" aria-label="Newsletter">
  <div class="container container--sm">
    <div class="newsletter-block text-center" data-aos="fade-up">
      <span class="section__eyebrow">Stay Informed</span>
      <h2 class="section__title" style="font-size:1.75rem;margin-bottom:12px">
        Do you need more information ?
      </h2>
      <p style="color:var(--text-secondary);margin-bottom:28px">
        Market updates, new guides, and buyer tips - once a week. No spam, ever.
      </p>
      <button class="btn btn-primary">
        <a href="/contact">Contact Us</a>
      </button>
    </div>
  </div>
</section>

<?php get_footer(); ?>
