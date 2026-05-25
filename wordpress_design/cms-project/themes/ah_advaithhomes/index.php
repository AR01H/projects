<?php
get_header();
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Insights & Expertise',
	'title'      => 'The ' . CLIENT_FULL_TITLE,
	'title_em'   => 'Blog',
	'desc'       => 'Practical advice from buyer\'s agents – market insights, step-by-step guides, and everything you need to buy smarter.',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'Blog', '' ] ],
] ); ?>

<section class="section" aria-label="<?php echo esc_attr( TXT_BLOG_POSTS ); ?>">
  <div class="container">
    <?php if ( have_posts() ) : ?>
      <div class="post-grid">
        <?php while ( have_posts() ) : the_post();
          $post_url   = get_permalink();
          $post_title = get_the_title();
          $cats       = get_the_category();
          $thumb_url  = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'ah-card' ) : '';
        ?>
        <article class="blog-card" data-aos="fade-up">
          <div class="blog-card__img-wrap">
            <a href="<?php echo esc_url( $post_url ); ?>" class="blog-card__img-link" tabindex="-1" aria-hidden="true">
              <?php if ( $thumb_url ) : ?>
              <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $post_title ); ?>" class="blog-card__img" loading="lazy">
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
              <!-- Slides up from inside the card on hover -->
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
        <?php endwhile; ?>
      </div>
      <?php ah_pagination(); ?>
    <?php else : ?>
    <div class="text-center" style="padding:80px 24px">
      <div style="font-size:3.5rem;margin-bottom:16px">✍️</div>
      <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">No posts yet</h2>
      <p style="color:var(--text-secondary)">We're working on something great – check back soon.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
