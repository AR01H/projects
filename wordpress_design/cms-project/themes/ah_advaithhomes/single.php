<?php get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

<!-- ── Post Header ────────────────────────────────────────────────────────── -->
<header class="post-header" aria-label="Post header">
  <div class="container">
    <?php ah_breadcrumb(); ?>

    <div class="post-header__meta">
      <?php $cats = get_the_category(); foreach ( $cats as $cat ) : ?>
        <a href="<?php echo esc_url( get_category_link( $cat ) ); ?>" class="post-header__cat">
          <?php echo esc_html( $cat->name ); ?>
        </a>
      <?php endforeach; ?>
      <span class="post-header__dot" aria-hidden="true">·</span>
      <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
        <?php echo esc_html( get_the_date( 'j M Y' ) ); ?>
      </time>
      <span class="post-header__dot" aria-hidden="true">·</span>
      <span><?php echo esc_html( ah_reading_time() ); ?></span>
    </div>

    <h1 class="post-header__title"><?php the_title(); ?></h1>

    <?php $excerpt = wp_trim_words( get_the_excerpt(), 35, '…' ); if ( $excerpt ) : ?>
    <p class="post-header__excerpt"><?php echo esc_html( $excerpt ); ?></p>
    <?php endif; ?>

    <!-- Author row -->
    <div class="post-header__author">
      <div class="post-header__avatar">
        <?php echo esc_html( strtoupper( substr( get_the_author_meta( 'display_name' ), 0, 1 ) ) ); ?>
      </div>
      <div>
        <div class="post-header__author-name"><?php the_author_meta( 'display_name' ); ?></div>
        <div class="post-header__author-role">Buyer's Agent · Advaith Homes</div>
      </div>
    </div>
  </div>
</header>

<!-- ── Featured Image ────────────────────────────────────────────────────── -->
<?php if ( has_post_thumbnail() ) : ?>
<div class="post-featured-img">
  <div class="container">
    <?php the_post_thumbnail( 'ah-hero', [ 'class' => 'post-featured-img__img' ] ); ?>
  </div>
</div>
<?php endif; ?>

<!-- ── Post Body ─────────────────────────────────────────────────────────── -->
<main id="main-content">
  <div class="container section">
    <div class="content-layout">

      <!-- Article -->
      <article class="prose" id="article-body">
        <?php the_content(); ?>

        <!-- Tags -->
        <?php $tags = get_the_tags(); if ( $tags ) : ?>
        <div class="post-tags">
          <?php foreach ( $tags as $tag ) : ?>
            <a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" class="post-tag">
              #<?php echo esc_html( $tag->name ); ?>
            </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Share row -->
        <div class="post-share">
          <span class="post-share__label">Share this article:</span>
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="Share on X/Twitter">𝕏</a>
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode( get_permalink() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="Share on LinkedIn">in</a>
          <button class="post-share__btn" data-copy="<?php echo esc_attr( get_permalink() ); ?>" aria-label="Copy link">🔗</button>
        </div>
      </article>

      <!-- Sidebar -->
      <aside class="sidebar" aria-label="Article sidebar">

        <!-- CTA card -->
        <div class="sidebar-card sidebar-card--accent">
          <div class="sidebar-card__icon">💬</div>
          <div class="sidebar-card__title">Free Consultation</div>
          <p>Ready to put this into practice? Speak to a buyer's agent — free, no obligation.</p>
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary btn-block">
            Book a Free Call →
          </a>
        </div>


        <!-- Quick links -->
        <div class="sidebar-card">
          <div class="sidebar-card__title">Useful Links</div>
          <div class="toc">
            <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="toc__item">📚 All Buying Guides</a>
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

<?php endwhile; ?>

<!-- ── Related Articles ───────────────────────────────────────────────────── -->
<?php
$_related = get_posts( [
  'numberposts'  => 3,
  'category__in' => wp_get_post_categories( get_the_ID() ),
  'post__not_in' => [ get_the_ID() ],
  'post_status'  => 'publish',
] );
if ( $_related ) :
?>
<section class="section section--alt" aria-label="Related articles">
  <div class="container">
    <div class="section__header" style="margin-bottom:32px">
      <span class="section__eyebrow">Keep Reading</span>
      <h2 class="section__title" style="font-size:1.5rem">Related Articles</h2>
    </div>
    <div class="post-grid">
      <?php foreach ( $_related as $rp ) : ?>
      <article class="post-card">
        <?php if ( has_post_thumbnail( $rp ) ) : ?>
        <a href="<?php echo esc_url( get_permalink( $rp ) ); ?>" class="post-card__img-wrap">
          <?php echo get_the_post_thumbnail( $rp, 'ah-card' ); ?>
        </a>
        <?php else : ?>
        <a href="<?php echo esc_url( get_permalink( $rp ) ); ?>" class="post-card__img-wrap"
           style="background:var(--bg-alt);display:flex;align-items:center;justify-content:center;min-height:160px;font-size:2.5rem">
          📰
        </a>
        <?php endif; ?>
        <div class="post-card__body">
          <?php $rp_cats = get_the_category( $rp->ID ); if ( $rp_cats ) : ?>
          <div class="post-card__cat"><?php echo esc_html( $rp_cats[0]->name ); ?></div>
          <?php endif; ?>
          <div class="card__meta">
            <span><?php echo esc_html( get_the_date( 'j M Y', $rp ) ); ?></span>
            <span>·</span>
            <span><?php echo esc_html( ah_reading_time( $rp->ID ) ); ?></span>
          </div>
          <h3 class="post-card__title">
            <a href="<?php echo esc_url( get_permalink( $rp ) ); ?>"><?php echo esc_html( get_the_title( $rp ) ); ?></a>
          </h3>
          <p class="post-card__excerpt">
            <?php echo esc_html( wp_trim_words( get_the_excerpt( $rp ), 20, '…' ) ); ?>
          </p>
          <a href="<?php echo esc_url( get_permalink( $rp ) ); ?>" class="btn btn-sm btn-ghost" style="margin-top:auto">
            Read →
          </a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
