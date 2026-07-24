<?php
/**
 * Values - larger principle cards (icon, title, copy) with an optional aside image.
 *
 * GENERIC: values, promises, guarantees, "why us" pillars. Richer than the
 * compact feature-badges strip - use badges for a one-line strip, this for
 * explained pillars. Switch data per page with `source`.
 * Data: { tag, title (em allowed), sub, image, alt, items[] { icon, title, text } }
 */
defined( 'ABSPATH' ) || exit;

$vl_source = ( isset( $source ) && $source ) ? (string) $source : 'values';
$data      = nt_data( $vl_source );
$items     = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}
$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
$image = $data['image'] ?? '';
$alt   = $data['alt']   ?? '';
?>
<section class="nt-values" id="values">
	<div class="container">

		<?php if ( $tag || $title || $sub ) : ?>
			<div class="nt-section-center">
				<?php if ( $tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?>
					<h2 class="section-title"><?php echo wp_kses( $title, array( 'em' => array() ) ); ?></h2>
				<?php endif; ?>
				<?php if ( $sub ) : ?><p class="section-body"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nt-values__layout<?php echo $image ? ' nt-values__layout--split' : ''; ?>">
			<div class="nt-values__grid">
				<?php foreach ( $items as $item ) :
					$item  = (array) $item;
					$v_ttl = $item['title'] ?? '';
					if ( '' === trim( (string) $v_ttl ) ) {
						continue;
					}
				?>
					<article class="nt-value">
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<span class="nt-value__icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
						<?php endif; ?>
						<h3 class="nt-value__title"><?php echo esc_html( $v_ttl ); ?></h3>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<p class="nt-value__text"><?php echo esc_html( $item['text'] ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>

			<?php if ( $image ) : ?>
				<figure class="nt-values__media">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
				</figure>
			<?php endif; ?>
		</div>

	</div>
</section>
