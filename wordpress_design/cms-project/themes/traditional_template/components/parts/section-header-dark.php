<?php
/**
 * Section header - dark-background variant.
 *
 * Same eyebrow-tag + accented-title + body pattern used across the vintage
 * design (see .nt-flavours__title, .nt-bottles__title, .nt-reviews__title),
 * but in gold/cream so it reads on a dark photo or wood background instead
 * of the light parchment used by components/parts/section-header.php.
 *
 * Args (all optional):
 *  tag     string  Eyebrow label.
 *  title   string  Heading HTML - wrap the accent word in <em> for gold.
 *  body    string  Sub-paragraph.
 *  class   string  Extra class on the wrapper.
 */
defined( 'ABSPATH' ) || exit;

$tag   = $args['tag']   ?? '';
$title = $args['title'] ?? '';
$body  = $args['body']  ?? '';
$class = $args['class'] ?? '';

if ( ! $tag && ! $title && ! $body ) {
	return;
}
?>
<div class="nt-section-header-dark<?php echo $class ? ' ' . esc_attr( $class ) : ''; ?>">
	<?php if ( $tag ) : ?>
		<span class="nt-section-header-dark__tag"><?php echo esc_html( $tag ); ?></span>
	<?php endif; ?>
	<?php if ( $title ) : ?>
		<h2 class="nt-section-header-dark__title"><?php echo wp_kses( $title, array( 'em' => array(), 'span' => array( 'class' => array() ), 'br' => array() ) ); ?></h2>
	<?php endif; ?>
	<?php if ( $body ) : ?>
		<p class="nt-section-header-dark__body"><?php echo esc_html( $body ); ?></p>
	<?php endif; ?>
</div>
