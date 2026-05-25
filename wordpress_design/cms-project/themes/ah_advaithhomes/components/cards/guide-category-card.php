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

// Build inline style: image > parent_color gradient > fallback to nth-child CSS
$inline_style = '';
if ( $img_url ) {
	$inline_style = "--gc-bg:url('" . esc_url( $img_url ) . "')";
} elseif ( $parent_color && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $parent_color ) ) {
	// Darken parent color to ~35% brightness for gradient start
	$hex    = ltrim( $parent_color, '#' );
	$r      = (int) ( hexdec( substr( $hex, 0, 2 ) ) * 0.35 );
	$g      = (int) ( hexdec( substr( $hex, 2, 2 ) ) * 0.35 );
	$b      = (int) ( hexdec( substr( $hex, 4, 2 ) ) * 0.35 );
	$dark   = sprintf( '#%02x%02x%02x', $r, $g, $b );
	$inline_style = "background:linear-gradient(145deg,{$dark} 0%,{$parent_color} 100%)";
}
?>
<a href="<?php echo esc_url( $url ); ?>"
   class="gcat-card<?php echo $img_url ? ' gcat-card--img' : ''; ?>"
   <?php if ( $inline_style ) echo 'style=" ' . esc_attr( $inline_style ) . ' "'; ?>
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
