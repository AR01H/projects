<?php
/**
 * components/parts/section_headers/eyebrow_heading.php
 *
 * Reusable section header component:
 *   - Eyebrow pill badge (optional)
 *   - Section heading h2/h3 (with centered gold underline)
 *   - Subheading paragraph (optional)
 *
 * Props:
 *   $eyebrow        string  optional small uppercase pill above the heading
 *   $heading        string  the heading text
 *   $subheading     string  optional paragraph under the heading
 *   $tag            string  'h2' (default) | 'h3' | 'h1' | 'h4'
 *   $wrapper_class  string  extra class(es) alongside 'section-header-wrap'
 *
 * Usage:
 *   adn_component( 'parts/section_headers/eyebrow_heading', array(
 *       'eyebrow'       => 'Our Process',
 *       'heading'       => 'From First Contact to the Right Result',
 *       'subheading'    => 'Five clear steps tailored to your journey.',
 *       'wrapper_class' => 'hiw-process-header',
 *   ) );
 */
defined( 'ABSPATH' ) || exit;

$_eyb = isset( $eyebrow )    ? esc_html( (string) $eyebrow )    : '';
$_hdg = isset( $heading )    ? esc_html( (string) $heading )    : '';
$_sub = isset( $subheading ) ? esc_html( (string) $subheading ) : '';
$_tag = isset( $tag ) && in_array( $tag, array( 'h1', 'h2', 'h3', 'h4' ), true ) ? $tag : 'h2';

if ( '' === $_eyb && '' === $_hdg ) return;

$_wrap = 'section-header-wrap';
if ( ! empty( $wrapper_class ) ) {
	$_wrap .= ' ' . implode( ' ', array_map( 'sanitize_html_class', explode( ' ', (string) $wrapper_class ) ) );
}
?>
<div class="<?php echo esc_attr( $_wrap ); ?>">
	<?php if ( '' !== $_eyb ) : ?><span class="section-eyebrow"><?php echo $_eyb; ?></span><?php endif; ?>
	<?php if ( '' !== $_hdg ) : ?><<?php echo $_tag; ?> class="contact-section-heading"><?php echo $_hdg; ?></<?php echo $_tag; ?>><?php endif; ?>
	<?php if ( '' !== $_sub ) : ?><p class="section-subheading hiw-process-sub"><?php echo $_sub; ?></p><?php endif; ?>
</div>

