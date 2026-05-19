<?php get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

<?php
$cats      = get_the_category();
$cat       = $cats ? $cats[0] : null;
$exc       = wp_trim_words( get_the_excerpt(), 30, '…' );
$crumbs    = [
	[ 'Home', home_url( '/' ) ],
];
if ( $cat ) $crumbs[] = [ $cat->name, get_category_link( $cat ) ];
$crumbs[] = [ get_the_title(), '' ];
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => $cat ? $cat->name : 'Article',
	'title'      => get_the_title(),
	'desc'       => $exc,
	'badge'      => ah_reading_time(),
	'breadcrumb' => $crumbs,
] ); ?>

<!-- ── Featured Image ── full bleed ──────────────────────────────────────── -->
<?php if ( has_post_thumbnail() ) : ?>
<div class="sp-hero-img">
  <?php the_post_thumbnail( 'full', [ 'class' => 'sp-hero-img__img', 'loading' => 'eager' ] ); ?>
  <div class="sp-hero-img__meta">
    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
      <?php echo esc_html( get_the_date( 'j M Y' ) ); ?>
    </time>
    <span>·</span>
    <span><?php echo esc_html( ah_reading_time() ); ?></span>
  </div>
</div>
<?php endif; ?>

<!-- ── Article + Sidebar ─────────────────────────────────────────────────── -->
<main id="main-content">
  <div class="container section" style="padding-top:clamp(32px,4vw,56px)">
    <div class="content-layout">

      <!-- Article -->
      <article class="prose" id="article-body">
        <?php the_content(); ?>

        <?php $tags = get_the_tags(); if ( $tags ) : ?>
        <div class="post-tags">
          <?php foreach ( $tags as $tag ) : ?>
            <a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" class="post-tag">
              #<?php echo esc_html( $tag->name ); ?>
            </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="post-share">
          <span class="post-share__label">Share:</span>
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="Share on X">𝕏</a>
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode( get_permalink() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="Share on LinkedIn">in</a>
          <button class="post-share__btn" data-copy="<?php echo esc_attr( get_permalink() ); ?>" aria-label="Copy link">🔗</button>
        </div>
      </article>

      <!-- Sidebar -->
      <aside class="sidebar" aria-label="Article sidebar">
        <div class="sidebar-card sidebar-card--accent">
          <div class="sidebar-card__icon">💬</div>
          <div class="sidebar-card__title">Free Consultation</div>
          <p>Ready to put this into practice? Speak to a buyer's agent — free, no obligation.</p>
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-gold btn-block">
            Book a Free Call →
          </a>
        </div>
        <div class="sidebar-card">
          <div class="sidebar-card__title">Useful Links</div>
          <div class="toc">
            <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>"                   class="toc__item">📚 All Buying Guides</a>
            <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"                 class="toc__item">✦ Our Services</a>
            <a href="<?php echo esc_url( home_url( '/guides/stamp-duty/' ) ); ?>"        class="toc__item">📋 Stamp Duty Calculator</a>
            <a href="<?php echo esc_url( home_url( '/guides/mortgage-guide/' ) ); ?>"   class="toc__item">🏦 Mortgage Guide</a>
            <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>"           class="toc__item">⭐ Client Stories</a>
          </div>
        </div>
      </aside>

    </div>
  </div>
</main>

<?php endwhile; ?>

<!-- ── Related Articles ───────────────────────────────────────────────────── -->
<?php
// Try same category first; pad with any posts if not enough
$_current_cats = wp_get_post_categories( get_the_ID() );
$_related = get_posts( [
  'numberposts'  => 5,
  'category__in' => $_current_cats,
  'post__not_in' => [ get_the_ID() ],
  'post_status'  => 'publish',
] );
if ( count( $_related ) < 5 ) {
  $have_ids    = array_merge( [ get_the_ID() ], wp_list_pluck( $_related, 'ID' ) );
  $_pad        = get_posts( [
    'numberposts'  => 5 - count( $_related ),
    'post__not_in' => $have_ids,
    'post_status'  => 'publish',
    'orderby'      => 'date',
    'order'        => 'DESC',
  ] );
  $_related = array_merge( $_related, $_pad );
}
if ( $_related ) :
?>
<section class="section section--pattern" aria-label="Related articles">
  <div class="container">
    <div class="section__header" style="margin-bottom:32px">
      <span class="section__eyebrow">Keep Reading</span>
      <h2 class="section__title" style="font-size:1.5rem">Related Articles</h2>
    </div>
    <div class="related-grid">
      <?php foreach ( $_related as $rp ) :
        $rc        = get_the_category( $rp->ID );
        $thumb_url = get_the_post_thumbnail_url( $rp->ID, 'large' );
      ?>
      <a href="<?php echo esc_url( get_permalink( $rp ) ); ?>" class="ra-card">
        <?php if ( $thumb_url ) : ?>
          <img class="ra-card__img" src="<?php echo esc_url( $thumb_url ); ?>"
               alt="<?php echo esc_attr( get_the_title( $rp ) ); ?>" loading="lazy">
        <?php else : ?>
          <div class="ra-card__img ra-card__img--fallback">📰</div>
        <?php endif; ?>
        <div class="ra-card__overlay">
          <div class="ra-card__top">
            <?php if ( $rc ) : ?>
              <span class="ra-card__cat"><?php echo esc_html( $rc[0]->name ); ?></span>
            <?php endif; ?>
          </div>
          <div class="ra-card__bottom">
            <div class="ra-card__meta">
              <?php echo esc_html( get_the_date( 'j M Y', $rp ) ); ?>
              <span>·</span>
              <?php echo esc_html( ah_reading_time( $rp->ID ) ); ?>
            </div>
            <h3 class="ra-card__title"><?php echo esc_html( get_the_title( $rp ) ); ?></h3>
            <span class="ra-card__btn">Read Article →</span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>

<style>
/* ── Full-bleed featured image ───────────────────────────────────────────── */
.sp-hero-img {
  position: relative;
  width: 100%;
  max-height: 560px;
  overflow: hidden;
  display: block;
}
.sp-hero-img__img {
  width: 100%;
  height: 100%;
  max-height: 560px;
  object-fit: contain;
  display: block;
}
.sp-hero-img__meta {
  position: absolute;
  bottom: 16px;
  right: 20px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: .78rem;
  font-weight: 500;
  color: #fff;
  background: rgba(0,0,0,.45);
  backdrop-filter: blur(6px);
  border-radius: 999px;
  padding: 5px 14px;
}
@media (max-width: 600px) {
  .sp-hero-img { max-height: 260px; }
  .sp-hero-img__img { max-height: 260px; }
}
</style>
