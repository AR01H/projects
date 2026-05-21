<?php get_header(); ?>

<main id="main-content">
  <header class="section section--sm section--alt">
    <div class="container">
      <?php ah_breadcrumb(); ?>
      <h1 class="section__title" style="margin-top:12px">
        <?php
        printf(
          esc_html__( 'Search results for: %s', 'ah-theme' ),
          '<em>' . esc_html( get_search_query() ) . '</em>'
        );
        ?>
      </h1>

      <!-- Search form -->
      <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" style="margin-top:20px;max-width:480px">
        <div style="display:flex;gap:8px">
          <input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
                 placeholder="Search guides, news, topics…"
                 class="form-control">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </form>
    </div>
  </header>

  <div class="container section">
    <?php if ( have_posts() ) : ?>
      <p style="margin-bottom:24px;color:var(--text-muted)">
        <?php printf( esc_html__( 'Found %d results', 'ah-theme' ), $wp_query->found_posts ); ?>
      </p>
      <div class="post-grid">
        <?php while ( have_posts() ) : the_post(); ?>
          <article class="post-card" data-aos="fade-up">
            <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>" class="post-card__img-wrap">
                <?php the_post_thumbnail( 'ah-card' ); ?>
              </a>
            <?php endif; ?>
            <div class="post-card__body">
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
      <div style="text-align:center;padding:80px 0">
        <div style="font-size:3rem;margin-bottom:16px">🔍</div>
        <h2 style="font-family:var(--font-display);margin-bottom:12px">No results found</h2>
        <p style="color:var(--text-muted);margin-bottom:24px">
          Try a different search term, or browse our guides below.
        </p>
        <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-primary">Browse All Guides →</a>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php get_footer(); ?>
