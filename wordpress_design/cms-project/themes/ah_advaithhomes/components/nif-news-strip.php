<?php
/**
 * Component: NIF News Strip
 * "Latest News" — 4-column card row pulled from WP posts.
 *
 * @var array $args {
 *   @type WP_Post[] $posts     Up to 4 WP_Post objects.
 *   @type string    $see_all   URL for "See all" link.
 *   @type string    $eyebrow   Section label. Default 'Latest News'.
 * }
 */
defined( 'ABSPATH' ) || exit;

$posts   = $args['posts']   ?? [];
$see_all = $args['see_all'] ?? home_url( '/news/' );
$eyebrow = $args['eyebrow'] ?? __( 'Latest News', 'ah-theme' );

if ( empty( $posts ) ) return;
?>
<section class="nif-portal-section" aria-label="<?php echo esc_attr( $eyebrow ); ?>">

  <div class="nif-portal-section-row">
    <span class="nif-section-label--primary"><?php echo esc_html( $eyebrow ); ?></span>
    <a href="<?php echo esc_url( $see_all ); ?>" class="nif-more-link">
      <?php esc_html_e( 'See all', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
    </a>
  </div>

  <div class="nif-news-strip">
    <?php foreach ( $posts as $i => $p ) :
      $d        = nif_get_post_data( $p );
      $date_str = get_the_date( 'j M Y', $p->ID );
    ?>
    <article class="nif-news-card" data-aos="fade-up" data-aos-delay="<?php echo esc_attr( $i * 70 ); ?>">

      <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-news-card__img-wrap" tabindex="-1" aria-hidden="true">
        <?php if ( $d['thumb_url'] ) : ?>
          <img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
               alt="<?php echo esc_attr( get_the_title( $p->ID ) ); ?>"
               loading="lazy" decoding="async">
        <?php else : ?>
          <div class="nif-news-card__placeholder" aria-hidden="true">
            <span><?php echo esc_html( $d['emoji'] ); ?></span>
          </div>
        <?php endif; ?>
        <?php if ( $d['cat'] ) : ?>
          <span class="nif-news-card__badge" data-slug="<?php echo esc_attr( $d['cat']->slug ); ?>">
            <?php echo esc_html( $d['cat']->name ); ?>
          </span>
        <?php endif; ?>
      </a>

      <div class="nif-news-card__body">
        <h3 class="nif-news-card__title">
          <a href="<?php echo esc_url( $d['permalink'] ); ?>">
            <?php echo esc_html( get_the_title( $p->ID ) ); ?>
          </a>
        </h3>
        <p class="nif-news-card__excerpt"><?php echo esc_html( $d['excerpt'] ); ?></p>
        <div class="nif-news-card__meta">
          <?php if ( $d['read_time'] ) : ?>
            <span class="nif-meta-dot-row">
              <span class="nif-meta-dot nif-meta-dot--active"></span>
              <span class="nif-meta-dot-line"></span>
              <span class="nif-meta-dot"></span>
              <span class="nif-meta-dot-line"></span>
              <span class="nif-meta-dot"></span>
            </span>
            <span class="nif-meta-time"><?php echo esc_html( $d['read_time'] ); ?></span>
          <?php endif; ?>
          <span class="nif-meta-date"><?php echo esc_html( $date_str ); ?></span>
        </div>
      </div>

    </article>
    <?php endforeach; ?>
  </div>

</section>
