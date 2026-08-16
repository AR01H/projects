<?php

defined( 'ABSPATH' ) || exit;

$tag     = isset( $tag ) ? (string) $tag : '';
$title   = isset( $title ) ? (string) $title : '';
$sub     = isset( $sub ) ? (string) $sub : '';
$align   = ( isset( $align ) && 'left' === $align ) ? ' section-header--left' : '';
$variant = ( isset( $variant ) && 'dark' === $variant ) ? ' section-header--dark' : '';

if ( '' === $tag && '' === $title && '' === $sub ) {
	return;
}
?>
<div class="section-header<?php echo esc_attr( $align . $variant ); ?>">
	<?php if ( '' !== $tag ) : ?>
		<span class="section-header__tag"><?php echo esc_html( $tag ); ?></span>
	<?php endif; ?>
	<?php if ( '' !== $title ) : ?>
		<h2 class="section-header__title"><?php echo wp_kses_post( $title ); ?></h2>
	<?php endif; ?>
	<?php if ( '' !== $sub ) : ?>
		<p class="section-header__sub"><?php echo esc_html( $sub ); ?></p>
	<?php endif; ?>
</div>
