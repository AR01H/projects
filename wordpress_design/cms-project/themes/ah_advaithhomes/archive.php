<?php get_header(); ?>

<?php
$archive_title = get_the_archive_title();
$archive_desc  = get_the_archive_description();

// Build a clean title - strip the "Category: / Tag: " prefix WP adds
$clean_title = preg_replace( '/^[^:]+:\s*/', '', strip_tags( $archive_title ) );

get_template_part( 'components/page-header', null, [
	'eyebrow'    => is_category() ? 'Category' : ( is_tag() ? 'Tag' : 'Archive' ),
	'title'      => $clean_title,
	'desc'       => $archive_desc ? wp_strip_all_tags( $archive_desc ) : '',
	'breadcrumb' => [
		[ 'Home', home_url( '/' ) ],
		[ $clean_title, '' ],
	],
] );
?>

<main id="main-content">
  <div class="container section">
    <div class="content-layout">

      <!-- Posts -->
      <div>
        <?php if ( have_posts() ) : ?>
          <div class="post-grid">
            <?php while ( have_posts() ) : the_post();
              $thumb_url  = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'ah-card' ) : '';
              $post_url   = get_permalink();
              $post_title = get_the_title();
              $cats       = get_the_category();
            ?>
            <article class="post-card post-card--overlay" data-aos="fade-up">
              <div class="post-card__bg"<?php if ( $thumb_url ) echo ' style="background-image:url(' . esc_url( $thumb_url ) . ')"'; ?>></div>

              <div class="post-card__content">
                <div class="post-card__top">
                  <?php if ( $cats ) : ?>
                  <span class="post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
                  <?php else : ?>
                  <span></span>
                  <?php endif; ?>

                  <div class="post-share">
                    <button class="post-share__btn" aria-label="Share this post" aria-expanded="false">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                      </svg>
                    </button>
                    <div class="post-share__popover" role="dialog" aria-label="Share options">
                      <span class="post-share__label">Share</span>
                      <div class="post-share__icons">
                        <a href="https://wa.me/?text=<?php echo rawurlencode( $post_title . ' ' . $post_url ); ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="post-share__icon post-share__icon--wa" aria-label="Share on WhatsApp">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                        </a>
                        <button class="post-share__icon post-share__icon--copy"
                                data-url="<?php echo esc_attr( $post_url ); ?>"
                                aria-label="Copy link">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                        </button>
                        <button class="post-share__icon post-share__icon--native"
                                data-url="<?php echo esc_attr( $post_url ); ?>"
                                data-title="<?php echo esc_attr( $post_title ); ?>"
                                aria-label="More share options">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="post-card__info">
                  <div class="card__meta">
                    <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
                    <span>·</span>
                    <span><?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span>
                  </div>
                  <h2 class="post-card__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                  </h2>
                  <p class="post-card__excerpt">
                    <?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?>
                  </p>
                  <a href="<?php the_permalink(); ?>" class="post-card__read-btn">
                    Read <span aria-hidden="true">→</span>
                  </a>
                </div>
              </div>
            </article>
            <?php endwhile; ?>
          </div>

          <?php ah_pagination(); ?>

        <?php else : ?>
          <div class="text-center" style="padding:60px 24px">
            <div style="font-size:3rem;margin-bottom:16px">📂</div>
            <h2 style="font-family:var(--font-display);font-size:1.4rem;margin-bottom:10px">No posts found</h2>
            <p style="color:var(--text-secondary)">Nothing published in this category yet - check back soon.</p>
            <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="btn btn-outline" style="margin-top:20px">Browse All Posts →</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Sidebar -->
      <aside class="sidebar" aria-label="Archive sidebar">
        <div class="sidebar-card sidebar-card--accent">
          <div class="sidebar-card__icon">💬</div>
          <div class="sidebar-card__title">Get in Touch</div>
          <p>Have a question? Drop us a message and we'll get back to you shortly.</p>
        </div>
        <div class="sidebar-card" style="margin-top:16px">
          <?php echo do_shortcode( '[ah_form id="2"]' ); ?>
        </div>
      </aside>

    </div>
  </div>
</main>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
