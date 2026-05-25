<?php
/**
 * Component: NIF Featured Hero
 * Large Breaking News card - full background image, overlaid content.
 *
 * @var array $args {
 *   @type WP_Post $post      The featured post.
 *   @type string  $news_url  "All news" link URL.
 * }
 */
defined( 'ABSPATH' ) || exit;

$post     = $args['post']     ?? null;
$news_url = $args['news_url'] ?? home_url( '/news/' );

if ( ! $post instanceof WP_Post ) return;

$d        = nif_get_post_data( $post );
$cats     = get_the_category( $post->ID );
$cat1     = $cats[0] ?? null;
$cat2     = $cats[1] ?? null;
$date_str = get_the_date( 'j M Y', $post->ID );
$bg_style = $d['thumb_url'] ? 'style="--nif-bg:url(' . esc_url( $d['thumb_url'] ) . ')"' : '';
?>
<section class="nif-portal-section" aria-label="<?php echo esc_attr( TXT_BREAKING_NEWS ); ?>">

  <div class="nif-portal-section-row">
    <span class="nif-section-label--primary"><?php echo esc_html( TXT_BREAKING_NEWS ); ?></span>
    <a href="<?php echo esc_url( $news_url ); ?>" class="nif-more-link">
      <?php echo esc_html( TXT_ALL_NEWS ); ?> <span aria-hidden="true">→</span>
    </a>
  </div>

  <article class="nif-featured-hero" <?php echo $bg_style; ?> data-aos="fade-up">
    <div class="nif-featured-hero__gradient" aria-hidden="true"></div>

    <div class="nif-featured-hero__body">
      <div class="nif-featured-hero__badges">
        <?php if ( $cat1 ) : ?>
          <span class="nif-tile-badge" data-slug="<?php echo esc_attr( $cat1->slug ); ?>">
            <?php echo esc_html( $cat1->name ); ?>
          </span>
        <?php endif; ?>
        <?php if ( $cat2 ) : ?>
          <span class="nif-tile-badge nif-tile-badge--outline" data-slug="<?php echo esc_attr( $cat2->slug ); ?>">
            <?php echo esc_html( $cat2->name ); ?>
          </span>
        <?php endif; ?>
      </div>

      <h2 class="nif-featured-hero__title">
        <a href="<?php echo esc_url( $d['permalink'] ); ?>">
          <?php echo esc_html( get_the_title( $post->ID ) ); ?>
        </a>
      </h2>

      <p class="nif-featured-hero__excerpt"><?php echo esc_html( $d['excerpt'] ); ?></p>

      <div class="nif-featured-hero__meta">
        <?php if ( $d['read_time'] ) : ?>
          <span class="nif-meta-pill">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?php echo esc_html( $d['read_time'] ); ?>
          </span>
        <?php endif; ?>
        <span class="nif-meta-pill nif-meta-pill--date"><?php echo esc_html( $date_str ); ?></span>
        <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-featured-hero__cta">
          <?php echo esc_html( TXT_CONTINUE_READING_1 ); ?> <span aria-hidden="true">→</span>
        </a>
      </div>
    </div>
  </article>

</section>
