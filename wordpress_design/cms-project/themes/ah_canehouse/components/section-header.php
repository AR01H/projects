<?php
/**
 * Centered section header: eyebrow tag + heading + optional body paragraph.
 *
 * Args (all optional):
 *  tag           string  Eyebrow label text
 *  tag_class     string  Class on the eyebrow div   (default: 'section-tag')
 *  tag_style     string  Inline style on the eyebrow div
 *  title         string  Heading HTML – wp_kses'd (span/em/strong allowed)
 *  title_tag     string  Heading element h1|h2|h3   (default: 'h2')
 *  title_class   string  Class on the heading       (default: 'section-title')
 *  title_style   string  Inline style on the heading
 *  body          string  Body paragraph text
 *  body_class    string  Class on the <p>            (default: 'section-body')
 *  body_style    string  Inline style on the <p>
 *  dark          bool    Preset dark-background colour overrides
 *  wrapper_class string  Extra class on the wrapper div
 *  wrapper_style string  Inline style on the wrapper div
 *  wrapper_base  string  Base class(es) for wrapper (default: 'ch-section-center fade-up')
 *  no_wrapper    bool    Output elements with no surrounding div (for embedded layouts)
 */
defined( 'ABSPATH' ) || exit;

$tag           = $args['tag']           ?? '';
$tag_class     = $args['tag_class']     ?? 'section-tag';
$tag_style     = $args['tag_style']     ?? '';
$title         = $args['title']         ?? '';
$title_tag_raw = $args['title_tag']     ?? 'h2';
$title_tag     = in_array( $title_tag_raw, [ 'h1', 'h2', 'h3' ], true ) ? $title_tag_raw : 'h2';
$title_class   = $args['title_class']   ?? 'section-title';
$title_style   = $args['title_style']   ?? '';
$body          = $args['body']          ?? '';
$body_class    = $args['body_class']    ?? 'section-body';
$body_style    = $args['body_style']    ?? '';
$dark          = ! empty( $args['dark'] );
$wrapper_class = $args['wrapper_class'] ?? '';
$wrapper_style = $args['wrapper_style'] ?? '';
$wrapper_base  = $args['wrapper_base']  ?? 'ch-section-center fade-up';
$no_wrapper    = ! empty( $args['no_wrapper'] );

if ( ! $tag && ! $title && ! $body ) return;

$allowed_kses = [
	'span'   => [ 'class' => [], 'style' => [] ],
	'em'     => [],
	'strong' => [],
	'br'     => [],
];

if ( $dark ) {
	$wrapper_style = $wrapper_style ?: 'color:var(--client-color-11)';
	$tag_style     = $tag_style     ?: 'color:var(--client-color-7)';
	$title_style   = $title_style   ?: 'color:var(--client-color-11)';
	$body_style    = $body_style    ?: 'color:rgba(255,255,255,0.65)';
}

$wrapper_cls = trim( $wrapper_base . ' ' . $wrapper_class );
?>
<?php if ( ! $no_wrapper ) : ?>
<div class="<?php echo esc_attr( $wrapper_cls ); ?>"<?php echo $wrapper_style ? ' style="' . esc_attr( $wrapper_style ) . '"' : ''; ?>>
<?php endif; ?>
	<?php if ( $tag ) : ?>
		<div class="<?php echo esc_attr( $tag_class ); ?>"<?php echo $tag_style ? ' style="' . esc_attr( $tag_style ) . '"' : ''; ?>><?php echo esc_html( $tag ); ?></div>
	<?php endif; ?>
	<?php if ( $title ) : ?>
		<<?php echo $title_tag; ?> class="<?php echo esc_attr( $title_class ); ?>"<?php echo $title_style ? ' style="' . esc_attr( $title_style ) . '"' : ''; ?>><?php echo wp_kses( $title, $allowed_kses ); ?></<?php echo $title_tag; ?>>
	<?php endif; ?>
	<?php if ( $body ) : ?>
		<p class="<?php echo esc_attr( $body_class ); ?>"<?php echo $body_style ? ' style="' . esc_attr( $body_style ) . '"' : ''; ?>><?php echo esc_html( $body ); ?></p>
	<?php endif; ?>
<?php if ( ! $no_wrapper ) : ?>
</div>
<?php endif; ?>
