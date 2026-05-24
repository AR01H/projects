<?php get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

<?php
$cats      = get_the_category();
$cat       = $cats ? $cats[0] : null;
$exc       = wp_trim_words( get_the_excerpt(), 30, '…' );

// Build breadcrumb: Home › Parent Term › Category › Post
$_pt_for_cat = $cat ? ah_get_parent_term_for_cat( $cat->slug ) : null;
$crumbs      = [ [ 'Home', home_url( '/' ) ] ];
if ( $_pt_for_cat ) {
	$crumbs[] = [ $_pt_for_cat->name, home_url( '/' . $_pt_for_cat->slug . '/' ) ];
	$crumbs[] = [ $cat->name, home_url( '/' . $_pt_for_cat->slug . '/' . $cat->slug . '/' ) ];
} elseif ( $cat ) {
	$crumbs[] = [ $cat->name, get_category_link( $cat ) ];
}
$crumbs[] = [ get_the_title(), '' ];
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => $cat ? $cat->name : 'Article',
	'title'      => get_the_title(),
	'desc'       => $exc,
	'badge'      => ah_reading_time(),
	'breadcrumb' => $crumbs,
] ); ?>

<!-- ── Article + Sidebar ─────────────────────────────────────────────────── -->
<main id="main-content">
  <div class="container section" style="padding-top:clamp(28px,3.5vw,48px)">
    <div class="content-layout">

      <!-- Article -->
      <article class="prose" id="article-body">

        <?php if ( has_post_thumbnail() ) : ?>
        <figure class="sp-feat-img">
          <?php the_post_thumbnail( 'large', [ 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
        </figure>
        <?php endif; ?>

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
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="Share on X">𝕏</a>
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode( get_permalink() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="Share on LinkedIn">in</a>

          <button class="post-share__icon post-share__icon--native"
                  data-url="<?php echo esc_attr( get_permalink() ); ?>"
                  data-title="<?php echo esc_attr( get_the_title() ); ?>"
                  aria-label="More share options">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
          </button>
        </div>
        <div style="clear:both"></div><!-- clears sp-feat-img float -->
      </article>

      <!-- Sidebar -->
      <aside class="sidebar" aria-label="Article sidebar">

        <!-- Table of Contents — populated by JS from article headings -->
        <div class="sidebar-card sp-toc-card" id="sp-toc" hidden>
          <div class="sidebar-card__title sp-toc__heading">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <?php esc_html_e( 'Contents', 'ah-theme' ); ?>
          </div>
          <nav class="sp-toc__nav" aria-label="<?php esc_attr_e( 'Article sections', 'ah-theme' ); ?>"></nav>
        </div>

        <!-- Consultation CTA -->
        <div class="sidebar-card sidebar-card--accent">
          <div class="sidebar-card__icon">💬</div>
          <div class="sidebar-card__title"><?php esc_html_e( 'Free Consultation', 'ah-theme' ); ?></div>
          <p><?php esc_html_e( 'Ready to put this into practice? Speak to a buyer\'s agent - free, no obligation.', 'ah-theme' ); ?></p>
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-gold btn-block">
            <?php esc_html_e( 'Book a Free Call →', 'ah-theme' ); ?>
          </a>
        </div>


        <!-- More in same category — from DB -->
        <?php
        $_sb_more = $cat ? get_posts( [
          'numberposts'  => 3,
          'category__in' => [ $cat->term_id ],
          'post__not_in' => [ get_the_ID() ],
          'post_status'  => 'publish',
          'orderby'      => 'date',
          'order'        => 'DESC',
        ] ) : [];
        ?>
        <?php if ( $_sb_more ) : ?>
        <div class="sidebar-card sp-more-card">
          <div class="sidebar-card__title">
            <?php printf( esc_html__( 'More in %s', 'ah-theme' ), esc_html( $cat->name ) ); ?>
          </div>
          <ul class="sp-more-list">
            <?php foreach ( $_sb_more as $mp ) :
              $mp_thumb = get_the_post_thumbnail_url( $mp->ID, 'thumbnail' )
                       ?: get_the_post_thumbnail_url( $mp->ID, 'medium' );
            ?>
            <li class="sp-more-item">
              <a href="<?php echo esc_url( get_permalink( $mp ) ); ?>" class="sp-more-item__link">
                <?php if ( $mp_thumb ) : ?>
                  <img class="sp-more-item__img" src="<?php echo esc_url( $mp_thumb ); ?>"
                       alt="<?php echo esc_attr( get_the_title( $mp ) ); ?>" loading="lazy">
                <?php else : ?>
                  <div class="sp-more-item__img sp-more-item__img--ph" aria-hidden="true"><?php echo esc_html( get_the_title( $mp )[0] ) ?></div>
                <?php endif; ?>
                <span class="sp-more-item__title"><?php echo esc_html( get_the_title( $mp ) ); ?></span>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php
          $_more_cat_url = $_pt_for_cat
            ? home_url( '/' . $_pt_for_cat->slug . '/' . $cat->slug . '/' )
            : get_category_link( $cat );
          ?>
          <a href="<?php echo esc_url( $_more_cat_url ); ?>" class="sp-more-all">
            <?php printf( esc_html__( 'All %s →', 'ah-theme' ), esc_html( $cat->name ) ); ?>
          </a>
        </div>
        <?php endif; ?>

        <!-- Highlight Links — shown only when defined on this post -->
        <?php
        $_hl_raw   = get_post_meta( get_the_ID(), '_ah_highlight_links', true );
        $_hl_links = json_decode( $_hl_raw ?: '[]', true );
        if ( ! is_array( $_hl_links ) ) $_hl_links = [];
        $_hl_links = array_values( array_filter( $_hl_links, fn( $l ) => ! empty( $l['name'] ) || ! empty( $l['url'] ) ) );
        ?>
        <?php if ( ! empty( $_hl_links ) ) : ?>
        <div class="sidebar-card ah-hl-card">
          <div class="sidebar-card__title"><?php esc_html_e( 'Highlight Links', 'ah-theme' ); ?></div>
          <div class="ah-hl-buttons">
            <?php foreach ( $_hl_links as $_hl ) : ?>
            <a href="<?php echo esc_url( $_hl['url'] ?? '#' ); ?>" class="ah-hl-btn">
              <?php echo esc_html( $_hl['name'] ?? $_hl['url'] ); ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Useful Links -->
        <div class="sidebar-card">
          <div class="sidebar-card__title"><?php esc_html_e( 'Useful Links', 'ah-theme' ); ?></div>
          <div class="toc">
            <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>"         class="toc__item">📚 <?php esc_html_e( 'All Buying Guides', 'ah-theme' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"       class="toc__item">✦ <?php esc_html_e( 'Our Services', 'ah-theme' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="toc__item">⭐ <?php esc_html_e( 'Client Stories', 'ah-theme' ); ?></a>
          </div>
        </div>

      </aside>

    </div>
  </div>
</main>

<?php endwhile; ?>

<?php
// ── Related Articles — 1 suggested first, rest random same-cat, pad if < 3 ───
$_current_cat_ids = wp_get_post_categories( get_the_ID() );

// 1 suggested post from same category (random pick if multiple)
$_suggested = get_posts( [
  'numberposts'  => 1,
  'category__in' => $_current_cat_ids,
  'post__not_in' => [ get_the_ID() ],
  'post_status'  => 'publish',
  'orderby'      => 'rand',
  'meta_key'     => '_ah_is_suggested',
  'meta_value'   => '1',
] );

// Fill remaining slots with random posts from same category
$_excl = array_merge( [ get_the_ID() ], wp_list_pluck( $_suggested, 'ID' ) );
$_rand_cat = get_posts( [
  'numberposts'  => 3 - count( $_suggested ),
  'category__in' => $_current_cat_ids,
  'post__not_in' => $_excl,
  'post_status'  => 'publish',
  'orderby'      => 'rand',
] );

$_related = array_merge( $_suggested, $_rand_cat );

// Pad with random posts from any category if still under 3
if ( count( $_related ) < 3 ) {
  $_have = array_merge( [ get_the_ID() ], wp_list_pluck( $_related, 'ID' ) );
  $_pad  = get_posts( [
    'numberposts'  => 3 - count( $_related ),
    'post__not_in' => $_have,
    'post_status'  => 'publish',
    'orderby'      => 'rand',
  ] );
  $_related = array_merge( $_related, $_pad );
}

// ── You Might Also Like — popular from other categories, fill with others ────
$_exclude_ids = array_merge( [ get_the_ID() ], wp_list_pluck( $_related, 'ID' ) );

// Step 1: popular posts from different categories (up to 3)
$_guides = get_posts( [
  'numberposts'      => 3,
  'post__not_in'     => $_exclude_ids,
  'post_status'      => 'publish',
  'category__not_in' => $_current_cat_ids,
  'orderby'          => 'rand',
  'meta_key'         => '_ah_is_popular',
  'meta_value'       => '1',
] );

// Step 2: if fewer than 3, fill with other-category posts (non-popular)
if ( count( $_guides ) < 3 ) {
  $_guides_excl = array_merge( $_exclude_ids, wp_list_pluck( $_guides, 'ID' ) );
  $_guides_fill = get_posts( [
    'numberposts'      => 3 - count( $_guides ),
    'post__not_in'     => $_guides_excl,
    'post_status'      => 'publish',
    'category__not_in' => $_current_cat_ids,
    'orderby'          => 'rand',
  ] );
  $_guides = array_merge( $_guides, $_guides_fill );
}

// Step 3: last resort — any post if still under 3
if ( count( $_guides ) < 3 ) {
  $_guides_excl = array_merge( $_exclude_ids, wp_list_pluck( $_guides, 'ID' ) );
  $_guides_any  = get_posts( [
    'numberposts'  => 3 - count( $_guides ),
    'post__not_in' => $_guides_excl,
    'post_status'  => 'publish',
    'orderby'      => 'rand',
  ] );
  $_guides = array_merge( $_guides, $_guides_any );
}
?>

<?php if ( $_related ) :
  get_template_part( 'components/sp-related-articles', null, [
    'posts' => $_related,
    'cat'   => $cat,
  ] );
endif; ?>

<?php if ( $_guides ) :
  get_template_part( 'components/sp-guide-suggestions', null, [
    'posts' => $_guides,
  ] );
endif; ?>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
