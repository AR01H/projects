<?php
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$query = get_search_query();
?>
<main id="main-content">

  <section class="page-hero page-hero--sm">
    <div class="container">
      <h1 class="reveal">
        <?php
        printf(
          /* translators: %s search term */
          esc_html__( 'Search results for: %s', 'ah-theme' ),
          '<span style="color:var(--accent)">' . esc_html( $query ) . '</span>'
        );
        ?>
      </h1>
      <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="search-form reveal reveal-delay-1">
        <input type="search" name="s" class="form-control" value="<?php echo esc_attr( $query ); ?>"
               placeholder="<?php esc_attr_e( 'Search guides and articles…', 'ah-theme' ); ?>" required>
        <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Search', 'ah-theme' ); ?></button>
      </form>
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
            $delay = [ '', 'reveal-delay-1', 'reveal-delay-2' ][ $i % 3 ];
            $thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) ?: ah_unsplash( '1560518883-ce09059eeffa', 600, 400 );
            $cats  = get_the_category();
            $cat   = ! empty( $cats ) ? $cats[0]->name : '';
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
                </div>
                <h3 class="blog-card__title">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
              </div>
            </article>
          <?php
            $i++;
          endwhile;
          ?>
        </div>
        <div style="margin-top:48px">
          <?php the_posts_pagination( [ 'prev_text' => '←', 'next_text' => '→' ] ); ?>
        </div>
      <?php else : ?>
        <div style="text-align:center;padding:80px 0">
          <div style="font-size:3rem;margin-bottom:16px">🔍</div>
          <h3><?php esc_html_e( 'No results found', 'ah-theme' ); ?></h3>
          <p style="color:var(--text-muted);margin-bottom:32px">
            <?php printf( esc_html__( 'We couldn\'t find anything matching "%s". Try a different search term.', 'ah-theme' ), esc_html( $query ) ); ?>
          </p>
          <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-primary">
            <?php esc_html_e( 'Browse All Guides', 'ah-theme' ); ?>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </section>

</main>
<?php get_template_part( 'parts/footer' ); ?>
