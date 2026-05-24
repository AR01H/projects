<?php
/**
 * Component: Single Post - Suggested Guides
 * 3-column card row from different categories ("You Might Also Like").
 *
 * @var array $args {
 *   @type WP_Post[] $posts  Up to 3 posts.
 * }
 */
defined( 'ABSPATH' ) || exit;

$posts = $args['posts'] ?? [];

if ( empty( $posts ) ) return;

$emoji_map = [ 'buying'=>'🏠','first'=>'🔑','finance'=>'💷','legal'=>'⚖️','invest'=>'📈','tips'=>'💡','client'=>'⭐' ];
?>
<section class="sp-guides-section" aria-label="<?php esc_attr_e( 'Suggested guides', 'ah-theme' ); ?>">
  <div class="container">

    <div class="sp-section-head">
      <div>
        <span class="section__eyebrow"><?php esc_html_e( 'Explore More', 'ah-theme' ); ?></span>
        <h2 class="sp-section-head__title"><?php esc_html_e( 'You Might Also Like', 'ah-theme' ); ?></h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="sp-see-all">
        <?php esc_html_e( 'All Guides →', 'ah-theme' ); ?>
      </a>
    </div>

    <div class="sp-guide-row">
      <?php foreach ( $posts as $i => $gp ) :
        $gc     = get_the_category( $gp->ID );
        $gc0    = $gc[0] ?? null;
        $gthumb = get_the_post_thumbnail_url( $gp->ID, 'medium_large' )
               ?: get_the_post_thumbnail_url( $gp->ID, 'medium' )
               ?: get_the_post_thumbnail_url( $gp->ID, 'full' );
        $gexc   = wp_trim_words( get_the_excerpt( $gp->ID ) ?: $gp->post_content, 20, '…' );
        $gtime  = function_exists( 'ah_reading_time' ) ? ah_reading_time( $gp->ID ) : '';
        $gemoji = '📰';
        if ( $gc0 ) foreach ( $emoji_map as $k => $e ) { if ( stripos( $gc0->slug, $k ) !== false ) { $gemoji = $e; break; } }
      ?>
      <a href="<?php echo esc_url( get_permalink( $gp ) ); ?>"
         class="sp-guide-card"
         data-cat="<?php echo esc_attr( $gc0 ? $gc0->slug : '' ); ?>"
         data-aos="fade-up" data-aos-delay="<?php echo esc_attr( $i * 80 ); ?>">

        <div class="sp-guide-card__img">
          <?php if ( $gthumb ) : ?>
            <img src="<?php echo esc_url( $gthumb ); ?>"
                 alt="<?php echo esc_attr( get_the_title( $gp ) ); ?>"
                 loading="lazy" decoding="async">
          <?php else : ?>
            <div class="sp-guide-card__placeholder" aria-hidden="true"><?php echo esc_html( $gemoji ); ?></div>
          <?php endif; ?>
        </div>

        <div class="sp-guide-card__body">
          <?php if ( $gc0 ) : ?>
            <span class="sp-guide-card__cat" data-slug="<?php echo esc_attr( $gc0->slug ); ?>">
              <?php echo esc_html( $gc0->name ); ?>
            </span>
          <?php endif; ?>
          <h3 class="sp-guide-card__title"><?php echo esc_html( get_the_title( $gp ) ); ?></h3>
          <p class="sp-guide-card__excerpt"><?php echo esc_html( $gexc ); ?></p>
          <div class="sp-guide-card__footer">
            <?php if ( $gtime ) : ?>
              <span class="sp-guide-card__time">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo esc_html( $gtime ); ?>
              </span>
            <?php endif; ?>
            <span class="sp-guide-card__read"><?php esc_html_e( 'Read →', 'ah-theme' ); ?></span>
          </div>
        </div>

      </a>
      <?php endforeach; ?>
    </div>

  </div>
</section>
