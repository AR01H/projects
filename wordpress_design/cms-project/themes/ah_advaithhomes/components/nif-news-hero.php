<?php
/**
 * Component: NIF News Hero
 * "Latest News" - big featured card (left, 3fr) + 3 compact side cards (right, 2fr).
 * Inline category chip filter via ?news_cat= (portal layout stays intact).
 * Sticky post = featured hero; falls back to most recent.
 * No dates displayed.
 *
 * @var array $args {
 *   @type WP_Post[] $posts      4+ post objects. posts[0]=hero, posts[1-3]=side.
 *   @type string    $see_all    "See all" URL.
 *   @type string    $eyebrow    Section label. Default 'Latest News'.
 *   @type WP_Term[] $cats       All WP categories for the filter chips.
 *   @type string    $news_cat   Currently active news category slug.
 *   @type string    $permalink  Base page URL for building filter links.
 * }
 */
defined( 'ABSPATH' ) || exit;

$posts      = $args['posts']     ?? [];
$see_all    = $args['see_all']   ?? home_url( '/news/' );
$eyebrow    = $args['eyebrow']   ?? TXT_LATEST_NEWS;
$cats       = $args['cats']      ?? [];
$news_cat   = $args['news_cat']  ?? '';
$permalink  = $args['permalink'] ?? get_permalink();

if ( empty( $posts ) ) return;

$hero       = $posts[0];
$side_posts = array_slice( $posts, 1, 3 );

$hd     = nif_get_post_data( $hero );
$h_cats = get_the_category( $hero->ID );
$h_cat1 = $h_cats[0] ?? null;
$h_cat2 = $h_cats[1] ?? null;
$h_bg   = $hd['thumb_url'] ? 'style="--nif-bg:url(' . esc_url( $hd['thumb_url'] ) . ')"' : '';

// Build category chip URLs - preserve any existing ?pg or ?category params except news_cat
$chip_base = remove_query_arg( 'news_cat', $permalink );
?>
<section class="nif-portal-section" aria-label="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_EYEBROW ); ?>">

  <!-- Header row: label + See all -->
  <div class="nif-portal-section-row">
    <span class="nif-section-label--primary"><?php echo esc_html( $eyebrow ); ?></span>
    <a href="<?php echo esc_url( $see_all ); ?>" class="nif-more-link">
      <?php echo esc_html( TXT_SEE_ALL ); ?> <span aria-hidden="true">→</span>
    </a>
  </div>

  <!-- Category filter chips -->
  <!-- <?php if ( ! empty( $cats ) ) : ?>
  <div class="nif-news-cats" role="navigation" aria-label="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_TXT_FILTER_NEWS_BY_CATEGORY ); ?>">
    <a href="<?php echo esc_url( $chip_base ); ?>"
       class="nif-news-cat-chip<?php echo ! $news_cat ? ' nif-news-cat-chip--active' : ''; ?>">
      <?php echo esc_html( TXT_ALL ); ?>
    </a>
    <?php foreach ( $cats as $cat ) :
      $active = ( $news_cat === $cat->slug );
    ?>
    <a href="<?php echo esc_url( add_query_arg( 'news_cat', $cat->slug, $chip_base ) ); ?>"
       class="nif-news-cat-chip<?php echo $active ? ' nif-news-cat-chip--active' : ''; ?>"
       data-slug="<?php echo esc_attr( $cat->slug ); ?>">
      <?php echo esc_html( $cat->name ); ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?> -->

  <!-- Hero grid -->
  <div class="nif-news-hero-grid">

    <!-- ── BIG HERO CARD ── -->
    <article class="nif-hero-big-card" <?php echo $h_bg; ?> data-aos="fade-up">
      <div class="nif-hero-big-card__gradient" aria-hidden="true"></div>
      <div class="nif-hero-big-card__body">
        <div class="nif-hero-big-card__badges">
          <?php if ( $h_cat1 ) : ?>
            <span class="nif-tile-badge" data-slug="<?php echo esc_attr( $h_cat1->slug ); ?>">
              <?php echo esc_html( $h_cat1->name ); ?>
            </span>
          <?php endif; ?>
          <?php if ( $h_cat2 ) : ?>
            <span class="nif-tile-badge nif-tile-badge--outline" data-slug="<?php echo esc_attr( $h_cat2->slug ); ?>">
              <?php echo esc_html( $h_cat2->name ); ?>
            </span>
          <?php endif; ?>
        </div>

        <h2 class="nif-hero-big-card__title">
          <a href="<?php echo esc_url( $hd['permalink'] ); ?>">
            <?php echo esc_html( get_the_title( $hero->ID ) ); ?>
          </a>
        </h2>

        <p class="nif-hero-big-card__excerpt"><?php echo esc_html( $hd['excerpt'] ); ?></p>

        <div class="nif-hero-big-card__meta">
          <?php if ( $hd['read_time'] ) : ?>
            <span class="nif-meta-pill">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?php echo esc_html( $hd['read_time'] ); ?>
            </span>
          <?php endif; ?>
          <a href="<?php echo esc_url( $hd['permalink'] ); ?>" class="nif-hero-big-card__cta">
            <?php echo esc_html( TXT_CONTINUE_READING_1 ); ?> <span aria-hidden="true">→</span>
          </a>
        </div>
      </div>
    </article>

    <!-- ── SMALL SIDE CARDS ── -->
    <div class="nif-hero-side-cards">
      <?php foreach ( $side_posts as $i => $p ) :
        $d      = nif_get_post_data( $p );
        $p_cats = get_the_category( $p->ID );
        $p_cat  = $p_cats[0] ?? null;
      ?>
      <article class="nif-side-card" data-aos="fade-left" data-aos-delay="<?php echo esc_attr( $i * 80 ); ?>">

        <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-side-card__img" tabindex="-1" aria-hidden="true">
          <?php if ( $d['thumb_url'] ) : ?>
            <img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
                 alt="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_GET_THE_TITLE_P_ID ); ?>"
                 loading="lazy" decoding="async">
          <?php else : ?>
            <div class="nif-side-card__placeholder" aria-hidden="true">
              <span><?php echo esc_html( $d['emoji'] ); ?></span>
            </div>
          <?php endif; ?>
        </a>

        <div class="nif-side-card__body">
          <?php if ( $p_cat ) : ?>
            <span class="nif-tile-badge nif-tile-badge--sm" data-slug="<?php echo esc_attr( $p_cat->slug ); ?>">
              <?php echo esc_html( $p_cat->name ); ?>
            </span>
          <?php endif; ?>
          <h3 class="nif-side-card__title">
            <a href="<?php echo esc_url( $d['permalink'] ); ?>">
              <?php echo esc_html( get_the_title( $p->ID ) ); ?>
            </a>
          </h3>
          <?php if ( $d['read_time'] ) : ?>
            <span class="nif-meta-time nif-side-card__rt">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?php echo esc_html( $d['read_time'] ); ?>
            </span>
          <?php endif; ?>
        </div>

      </article>
      <?php endforeach; ?>
    </div>

  </div><!-- /.nif-news-hero-grid -->

</section>
