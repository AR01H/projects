<?php
/**
 * Guide Category Card
 * $args['cat']   - array: id, slug, icon_emoji, image_id, title, desc, count
 * $args['index'] - int: for AOS stagger delay
 */
$cat          = $args['cat']   ?? [];
$index        = (int) ( $args['index'] ?? 0 );
$url          = home_url( '/guides/?category=' . urlencode( $cat['slug'] ?? '' ) );
$icon         = $cat['icon_emoji'] ?? ( $cat['icon'] ?? '📖' );
$title        = $cat['title']      ?? '';
$desc         = $cat['desc']       ?? '';
$count        = (int) ( $cat['count'] ?? 0 );
$image_id     = (int) ( $cat['image_id'] ?? 0 );
$img_url      = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
$parent_color = trim( $cat['parent_color'] ?? '' );

// Strip colour: image takes priority, then parent_color, then nth-child CSS fallback
$strip_style = '';
if ( $img_url ) {
	$strip_style = 'background:url(\'' . esc_url( $img_url ) . '\') center/cover no-repeat';
} elseif ( $parent_color && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $parent_color ) ) {
	$hex   = ltrim( $parent_color, '#' );
	$r     = (int) ( hexdec( substr( $hex, 0, 2 ) ) * 0.35 );
	$g     = (int) ( hexdec( substr( $hex, 2, 2 ) ) * 0.35 );
	$b     = (int) ( hexdec( substr( $hex, 4, 2 ) ) * 0.35 );
	$dark  = sprintf( '#%02x%02x%02x', $r, $g, $b );
	$strip_style = "background:linear-gradient(90deg,{$dark},{$parent_color})";
}
?>
<a href="<?php echo esc_url( $url ); ?>"
   class="gcat-card<?php echo $img_url ? ' gcat-card--img' : ''; ?>"
   <?php if ( $img_url ) echo 'style="background-image:url(\'' . esc_url( $img_url ) . '\')"'; ?>
   data-aos="fade-up"
   data-aos-delay="<?php echo $index * 80; ?>">

  <?php if ( ! $img_url ) : ?>
  <div class="gcat-card__img-strip gcat-card__img-strip--icon" <?php if ( $strip_style ) echo 'style="' . esc_attr( $strip_style ) . '"'; ?>>
    <span class="gcat-card__strip-emoji"><?php echo esc_html( $icon ); ?></span>
  </div>
  <?php endif; ?>

  <div class="gcat-card__body-wrap">
    <div class="gcat-card__head">
      <?php if ( $img_url ) : ?>
      <div class="gcat-card__icon-wrap">
        <span class="gcat-card__icon"><?php echo esc_html( $icon ); ?></span>
      </div>
      <?php endif; ?>
      <h3 class="gcat-card__title"><?php echo esc_html( $title ); ?></h3>
    </div>

    <?php if ( $desc ) : ?>
    <div class="gcat-card__body">
      <p class="gcat-card__desc"><?php echo esc_html( $desc ); ?></p>
    </div>
    <?php endif; ?>

    <div class="gcat-card__footer">
      <?php if ( $count ) : ?>
        <span class="gcat-card__watermark"><?php echo (int) $count; ?> guides</span>
      <?php else : ?>
        <span></span>
      <?php endif; ?>
      <span class="gcat-card__arrow" aria-hidden="true">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </span>
    </div>
  </div>

</a>
