<?php
/**
 * Component: NIF Hero Row
 * Large horizontal hero card + 2 stacked side cards.
 *
 * @var array $args {
 *   @type WP_Post[] $posts  Up to 3 WP_Post objects. [0]=hero, [1][2]=side stack.
 * }
 */
defined( 'ABSPATH' ) || exit;

$posts = $args['posts'] ?? [];
$hero  = $posts[0] ?? null;
$side1 = $posts[1] ?? null;
$side2 = $posts[2] ?? null;

if ( ! $hero ) return;
?>
<section class="section nif-section-hero" aria-label="<?php esc_attr_e( 'Featured articles', 'ah-theme' ); ?>">
  <div class="container">
    <div class="nif-hero-row">

      <!-- Main hero card (horizontal) -->
      <?php $d = nif_get_post_data( $hero ); ?>
      <article class="nif-hero-card" data-aos="fade-up">

        <?php if ( $d['thumb_url'] ) : ?>
          <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-hero-card__img-wrap" tabindex="-1" aria-hidden="true">
            <img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
                 alt="<?php echo esc_attr( get_the_title( $hero->ID ) ); ?>"
                 loading="eager" decoding="async">
            <div class="nif-hero-card__img-overlay" aria-hidden="true"></div>
          </a>
        <?php else : ?>
          <div class="nif-hero-card__img-wrap nif-hero-card__img-wrap--placeholder" aria-hidden="true">
            <span><?php echo esc_html( $d['emoji'] ); ?></span>
          </div>
        <?php endif; ?>

        <div class="nif-hero-card__body">
          <div class="nif-hero-card__meta">
            <?php if ( $d['cat'] ) : ?>
              <span class="nif-badge nif-badge--gold" data-slug="<?php echo esc_attr( $d['cat']->slug ); ?>">
                <?php echo esc_html( $d['cat']->name ); ?>
              </span>
            <?php endif; ?>
            <span class="nif-hero-card__label"><?php esc_html_e( 'Top Story', 'ah-theme' ); ?></span>
          </div>
          <h2 class="nif-hero-card__title">
            <a href="<?php echo esc_url( $d['permalink'] ); ?>">
              <?php echo esc_html( get_the_title( $hero->ID ) ); ?>
            </a>
          </h2>
          <p class="nif-hero-card__excerpt"><?php echo esc_html( $d['excerpt'] ); ?></p>
          <div class="nif-hero-card__footer">
            <span class="nif-meta-time">
              <?php if ( $d['read_time'] ) : ?>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo esc_html( $d['read_time'] ); ?>
              <?php endif; ?>
            </span>
            <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-read-link">
              <?php esc_html_e( 'Read full article', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>

      </article>

      <!-- Side stack -->
      <div class="nif-side-stack">
        <?php foreach ( array_filter( [ $side1, $side2 ] ) as $sp ) :
          $d = nif_get_post_data( $sp );
        ?>
        <article class="nif-side-card" data-aos="fade-up">

          <?php if ( $d['thumb_url'] ) : ?>
            <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-side-card__img" tabindex="-1" aria-hidden="true">
              <img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
                   alt="<?php echo esc_attr( get_the_title( $sp->ID ) ); ?>"
                   loading="lazy" decoding="async">
            </a>
          <?php else : ?>
            <div class="nif-side-card__img nif-side-card__img--placeholder" aria-hidden="true">
              <span><?php echo esc_html( $d['emoji'] ); ?></span>
            </div>
          <?php endif; ?>

          <div class="nif-side-card__body">
            <?php if ( $d['cat'] ) : ?>
              <span class="nif-badge" data-slug="<?php echo esc_attr( $d['cat']->slug ); ?>">
                <?php echo esc_html( $d['cat']->name ); ?>
              </span>
            <?php endif; ?>
            <h3 class="nif-side-card__title">
              <a href="<?php echo esc_url( $d['permalink'] ); ?>">
                <?php echo esc_html( get_the_title( $sp->ID ) ); ?>
              </a>
            </h3>
            <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-read-link nif-read-link--sm" style="margin-top:auto">
              <?php esc_html_e( 'Read', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
            </a>
          </div>

        </article>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>
