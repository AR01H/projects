<?php get_header(); ?>

<main id="main-content">
  <header class="section section--sm section--alt">
    <div class="container">
      <?php ah_breadcrumb(); ?>
      <h1 class="section__title" style="margin-top:12px"><?php echo esc_html( get_the_archive_title() ); ?></h1>
      <?php if ( get_the_archive_description() ) : ?>
        <p class="section__desc"><?php echo wp_kses_post( get_the_archive_description() ); ?></p>
      <?php endif; ?>
    </div>
  </header>

  <div class="container section">
    <?php if ( have_posts() ) : ?>
      <div class="post-grid">
        <?php while ( have_posts() ) : the_post(); ?>
          <article class="post-card" data-aos="fade-up">
            <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>" class="post-card__img-wrap">
                <?php the_post_thumbnail( 'ah-card' ); ?>
              </a>
            <?php endif; ?>
            <div class="post-card__body">
              <div class="card__meta">
                <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
                <span>·</span>
                <span><?php echo esc_html( ah_reading_time() ); ?></span>
              </div>
              <h2 class="post-card__title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              </h2>
              <p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
              <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-ghost">Read →</a>
            </div>
          </article>
        <?php endwhile; ?>
      </div>
      <?php ah_pagination(); ?>
    <?php else : ?>
      <p><?php esc_html_e( 'No posts found in this category.', 'ah-theme' ); ?></p>
    <?php endif; ?>
  </div>
</main>

<?php get_footer(); ?>
