<?php
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$archive_title = get_the_archive_title();
$archive_desc  = get_the_archive_description();
?>
<main id="main-content">

  <section class="page-hero page-hero--sm">
    <div class="container">
      <div class="eyebrow reveal"><?php esc_html_e( 'Articles', 'ah-theme' ); ?></div>
      <h1 class="reveal reveal-delay-1"><?php echo esc_html( $archive_title ); ?></h1>
      <?php if ( $archive_desc ) : ?>
        <p class="reveal reveal-delay-2"><?php echo wp_kses_post( $archive_desc ); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <?php if ( have_posts() ) : ?>
        <div class="blog-grid">
          <?php
          $i = 0;
          while ( have_posts() ) :
            the_post();
            $delay   = [ '', 'reveal-delay-1', 'reveal-delay-2' ][ $i % 3 ];
            $thumb   = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) ?: ah_unsplash( '1560518883-ce09059eeffa', 600, 400 );
            $cats    = get_the_category();
            $cat     = ! empty( $cats ) ? $cats[0]->name : '';
            $words   = str_word_count( strip_tags( get_the_content() ) );
            $read    = ceil( $words / 200 ) . ' min';
          ?>
            <article class="blog-card reveal <?php echo esc_attr( $delay ); ?>">
              <a href="<?php the_permalink(); ?>" class="blog-card__img-wrap" tabindex="-1">
                <img src="<?php echo esc_url( $thumb ); ?>"
                     alt="<?php the_title_attribute(); ?>"
                     loading="lazy"
                     class="blog-card__img">
                <?php if ( $cat ) : ?>
                  <span class="blog-card__cat"><?php echo esc_html( $cat ); ?></span>
                <?php endif; ?>
              </a>
              <div class="blog-card__body">
                <div class="blog-card__meta">
                  <time class="blog-card__date"><?php the_date( 'j M Y' ); ?></time>
                  <span class="blog-card__read">⏱ <?php echo esc_html( $read ); ?></span>
                </div>
                <h3 class="blog-card__title">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
                <a href="<?php the_permalink(); ?>" class="blog-card__link">
                  <?php esc_html_e( 'Read Article', 'ah-theme' ); ?>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                  </svg>
                </a>
              </div>
            </article>
          <?php
            $i++;
          endwhile;
          ?>
        </div>
        <div style="margin-top:48px">
          <?php the_posts_pagination( [ 'prev_text' => '← ' . __( 'Previous', 'ah-theme' ), 'next_text' => __( 'Next', 'ah-theme' ) . ' →', 'class' => 'ah-pagination' ] ); ?>
        </div>
      <?php else : ?>
        <p class="text-center"><?php esc_html_e( 'No articles found.', 'ah-theme' ); ?></p>
      <?php endif; ?>
    </div>
  </section>

</main>
<?php get_template_part( 'parts/footer' ); ?>
