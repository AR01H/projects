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
	'posts_per_page' => 12,
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
	'title'      => 'The ' . CLIENT_FULL_TITLE,
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
    <div class="filter-tabs" role="tablist" aria-label="<?php echo esc_attr( TXT_BLOG_CATEGORIES ); ?>">
      <a href="<?php echo esc_url( get_permalink() ); ?>"
         class="filter-tab<?php if ( ! $active_cat ) echo esc_html( TXT_FILTER_TAB_ACTIVE ); ?>"
         role="tab" aria-selected="<?php echo ! $active_cat ? 'true' : 'false'; ?>">
        <?php echo esc_html( AH_LABEL_ALL_POSTS ); ?>
      </a>
      <?php foreach ( $wp_cats as $cat ) :
        $is_active = ( $active_cat === $cat->slug );
      ?>
      <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, get_permalink() ) ); ?>"
         class="filter-tab<?php if ( $is_active ) echo esc_html( TXT_FILTER_TAB_ACTIVE ); ?>"
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
<section class="section" aria-label="<?php echo esc_attr( TXT_BLOG_POSTS ); ?>">
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
            <span><?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span>
          </div>
          <h2 class="post-card__title" style="font-size:1.5rem;line-height:1.25">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h2>
          <p class="post-card__excerpt" style="font-size:.95rem">
            <?php echo esc_html( wp_trim_words( get_the_excerpt(), 28, '…' ) ); ?>
          </p>
          <a href="<?php the_permalink(); ?>" class="btn btn-primary" style="align-self:flex-start;margin-top:8px">
            <?php echo esc_html( AH_LABEL_READ_ARTICLE ); ?> →
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
        <article class="blog-card" data-aos="fade-up">
          <div class="blog-card__img-wrap">
            <a href="<?php echo esc_url( $post_url ); ?>" class="blog-card__img-link" tabindex="-1" aria-hidden="true">
              <?php if ( $thumb_url ) : ?>
              <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_POST_TITLE ); ?>" class="blog-card__img" loading="lazy">
              <?php else : ?>
              <div class="blog-card__img-placeholder">✍️</div>
              <?php endif; ?>
            </a>
            <div class="blog-card__overlay">
              <div class="blog-card__badges">
                <?php if ( $cats ) : ?>
                <span class="blog-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
                <?php else : ?><span></span><?php endif; ?>
                <span class="blog-card__read-time"><?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span>
              </div>
              <h2 class="blog-card__title">
                <a href="<?php echo esc_url( $post_url ); ?>"><?php the_title(); ?></a>
              </h2>
              <!-- Slides up inside the card on hover -->
              <div class="blog-card__desc-wrap">
                <div>
                  <p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
                  <a href="<?php echo esc_url( $post_url ); ?>" class="blog-card__read-btn">
                    <?php echo esc_html( AH_LABEL_READ_MORE ); ?> <span aria-hidden="true">→</span>
                  </a>
                </div>
              </div>
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
      <nav class="pagination" aria-label="<?php echo esc_attr( TXT_BLOG_NAVIGATION ); ?>" style="margin-top:48px">
        <ul class="pagination__list">
          <?php foreach ( $links as $link ) echo esc_html( TXT_LI_CLASS_PAGINATION_ITEM_LINK_LI ); ?>
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
      <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-outline"><?php echo esc_html( AH_LABEL_VIEW_ALL_POSTS ); ?> →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ── Newsletter CTA ─────────────────────────────────────────────────────── -->
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_NEWSLETTER ); ?>">
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
        <a href="<?php echo esc_url( home_url( AH_LINK_CONTACT ) ); ?>"><?php echo esc_html( AH_LABEL_CONTACT_US ); ?></a>
      </button>
    </div>
  </div>
</section>

<?php get_footer(); ?>
