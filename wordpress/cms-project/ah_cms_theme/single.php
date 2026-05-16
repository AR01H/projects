<?php get_header(); ?>

<main id="main-content">
  <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

  <!-- Post Header -->
  <header class="section section--sm section--alt" style="padding-bottom:0">
    <div class="container">
      <?php ah_breadcrumb(); ?>
      <div class="content-layout" style="padding-top:24px">
        <div>
          <div class="card__meta" style="margin-bottom:16px">
            <?php
            $cats = get_the_category();
            if ( $cats ) :
              foreach ( $cats as $cat ) :
            ?>
              <a href="<?php echo esc_url( get_category_link( $cat ) ); ?>" style="color:var(--accent);font-weight:600">
                <?php echo esc_html( $cat->name ); ?>
              </a>
            <?php endforeach; endif; ?>
            <span>·</span>
            <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
            <span>·</span>
            <span><?php echo esc_html( ah_reading_time() ); ?></span>
          </div>

          <h1 style="font-family:var(--font-display);font-size:clamp(1.8rem,4vw,3rem);font-weight:700;line-height:1.2;letter-spacing:-0.02em;margin-bottom:20px">
            <?php the_title(); ?>
          </h1>

          <p style="font-size:1.1rem;color:var(--text-secondary);line-height:1.7;max-width:640px">
            <?php echo esc_html( wp_trim_words( get_the_excerpt(), 40, '…' ) ); ?>
          </p>
        </div>
        <div></div>
      </div>
    </div>
  </header>

  <!-- Post Content -->
  <div class="container section">
    <div class="content-layout">
      <!-- Main content -->
      <article class="prose" data-aos="fade-up">
        <?php if ( has_post_thumbnail() ) : ?>
          <div style="margin-bottom:32px;border-radius:var(--r-lg);overflow:hidden;aspect-ratio:16/9">
            <?php the_post_thumbnail( 'ah-hero', [ 'style' => 'width:100%;height:100%;object-fit:cover' ] ); ?>
          </div>
        <?php endif; ?>

        <?php the_content(); ?>

        <!-- Tags -->
        <?php
        $tags = get_the_tags();
        if ( $tags ) :
        ?>
        <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap">
          <?php foreach ( $tags as $tag ) : ?>
            <a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>"
               style="display:inline-block;padding:4px 12px;background:var(--bg-alt);border:1px solid var(--border);border-radius:var(--r-full);font-size:.78rem;font-weight:600;color:var(--text-secondary);text-decoration:none">
              #<?php echo esc_html( $tag->name ); ?>
            </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Author bio -->
        <div style="margin-top:40px;padding:24px;background:var(--bg-alt);border-radius:var(--r-lg);display:flex;gap:20px;align-items:flex-start">
          <div style="width:56px;height:56px;border-radius:50%;background:var(--accent);color:white;display:grid;place-items:center;font-family:var(--font-display);font-size:1.2rem;font-weight:700;flex-shrink:0">
            <?php echo esc_html( strtoupper( substr( get_the_author_meta('display_name'), 0, 1 ) ) ); ?>
          </div>
          <div>
            <div style="font-weight:700;margin-bottom:4px"><?php the_author_meta('display_name'); ?></div>
            <div style="font-size:.875rem;color:var(--text-muted)">Buyer's Agent · Advaith Homes</div>
          </div>
        </div>
      </article>

      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="sidebar-card">
          <div class="sidebar-card__title">Free Consultation</div>
          <p style="font-size:.875rem;color:var(--text-secondary);margin-bottom:16px">
            Ready to put this into practice? Speak to one of our buyer's agents — free and no obligation.
          </p>
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary btn-block">Book a Free Call →</a>
        </div>

        <!-- Related posts -->
        <?php
        $related = get_posts( [
          'numberposts'      => 3,
          'category__in'     => wp_get_post_categories( get_the_ID() ),
          'post__not_in'     => [ get_the_ID() ],
          'post_status'      => 'publish',
        ] );
        if ( $related ) :
        ?>
        <div class="sidebar-card">
          <div class="sidebar-card__title">Related Articles</div>
          <div style="display:flex;flex-direction:column;gap:12px">
            <?php foreach ( $related as $rp ) : ?>
              <a href="<?php echo esc_url( get_permalink( $rp ) ); ?>"
                 style="font-size:.875rem;font-weight:500;color:var(--text-secondary);text-decoration:none;display:block;line-height:1.4">
                <?php echo esc_html( get_the_title( $rp ) ); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>

  <?php endwhile; ?>
</main>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
