<?php
defined( 'ABSPATH' ) || exit;
$blog_query = $args['blog_query'] ?? null;
$active_cat = $args['active_cat'] ?? '';
$paged      = $args['paged']      ?? 1;
if ( ! $blog_query ) return;
?>
<section class="section" aria-label="<?php echo esc_attr( TXT_BLOG_POSTS ); ?>">
  <div class="container">

    <?php if ( $blog_query->have_posts() ) : ?>

      <?php $show_featured = ( $paged === 1 && ! $active_cat && $blog_query->found_posts > 1 ); ?>

      <?php if ( $show_featured ) : $blog_query->the_post(); ?>
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
      <?php endif; ?>

      <div class="post-grid">
        <?php while ( $blog_query->have_posts() ) : $blog_query->the_post();
          $thumb_url  = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'ah-card' ) : '';
          $post_url   = get_permalink();
          $post_title = get_the_title();
          $cats       = get_the_category();
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
          <?php foreach ( $links as $link ) echo '<li class="pagination__item">' . $link . '</li>'; ?>
        </ul>
      </nav>
      <?php endif; endif; ?>

    <?php else : ?>
    <div class="text-center" style="padding:80px 24px">
      <div style="font-size:3.5rem;margin-bottom:16px">✍️</div>
      <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">No posts yet</h2>
      <p style="color:var(--text-secondary);margin-bottom:28px;max-width:400px;margin-inline:auto">
        <?php echo $active_cat ? 'No posts in this category. Try another or view all.' : 'We\'re working on something great - check back soon.'; ?>
      </p>
      <?php if ( $active_cat ) : ?>
      <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-outline"><?php echo esc_html( AH_LABEL_VIEW_ALL_POSTS ); ?> →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</section>
