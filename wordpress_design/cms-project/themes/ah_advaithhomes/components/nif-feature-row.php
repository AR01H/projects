<?php
/**
 * Component: NIF Feature Row
 * Dark overlay card (full image background) + flanking horizontal cards.
 *
 * @var array $args {
 *   @type WP_Post   $feat   Featured post shown as full-bleed overlay card.
 *   @type WP_Post[] $flank  Up to 2 flanking article cards.
 *   @type string    $eyebrow Section eyebrow label. Default "Editor's Picks".
 * }
 */
defined( 'ABSPATH' ) || exit;

$feat    = $args['feat']    ?? null;
$flank   = $args['flank']   ?? [];
$eyebrow = $args['eyebrow'] ?? TXT_EDITOR_S_PICKS;

if ( ! $feat && empty( $flank ) ) return;
?>
<section class="section section--alt nif-section-feature" aria-label="<?php echo esc_attr( $eyebrow ); ?>">
  <div class="container">

    <div class="nif-section-label" data-aos="fade-up">
      <span class="section__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
    </div>

    <div class="nif-feature-row">

      <!-- Dark overlay card -->
      <?php if ( $feat ) :
        $d  = nif_get_post_data( $feat );
        $bg = $d['thumb_url'] ? 'style="--nif-bg:url(' . esc_url( $d['thumb_url'] ) . ')"' : '';
      ?>
      <article class="nif-overlay-card" <?php echo $bg; ?> data-aos="fade-up">
        <div class="nif-overlay-card__gradient" aria-hidden="true"></div>
        <div class="nif-overlay-card__body">
          <?php if ( $d['cat'] ) : ?>
            <span class="nif-badge nif-badge--white" data-slug="<?php echo esc_attr( $d['cat']->slug ); ?>">
              <?php echo esc_html( $d['cat']->name ); ?>
            </span>
          <?php endif; ?>
          <h3 class="nif-overlay-card__title">
            <a href="<?php echo esc_url( $d['permalink'] ); ?>">
              <?php echo esc_html( get_the_title( $feat->ID ) ); ?>
            </a>
          </h3>
          <p class="nif-overlay-card__excerpt"><?php echo esc_html( $d['excerpt'] ); ?></p>
          <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-overlay-card__link">
            <?php echo '/slug/ or URL'; ?> <span aria-hidden="true">→</span>
          </a>
        </div>
      </article>
      <?php endif; ?>

      <!-- Flanking cards -->
      <div class="nif-flank-stack">
        <?php foreach ( $flank as $fp ) :
          $d = nif_get_post_data( $fp );
        ?>
        <article class="nif-flank-card" data-aos="fade-up">

          <?php if ( $d['thumb_url'] ) : ?>
            <div class="nif-flank-card__img">
              <img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
                   alt="<?php echo esc_attr( get_the_title( $fp->ID ) ); ?>"
                   loading="lazy" decoding="async">
            </div>
          <?php else : ?>
            <div class="nif-flank-card__img nif-flank-card__img--placeholder" aria-hidden="true">
              <span><?php echo esc_html( $d['emoji'] ); ?></span>
            </div>
          <?php endif; ?>

          <div class="nif-flank-card__body">
            <?php if ( $d['cat'] ) : ?>
              <span class="nif-badge" data-slug="<?php echo esc_attr( $d['cat']->slug ); ?>">
                <?php echo esc_html( $d['cat']->name ); ?>
              </span>
            <?php endif; ?>
            <h3 class="nif-flank-card__title">
              <a href="<?php echo esc_url( $d['permalink'] ); ?>">
                <?php echo esc_html( get_the_title( $fp->ID ) ); ?>
              </a>
            </h3>
            <p class="nif-flank-card__excerpt"><?php echo esc_html( $d['excerpt'] ); ?></p>
            <div class="nif-flank-card__footer">
              <?php if ( $d['read_time'] ) : ?>
                <span class="nif-meta-time">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <?php echo esc_html( $d['read_time'] ); ?>
                </span>
              <?php endif; ?>
              <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-read-link nif-read-link--sm">
                <?php echo esc_html( TXT_READ ); ?> <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>

        </article>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>
