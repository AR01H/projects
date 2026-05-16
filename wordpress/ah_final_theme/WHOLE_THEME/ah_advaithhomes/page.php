<?php get_header(); ?>

<main id="main-content">
  <div class="container section--sm">
    <?php ah_breadcrumb(); ?>
  </div>

  <div class="container section">
    <div class="content-layout">
      <article class="prose">
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
          <h1><?php the_title(); ?></h1>
          <?php the_content(); ?>
        <?php endwhile; ?>
      </article>

      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="sidebar-card">
          <div class="sidebar-card__title">Need Expert Help?</div>
          <p style="font-size:.875rem;color:var(--text-secondary);margin-bottom:16px">
            Speak to one of our buyer's agents — free, no-obligation consultation.
          </p>
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary btn-block">
            Book a Free Call →
          </a>
        </div>

        <div class="sidebar-card">
          <div class="sidebar-card__title">Quick Links</div>
          <div style="display:flex;flex-direction:column;gap:8px">
            <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="toc__item">📚 Buying Guides</a>
            <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="toc__item">✦ Our Services</a>
            <a href="<?php echo esc_url( home_url( '/guides/stamp-duty/' ) ); ?>" class="toc__item">📋 Stamp Duty Calculator</a>
            <a href="<?php echo esc_url( home_url( '/guides/mortgage-guide/' ) ); ?>" class="toc__item">🏦 Mortgage Guide</a>
            <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="toc__item">⭐ Client Stories</a>
          </div>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php get_footer(); ?>
