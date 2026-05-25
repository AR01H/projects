<?php
/**
 * Component: Single Post - Related Articles
 * Dark bento grid: featured card (left, tall) + 2 stacked cards (right).
 *
 * @var array $args {
 *   @type WP_Post[] $posts  Up to 7 posts.
 *   @type WP_Term|null $cat Primary category of the current post (for "More in…" link).
 * }
 */
defined( 'ABSPATH' ) || exit;

$posts = $args['posts'] ?? [];
$cat   = $args['cat']   ?? null;

if ( empty( $posts ) ) return;

$emoji_map = [ 'buying'=>'🏠','first'=>'🔑','finance'=>'💷','legal'=>'⚖️','invest'=>'📈','tips'=>'💡','client'=>'⭐' ];
?>
<section class="sp-related" aria-label="<?php printf( 'Related %s', AH_TERM_LOWER_PLURAL ); ?>">
  <div class="container">

    <div class="sp-section-head">
      <div>
        <span class="section__eyebrow"><?php echo esc_html( TXT_KEEP_READING ); ?></span>
        <h2 class="sp-section-head__title"><?php printf( 'Related %s', AH_TERM_PLURAL ); ?></h2>
      </div>
      <?php if ( $cat ) : ?>
      <a href="<?php echo esc_url( get_category_link( $cat ) ); ?>" class="sp-see-all">
        <?php printf( 'More %s →', esc_html( $cat->name ) ); ?>
      </a>
      <?php endif; ?>
    </div>

    <div class="sp-related-grid">
      <?php foreach ( $posts as $i => $rp ) :
        $rc     = get_the_category( $rp->ID );
        $rc0    = $rc[0] ?? null;
        $thumb  = get_the_post_thumbnail_url( $rp->ID, 'large' )
               ?: get_the_post_thumbnail_url( $rp->ID, 'medium_large' )
               ?: get_the_post_thumbnail_url( $rp->ID, 'medium' );
        $rexc   = wp_trim_words( get_the_excerpt( $rp->ID ) ?: $rp->post_content, 22, '…' );
        $rtime  = function_exists( 'ah_reading_time' ) ? ah_reading_time( $rp->ID ) : '';
        $emoji  = '📰';
        if ( $rc0 ) foreach ( $emoji_map as $k => $e ) { if ( stripos( $rc0->slug, $k ) !== false ) { $emoji = $e; break; } }
        $is_sug = get_post_meta( $rp->ID, '_ah_is_suggested', true ) === '1';
      ?>
      <a href="<?php echo esc_url( get_permalink( $rp ) ); ?>"
         class="sp-ra-card<?php echo in_array($i, [0, 4, 6]) ? ' sp-ra-card--featured' : ''; ?><?php echo $is_sug ? ' sp-ra-card--suggested' : ''; ?>"
         data-cat="<?php echo esc_attr( $rc0 ? $rc0->slug : '' ); ?>">

        <!-- Image layer -->
        <div class="sp-ra-card__img" aria-hidden="true">
          <?php if ( $thumb ) : ?>
            <img src="<?php echo esc_url( $thumb ); ?>"
                 alt="" loading="lazy" decoding="async">
          <?php else : ?>
            <div class="sp-ra-card__img-ph"><?php echo esc_html( $emoji ); ?></div>
          <?php endif; ?>
        </div>

        <!-- Gradient overlay -->
        <div class="sp-ra-card__overlay" aria-hidden="true"></div>

        <!-- Text content -->
        <div class="sp-ra-card__body">
          <?php if ( $is_sug ) : ?>
            <span class="sp-ra-card__suggested-badge">💡 Suggested</span>
          <?php endif; ?>
          <div class="sp-ra-card__top">
            <?php if ( $rc0 ) : ?>
              <span class="sp-ra-card__cat"><?php echo esc_html( $rc0->name ); ?></span>
            <?php endif; ?>
            <?php if ( $rtime ) : ?>
              <span class="sp-ra-card__time"><?php echo esc_html( $rtime ); ?></span>
            <?php endif; ?>
          </div>
          <h3 class="sp-ra-card__title"><?php echo esc_html( get_the_title( $rp ) ); ?></h3>
          <?php if ( $i === 0 && $rexc ) : ?>
            <p class="sp-ra-card__excerpt"><?php echo esc_html( $rexc ); ?></p>
          <?php endif; ?>
          <span class="sp-ra-card__cta"><?php printf( $i.' Read %s →', AH_TERM_SINGULAR ); ?></span>
        </div>

      </a>
      <?php endforeach; ?>
    </div>

  </div>
</section>
