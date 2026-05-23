<?php
/**
 * Guide Category Card
 * $args['cat']   — array: id, slug, icon_emoji, image_id, title, desc, count
 * $args['index'] — int: for AOS stagger delay
 */
$cat      = $args['cat']   ?? [];
$index    = (int) ( $args['index'] ?? 0 );
$url      = home_url( '/guides/?category=' . urlencode( $cat['slug'] ?? '' ) );
$icon     = $cat['icon_emoji'] ?? ( $cat['icon'] ?? '📖' );
$title    = $cat['title']      ?? '';
$desc     = $cat['desc']       ?? '';
$count    = (int) ( $cat['count'] ?? 0 );
$image_id = (int) ( $cat['image_id'] ?? 0 );
$img_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
?>
<a href="<?php echo esc_url( $url ); ?>"
   class="gcat-card<?php echo $img_url ? ' gcat-card--img' : ''; ?>"
   <?php if ( $img_url ) : ?>
   style="--gc-bg:url('<?php echo esc_url( $img_url ); ?>')"
   <?php endif; ?>
   data-aos="fade-up"
   data-aos-delay="<?php echo $index * 80; ?>">

  <?php if ( $count ) : ?>
  <span class="gcat-card__watermark" aria-hidden="true"><?php echo esc_html( $count ); ?></span>
  <?php endif; ?>

  <div class="gcat-card__icon-wrap">
    <span class="gcat-card__icon"><?php echo esc_html( $icon ); ?></span>
  </div>

  <div class="gcat-card__body">
    <h3 class="gcat-card__title"><?php echo esc_html( $title ); ?></h3>
    <?php if ( $desc ) : ?>
    <p class="gcat-card__desc"><?php echo esc_html( $desc ); ?></p>
    <?php endif; ?>
  </div>

  <div class="gcat-card__footer">
    <span class="gcat-card__arrow" aria-hidden="true">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </span>
  </div>

</a>
