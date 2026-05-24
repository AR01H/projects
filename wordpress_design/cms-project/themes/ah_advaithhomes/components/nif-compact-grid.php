<?php
/**
 * Component: NIF Compact Grid
 * 4-column small card grid with optional "View all" link.
 *
 * @var array $args {
 *   @type WP_Post[] $posts       WP_Post objects to display.
 *   @type string    $eyebrow     Section eyebrow. Default 'More Reads'.
 *   @type string    $heading     Heading HTML. Default 'Latest <em>Updates</em>'.
 *   @type string    $more_url    Optional URL for a "View all" button.
 *   @type string    $more_label  Optional label for the "View all" button.
 * }
 */
defined( 'ABSPATH' ) || exit;

$posts      = $args['posts']      ?? [];
$eyebrow    = $args['eyebrow']    ?? sprintf( TXT_MORE_S, AH_TERM_PLURAL );
$heading    = $args['heading']    ?? 'Latest <em>Updates</em>';
$more_url   = $args['more_url']   ?? '';
$more_label = $args['more_label'] ?? sprintf( TXT_VIEW_ALL_S, AH_TERM_LOWER_PLURAL );

if ( empty( $posts ) ) return;
?>
<section class="section nif-section-compact" aria-label="<?php echo esc_attr( TXT_PHP_PRINTF_ESC_ATTR_TXT_MORE_S_AH_TERM_LOWER_PLURAL ); ?>">
  <div class="container">

    <div class="nif-section-header" data-aos="fade-up">
      <div>
        <span class="section__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
        <h2 class="section__title" style="font-size:1.4rem;margin:6px 0 0"><?php echo wp_kses_post( $heading ); ?></h2>
      </div>
      <?php if ( $more_url ) : ?>
        <a href="<?php echo esc_url( $more_url ); ?>" class="nif-more-link">
          <?php echo esc_html( $more_label ); ?> <span aria-hidden="true">→</span>
        </a>
      <?php endif; ?>
    </div>

    <div class="nif-compact-grid">
      <?php foreach ( $posts as $gp ) :
        $d = nif_get_post_data( $gp );
      ?>
      <article class="nif-compact-card" data-aos="fade-up">

        <?php if ( $d['thumb_url'] ) : ?>
          <div class="nif-compact-card__img">
            <img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
                 alt="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_GET_THE_TITLE_GP_ID ); ?>"
                 loading="lazy" decoding="async">
          </div>
        <?php else : ?>
          <div class="nif-compact-card__img nif-compact-card__img--placeholder" aria-hidden="true">
            <span><?php echo esc_html( $d['emoji'] ); ?></span>
          </div>
        <?php endif; ?>

        <div class="nif-compact-card__body">
          <?php if ( $d['cat'] ) : ?>
            <span class="nif-badge nif-badge--xs" data-slug="<?php echo esc_attr( $d['cat']->slug ); ?>">
              <?php echo esc_html( $d['cat']->name ); ?>
            </span>
          <?php endif; ?>
          <h3 class="nif-compact-card__title">
            <a href="<?php echo esc_url( $d['permalink'] ); ?>">
              <?php echo esc_html( get_the_title( $gp->ID ) ); ?>
            </a>
          </h3>
          <?php if ( $d['read_time'] ) : ?>
            <span class="nif-meta-time"><?php echo esc_html( $d['read_time'] ); ?></span>
          <?php endif; ?>
        </div>

      </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>
