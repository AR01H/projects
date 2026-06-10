<?php
/**
 * Knowledge Hub hero.
 * Args: the 'hero' data array from intermediate_logics/knowledge-hub.php.
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$h     = $args ?? array();
$trust = $h['trust'] ?? array();
$steps = $h['journey_steps'] ?? array();
$bg    = get_template_directory_uri() . '/assets/images/backgrounds/family_background.png';
?>
<section class="khub-hero">
  <div class="container khub-hero__inner">

    <div class="khub-hero__body">
      <?php if ( ! empty( $h['eyebrow'] ) ) : ?>
        <span class="khub-hero__eyebrow"><?php echo esc_html( $h['eyebrow'] ); ?></span>
      <?php endif; ?>

      <h1 class="khub-hero__title">
        <?php echo esc_html( $h['title'] ?? '' ); ?>
        <?php if ( ! empty( $h['highlight'] ) ) : ?>
          <span class="khub-hero__title-hl"><?php echo esc_html( $h['highlight'] ); ?></span>
        <?php endif; ?>
      </h1>

      <?php if ( ! empty( $h['sub'] ) ) : ?>
        <p class="khub-hero__sub"><?php echo esc_html( $h['sub'] ); ?></p>
      <?php endif; ?>

      <?php if ( $trust ) : ?>
      <ul class="khub-hero__trust" role="list">
        <?php foreach ( $trust as $t ) : ?>
          <li class="khub-trust">
            <span class="khub-trust__ico"><?php echo ah_khub_icon( $t['icon'] ?? 'shield', 18 ); ?></span>
            <span class="khub-trust__text"><?php echo esc_html( $t['text'] ?? '' ); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <div class="khub-hero__ctas">
        <?php if ( ! empty( $h['cta_primary']['label'] ) ) : ?>
          <a href="<?php echo esc_url( $h['cta_primary']['url'] ?? '#' ); ?>" class="btn btn-primary khub-hero__cta">
            <?php echo esc_html( $h['cta_primary']['label'] ); ?> <?php echo ah_khub_icon( 'arrow', 16 ); ?>
          </a>
        <?php endif; ?>
        <?php if ( ! empty( $h['cta_secondary']['label'] ) ) : ?>
          <a href="<?php echo esc_url( $h['cta_secondary']['url'] ?? '#' ); ?>" class="btn btn-ghost khub-hero__cta">
            <?php echo esc_html( $h['cta_secondary']['label'] ); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Property Journey card -->
    <aside class="khub-journey" aria-label="<?php echo esc_attr( $h['journey_title'] ?? 'Your property journey' ); ?>">
      <div class="khub-journey__media" style="background-image:url('<?php echo esc_url( $bg ); ?>')" aria-hidden="true"></div>
      <div class="khub-journey__card">
        <h2 class="khub-journey__title"><?php echo esc_html( $h['journey_title'] ?? '' ); ?></h2>
        <ol class="khub-journey__steps">
          <?php foreach ( $steps as $s ) : ?>
            <li class="khub-jstep">
              <span class="khub-jstep__num"><?php echo esc_html( $s['n'] ?? '' ); ?></span>
              <span class="khub-jstep__text">
                <strong><?php echo esc_html( $s['title'] ?? '' ); ?></strong>
                <small><?php echo esc_html( $s['desc'] ?? '' ); ?></small>
              </span>
            </li>
          <?php endforeach; ?>
        </ol>
        <?php if ( ! empty( $h['journey_note'] ) ) : ?>
          <p class="khub-journey__note"><?php echo esc_html( $h['journey_note'] ); ?></p>
        <?php endif; ?>
      </div>
    </aside>

  </div>
</section>
