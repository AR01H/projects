<?php
/**
 * components/parts/section_headers/eyebrow_heading.php
 *
 * Centered "eyebrow pill + heading" section intro (optionally a
 * subheading paragraph underneath, and the gold underline drawn by
 * .contact-section-heading::after). Used across the How It Works page's
 * sections (process timeline, story, icon feature grid, comparison) -
 * previously duplicated inline in each of those 4 component files.
 *
 * Note: the existing components/parts/section_headers/section_header.php
 * doesn't fit here - it has no eyebrow support, and its centered variant
 * (.section-header--center) resets heading font-size/margins that this
 * page's .contact-section-heading already sets deliberately.
 *
 * Props:
 *   $eyebrow        string  optional small uppercase pill above the heading
 *   $heading        string  the h2 text
 *   $subheading     string  optional paragraph under the heading
 *   $wrapper_class  string  extra class(es) alongside 'section-header-wrap'
 *                           (e.g. 'icon-grid-header', 'hiw-process-header') -
 *                           each caller's own CSS keys off this for spacing/
 *                           max-width, so it isn't hardcoded here.
 *
 * Usage:
 *   adn_component( 'parts/section_headers/eyebrow_heading', array(
 *       'eyebrow'       => $grid['eyebrow'] ?? '',
 *       'heading'       => $grid['heading'] ?? '',
 *       'wrapper_class' => 'icon-grid-header',
 *   ) );
 */
defined( 'ABSPATH' ) || exit;

$_eyb = isset( $eyebrow )    ? esc_html( (string) $eyebrow )    : '';
$_hdg = isset( $heading )    ? esc_html( (string) $heading )    : '';
$_sub = isset( $subheading ) ? esc_html( (string) $subheading ) : '';
if ( '' === $_eyb && '' === $_hdg ) return;

$_wrap = 'section-header-wrap';
if ( ! empty( $wrapper_class ) ) {
	$_wrap .= ' ' . implode( ' ', array_map( 'sanitize_html_class', explode( ' ', (string) $wrapper_class ) ) );
}
?>
<div class="<?php echo esc_attr( $_wrap ); ?>">
	<?php if ( '' !== $_eyb ) : ?><span class="section-eyebrow"><?php echo $_eyb; ?></span><?php endif; ?>
	<?php if ( '' !== $_hdg ) : ?><h2 class="contact-section-heading"><?php echo $_hdg; ?></h2><?php endif; ?>
	<?php if ( '' !== $_sub ) : ?><p class="hiw-process-sub"><?php echo $_sub; ?></p><?php endif; ?>
</div>
