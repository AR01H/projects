<?php
/**
 * Call-to-action banner - a full-width vintage band with a headline, blurb and
 * one or two buttons.
 *
 * GENERIC + per-page: point it at a different JSON block on every page via
 *   page_sections.json -> { "component": "cta-banner", "args": { "source": "cta_franchise" } }
 * Source shape: { tag, title (em allowed), sub, image, buttons[] { label, url, style } }
 *   style: "primary" (default) | "ghost"
 *
 * Renders nothing without a title.
 */
defined( 'ABSPATH' ) || exit;

$cta_source = ( isset( $source ) && $source ) ? (string) $source : 'cta_default';
$data       = nt_data( $cta_source );
$title      = ( is_array( $data ) && ! empty( $data['title'] ) ) ? $data['title'] : '';
if ( '' === $title ) {
	return;
}

$tag     = $data['tag']   ?? '';
$sub     = $data['sub']   ?? '';
$image   = $data['image'] ?? '';
$buttons = ( ! empty( $data['buttons'] ) ) ? (array) $data['buttons'] : array();
?>
<section class="nt-cta-band<?php echo $image ? ' nt-cta-band--photo' : ''; ?>"
	<?php if ( $image ) : ?>style="background-image:url('<?php echo esc_url( $image ); ?>');"<?php endif; ?>>
	<?php if ( $image ) : ?><span class="nt-cta-band__scrim" aria-hidden="true"></span><?php endif; ?>
	<div class="container nt-cta-band__inner">
		<?php if ( $tag ) : ?><span class="nt-cta-band__tag"><?php echo esc_html( $tag ); ?></span><?php endif; ?>
		<h2 class="nt-cta-band__title"><?php echo wp_kses( $title, array( 'em' => array(), 'br' => array() ) ); ?></h2>
		<?php if ( $sub ) : ?><p class="nt-cta-band__sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>

		<?php if ( ! empty( $buttons ) ) : ?>
			<div class="nt-cta-band__actions">
				<?php foreach ( $buttons as $btn ) :
					$btn   = (array) $btn;
					$label = $btn['label'] ?? '';
					if ( '' === trim( (string) $label ) ) {
						continue;
					}
					$style = ( 'ghost' === ( $btn['style'] ?? '' ) ) ? ' nt-cta-band__btn--ghost' : '';
				?>
					<a class="btn nt-cta-band__btn<?php echo esc_attr( $style ); ?>"
					   href="<?php echo esc_url( nt_link( $btn['url'] ?? '#' ) ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
