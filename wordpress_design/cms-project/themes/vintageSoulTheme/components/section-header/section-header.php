<?php
/**
 * VintageSoulTheme - Standardized Section Header Master System
 * Strictly enforces exactly 3 clean lines across EVERY section on the site:
 * 1. Tag Ribbon (with vintage ribbon styling)
 * 2. Main Title (with italic serif emphasis)
 * 3. Subtitle / Description (readable body text)
 */

defined( 'ABSPATH' ) || exit;

$tag     = isset( $tag ) ? trim( (string) $tag ) : '';
$title   = isset( $title ) ? trim( (string) $title ) : '';
$eyebrow = isset( $eyebrow ) ? trim( (string) $eyebrow ) : '';
$sub     = isset( $sub ) ? trim( (string) $sub ) : '';
$body    = isset( $body ) ? trim( (string) $body ) : '';
$align   = ( isset( $align ) && 'left' === $align ) ? ' section-header--left' : '';
$variant = ( isset( $variant ) && 'dark' === $variant ) ? ' section-header--dark' : '';
$ribbon  = ! isset( $ribbon ) || false !== $ribbon;

// Exactly 1 description line (never stack 4th or 5th redundant lines)
$description = '';
if ( '' !== $sub ) {
	$description = $sub;
} elseif ( '' !== $body ) {
	$description = $body;
} elseif ( '' !== $eyebrow ) {
	$description = $eyebrow;
}

if ( '' === $tag && '' === $title && '' === $description ) {
	return;
}
?>
<div class="section-header<?php echo esc_attr( $align . $variant ); ?>">
	<?php if ( '' !== $tag ) : ?>
		<?php if ( $ribbon ) : ?>
			<span class="vintage-ribbon-tag<?php echo ' section-header--dark' === $variant ? ' vintage-ribbon-tag--gold' : ''; ?>">
				<span><?php echo esc_html( $tag ); ?></span>
			</span>
		<?php else : ?>
			<span class="section-header__tag"><?php echo esc_html( $tag ); ?></span>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( '' !== $title ) : ?>
		<h2 class="section-header__title"><?php echo wp_kses_post( $title ); ?></h2>
	<?php endif; ?>

	<?php if ( '' !== $description ) : ?>
		<p class="section-header__sub"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
</div>
