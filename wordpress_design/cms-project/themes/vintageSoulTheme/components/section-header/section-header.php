<?php
/**
 * VintageSoulTheme - Standardized Section Header Component
 * Provides unified, theme-wide section headings with engraved cut lines, ribbons, italic serif accents, and subheadings.
 */

defined( 'ABSPATH' ) || exit;

$tag     = isset( $tag ) ? (string) $tag : '';
$title   = isset( $title ) ? (string) $title : '';
$eyebrow = isset( $eyebrow ) ? (string) $eyebrow : '';
$sub     = isset( $sub ) ? (string) $sub : '';
$body    = isset( $body ) ? (string) $body : '';
$align   = ( isset( $align ) && 'left' === $align ) ? ' section-header--left' : '';
$variant = ( isset( $variant ) && 'dark' === $variant ) ? ' section-header--dark' : '';
$ribbon  = ! empty( $ribbon );

if ( '' === $tag && '' === $title && '' === $sub && '' === $eyebrow ) {
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

	<?php if ( '' !== $eyebrow ) : ?>
		<p class="section-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== $sub ) : ?>
		<p class="section-header__sub"><?php echo esc_html( $sub ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== $body ) : ?>
		<p class="section-header__body"><?php echo esc_html( $body ); ?></p>
	<?php endif; ?>
</div>
