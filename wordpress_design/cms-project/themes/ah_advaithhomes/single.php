<?php get_header(); ?>

<!-- Reading progress bar (fills as the article is scrolled) -->
<div class="ah-readbar" aria-hidden="true"><span class="ah-readbar__fill" id="ahReadbarFill"></span></div>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

<?php
$cats = get_the_category();
$cat  = $cats ? $cats[0] : null;
$exc  = wp_trim_words( get_the_excerpt(), 30, '…' );

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
	'eyebrow'    => $cat ? $cat->name : AH_TERM_SINGULAR,
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

        <?php get_template_part( 'components/article-byline' ); ?>

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
          <span class="post-share__label">Share</span>
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( TXT_SHARE_ON_X ); ?>">𝕏</a>
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode( get_permalink() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( TXT_SHARE_ON_LINKEDIN ); ?>">in</a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="Share on Facebook">f</a>
          <a href="https://wa.me/?text=<?php echo urlencode( get_the_title() . ' ' . get_permalink() ); ?>"
             class="post-share__btn" target="_blank" rel="noopener" aria-label="Share on WhatsApp">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.978-1.297zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414z"/></svg>
          </a>
          <button type="button" class="post-share__btn post-share__btn--copy" data-url="<?php echo esc_attr( get_permalink() ); ?>" aria-label="Copy link">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
          </button>

          <button class="post-share__icon post-share__icon--native"
                  data-url="<?php echo esc_attr( get_permalink() ); ?>"
                  data-title="<?php echo esc_attr( get_the_title() ); ?>"
                  aria-label="<?php echo esc_attr( TXT_MORE_SHARE_OPTIONS ); ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
          </button>
        </div>
        <div style="clear:both"></div><!-- clears sp-feat-img float -->

        <?php
        $ah_prev = get_previous_post();
        $ah_next = get_next_post();
        if ( $ah_prev || $ah_next ) : ?>
        <nav class="ah-prevnext" aria-label="<?php printf( esc_attr( '%s navigation' ), AH_TERM_SINGULAR ); ?>">
          <?php if ( $ah_prev ) : ?>
            <a class="ah-prevnext__link ah-prevnext__link--prev" href="<?php echo esc_url( get_permalink( $ah_prev ) ); ?>">
              <span class="ah-prevnext__dir">&larr; Previous</span>
              <span class="ah-prevnext__title"><?php echo esc_html( get_the_title( $ah_prev ) ); ?></span>
            </a>
          <?php else : ?><span aria-hidden="true"></span><?php endif; ?>
          <?php if ( $ah_next ) : ?>
            <a class="ah-prevnext__link ah-prevnext__link--next" href="<?php echo esc_url( get_permalink( $ah_next ) ); ?>">
              <span class="ah-prevnext__dir">Next &rarr;</span>
              <span class="ah-prevnext__title"><?php echo esc_html( get_the_title( $ah_next ) ); ?></span>
            </a>
          <?php endif; ?>
        </nav>
        <?php endif; ?>
      </article>

      <!-- Sidebar -->
      <aside class="sidebar" aria-label="<?php printf('%s sidebar' , AH_TERM_SINGULAR ); ?>">

        <!-- Table of Contents - populated by JS from article headings -->
        <div class="sidebar-card sp-toc-card" id="sp-toc" hidden>
          <div class="sidebar-card__title sp-toc__heading">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <?php echo esc_html( TXT_CONTENTS ); ?>
          </div>
          <nav class="sp-toc__nav" aria-label="<?php printf( esc_attr( "%s sections" ), AH_TERM_SINGULAR ); ?>"></nav>
        </div>

        <!-- Consultation CTA -->
        <div class="sidebar-card sidebar-card--accent">
          <div class="sidebar-card__icon">💬</div>
          <div class="sidebar-card__title"><?php echo esc_html( TXT_FREE_CONSULTATION ); ?></div>
          <p><?php echo esc_html( TXT_READY_TO_PUT_THIS_INTO_PRACTICE_SPEAK_TO_A_BUYER_S ); ?></p>
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-gold btn-block">
            <?php echo esc_html( TXT_BOOK_A_FREE_CALL ); ?>
          </a>
        </div>


        <!-- More in same category - from DB -->
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
            <?php printf( 'More in %s', esc_html( $cat->name ) ); ?>
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
            <?php printf( 'All %s →', esc_html( $cat->name ) ); ?>
          </a>
        </div>
        <?php endif; ?>

        <!-- Highlight Links - shown only when defined on this post -->
        <?php
        $_hl_raw   = get_post_meta( get_the_ID(), '_ah_highlight_links', true );
        $_hl_links = json_decode( $_hl_raw ?: '[]', true );
        if ( ! is_array( $_hl_links ) ) $_hl_links = [];
        $_hl_links = array_values( array_filter( $_hl_links, fn( $l ) => ! empty( $l['name'] ) || ! empty( $l['url'] ) ) );
        ?>
        <?php if ( ! empty( $_hl_links ) ) : ?>
        <div class="sidebar-card ah-hl-card">
          <div class="sidebar-card__title"><?php echo esc_html( TXT_HIGHLIGHT_LINKS ); ?></div>
          <div class="ah-hl-buttons">
            <?php foreach ( $_hl_links as $_hl ) : ?>
            <a href="<?php echo esc_url( $_hl['url'] ?? '#' ); ?>" class="ah-hl-btn">
              <?php echo esc_html( $_hl['name'] ?? $_hl['url'] ); ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Useful Links - only shown when post is tagged with 'useful-links' taxonomy TYPE -->
        <?php if ( class_exists( 'AH_Theme_Content_Taxonomy' ) && AH_Theme_Content_Taxonomy::has_terms_of_type( get_the_ID(), 'ah_post', 'useful-links' ) ) : ?>
        <div class="sidebar-card">
          <div class="sidebar-card__title"><?php echo esc_html( TXT_USEFUL_LINKS ); ?></div>
          <div class="toc">
            <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>"         class="toc__item">📚 <?php echo esc_html( TXT_ALL_BUYING_GUIDES ); ?></a>
            <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"       class="toc__item">✦ <?php echo esc_html( TXT_OUR_SERVICES ); ?></a>
            <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="toc__item">⭐ <?php echo esc_html( TXT_CLIENT_STORIES ); ?></a>
          </div>
        </div>
        <?php endif; ?>

      </aside>

    </div>
  </div>
</main>

<?php endwhile; ?>

<?php
// ── Related Articles & You Might Also Like - only when post is tagged with 'related-articles' taxonomy TYPE ──
$_show_related = class_exists( 'AH_Theme_Content_Taxonomy' ) && AH_Theme_Content_Taxonomy::has_terms_of_type( get_the_ID(), 'ah_post', 'related-articles' );

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
if ( count( $_related ) < 7 ) {
  $_have = array_merge( [ get_the_ID() ], wp_list_pluck( $_related, 'ID' ) );
  $_pad  = get_posts( [
    'numberposts'  => 7 - count( $_related ),
    'post__not_in' => $_have,
    'post_status'  => 'publish',
    'orderby'      => 'rand',
  ] );
  $_related = array_merge( $_related, $_pad );
}

// ── You Might Also Like - popular from other categories, fill with others ────
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

// Step 3: last resort - any post if still under 3
if ( count( $_guides ) < 5 ) {
  $_guides_excl = array_merge( $_exclude_ids, wp_list_pluck( $_guides, 'ID' ) );
  $_guides_any  = get_posts( [
    'numberposts'  => 5 - count( $_guides ),
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

<?php get_template_part( 'components/scroll-to-top' ); ?>

<script>
(function () {
	/* ── Reading progress bar ── */
	var fill    = document.getElementById('ahReadbarFill');
	var article = document.getElementById('article-body');
	if (fill && article) {
		var ticking = false;
		function update() {
			var rect   = article.getBoundingClientRect();
			var top    = rect.top + window.pageYOffset;
			var height = article.offsetHeight - window.innerHeight;
			var scrolled = window.pageYOffset - top;
			var pct = height > 0 ? (scrolled / height) * 100 : 0;
			fill.style.width = Math.max(0, Math.min(100, pct)) + '%';
			ticking = false;
		}
		function onScroll() {
			if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
		}
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll);
		update();
	}

	/* ── Copy-link share button ── */
	var copyBtn = document.querySelector('.post-share__btn--copy');
	if (copyBtn) {
		copyBtn.addEventListener('click', function () {
			var url = copyBtn.getAttribute('data-url') || window.location.href;
			var done = function () {
				copyBtn.classList.add('is-copied');
				copyBtn.setAttribute('aria-label', 'Link copied');
				setTimeout(function () {
					copyBtn.classList.remove('is-copied');
					copyBtn.setAttribute('aria-label', 'Copy link');
				}, 1600);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(done).catch(done);
			} else {
				var t = document.createElement('textarea');
				t.value = url; document.body.appendChild(t); t.select();
				try { document.execCommand('copy'); } catch (e) {}
				document.body.removeChild(t); done();
			}
		});
	}
})();
</script>

<?php get_footer(); ?>
