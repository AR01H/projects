<?php
defined( 'ABSPATH' ) || exit;

$guide_cats = $args['guide_cats'] ?? [];
if ( ! $guide_cats ) return;
?>
<section class="nhp-topics">
  <div class="container">
    <div class="nhp-section-head" data-aos="fade-up">
      <div>
        <span class="nhp-eyebrow">Browse by Topic</span>
        <h2 class="nhp-section-title">Find Exactly What You Need</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="nhp-see-all">View all topics →</a>
    </div>
    <div class="nhp-topics__grid">
      <?php foreach ( array_slice( $guide_cats, 0, 6 ) as $i => $gc ) :
        $gc      = is_object( $gc ) ? (array) $gc : $gc;
        $img_url = ! empty( $gc['image_id'] ) ? wp_get_attachment_image_url( (int) $gc['image_id'], 'medium' ) : '';
        $color   = ! empty( $gc['parent_color'] ) ? $gc['parent_color'] : 'var(--accent)';
        $url     = home_url( '/guides/?category=' . urlencode( $gc['slug'] ?? '' ) );
      ?>
      <a href="<?php echo esc_url( $url ); ?>"
         class="nhp-topic-card"
         style="--tc:<?php echo esc_attr( $color ); ?>"
         data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 80; ?>">
        <div class="nhp-topic-card__header" <?php if ( $img_url ) echo 'style="background-image:url(' . esc_url( $img_url ) . ')"'; ?>>
          <?php if ( ! $img_url ) : ?>
          <span class="nhp-topic-card__icon"><?php echo esc_html( $gc['icon_emoji'] ?? '📖' ); ?></span>
          <?php endif; ?>
          <div class="nhp-topic-card__header-overlay" aria-hidden="true"></div>
        </div>
        <div class="nhp-topic-card__body">
          <h3 class="nhp-topic-card__title"><?php echo esc_html( $gc['title'] ?? '' ); ?></h3>
          <?php if ( ! empty( $gc['desc'] ) ) : ?><p class="nhp-topic-card__desc"><?php echo esc_html( $gc['desc'] ); ?></p><?php endif; ?>
        </div>
        <div class="nhp-topic-card__footer">
          <?php if ( ! empty( $gc['count'] ) ) : ?><span class="nhp-topic-card__count"><?php echo (int) $gc['count']; ?> guides</span><?php endif; ?>
          <span class="nhp-topic-card__arrow" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
