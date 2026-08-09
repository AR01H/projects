<?php
/**
 * Site Decor - fixed vintage illustrations flanking the page.
 *
 * Purely decorative parchment-era artwork pinned to the left/right screen
 * edges (behind all content) to give every page the framed, vintage feel of
 * the reference design. Every value - image paths, opacity, on/off - comes
 * from admin/data/decor.json, nothing is hardcoded. Hidden on narrow screens.
 */
defined( 'ABSPATH' ) || exit;

$decor = App_Data_Provider::get( 'decor' );
if ( empty( $decor['enabled'] ) ) {
	return;
}

$left    = $decor['left']['image']  ?? '';
$right   = $decor['right']['image'] ?? '';
$opacity = isset( $decor['opacity'] ) ? (float) $decor['opacity'] : 0.18;
$style   = '--app-decor-opacity:' . esc_attr( $opacity ) . ';';
?>
<div class="app-site-decor" aria-hidden="true" style="<?php echo esc_attr( $style ); ?>">
	<?php if ( $left ) : ?>
		<span class="app-site-decor__edge app-site-decor__edge--left"
		      style="background-image:url('<?php echo esc_url( get_template_directory_uri() . '/' . ltrim( $left, '/' ) ); ?>');"></span>
	<?php endif; ?>
	<?php if ( $right ) : ?>
		<span class="app-site-decor__edge app-site-decor__edge--right"
		      style="background-image:url('<?php echo esc_url( get_template_directory_uri() . '/' . ltrim( $right, '/' ) ); ?>');"></span>
	<?php endif; ?>
</div>
