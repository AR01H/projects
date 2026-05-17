<?php
get_header();
?>
<main class="container" style="padding-top:var(--section-py);padding-bottom:var(--section-py)">
  <?php if ( have_posts() ) : ?>
    <div class="post-grid">
      <?php while ( have_posts() ) : the_post(); ?>
        <article class="post-card">
          <?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>" class="post-card__img-wrap">
              <?php the_post_thumbnail( 'ah-card' ); ?>
            </a>
          <?php endif; ?>
          <div class="post-card__body">
            <h2 class="post-card__title">
              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>
            <p class="post-card__excerpt"><?php echo esc_html( ah_excerpt() ); ?></p>
            <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline">Read more →</a>
          </div>
        </article>
      <?php endwhile; ?>
    </div>
    <?php ah_pagination(); ?>
  <?php else : ?>
    <p><?php esc_html_e( 'No posts found.', 'ah-theme' ); ?></p>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
