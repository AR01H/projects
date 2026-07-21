<?php
/**
 * Signature Bottled Blends - vintage juice-bottle lineup.
 *
 * A horizontal "shelf" of sepia-toned bottled drinks, matching the reference
 * design's signature-flavours row. Everything (bottle images, names, taglines,
 * heading, button) is read from admin/data/signature_flavours.json - nothing
 * is hardcoded. Sepia tone is applied in CSS so any photo blends into the
 * vintage palette.
 */
defined( 'ABSPATH' ) || exit;

$data    = nt_data( 'signature_flavours' );
$bottles = $data['bottles'] ?? array();
if ( empty( $bottles ) ) {
	return;
}

$tag    = $data['tag']   ?? '';
$title  = $data['title'] ?? '';
$sub    = $data['sub']   ?? '';
$button = $data['button'] ?? array();
?>
<section class="nt-bottles" id="signature-bottles">
	<div class="container">

		<div class="nt-bottles__header">
			<?php if ( $tag ) : ?>
				<span class="nt-section-tag"><?php echo esc_html( $tag ); ?></span>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="nt-bottles__title"><?php echo wp_kses( $title, array( 'em' => array(), 'span' => array( 'class' => array() ) ) ); ?></h2>
			<?php endif; ?>
			<?php if ( $sub ) : ?>
				<p class="nt-bottles__sub"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
		</div>

		<div class="nt-bottles__shelf">
			<?php foreach ( $bottles as $bottle ) :
				$bottle  = (array) $bottle;
				$name    = $bottle['name']    ?? '';
				$tagline = $bottle['tagline'] ?? '';
				$image   = $bottle['image']   ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
			?>
				<figure class="nt-bottle">
					<div class="nt-bottle__frame">
						<?php if ( $image ) : ?>
							<img src="<?php echo esc_url( $image ); ?>"
							     alt="<?php echo esc_attr( $name ); ?>"
							     class="nt-bottle__img"
							     loading="lazy">
						<?php else : ?>
							<span class="nt-bottle__placeholder" aria-hidden="true">🍾</span>
						<?php endif; ?>
					</div>
					<figcaption class="nt-bottle__cap">
						<span class="nt-bottle__name"><?php echo esc_html( $name ); ?></span>
						<?php if ( $tagline ) : ?>
							<span class="nt-bottle__tagline"><?php echo esc_html( $tagline ); ?></span>
						<?php endif; ?>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $button['label'] ) ) : ?>
			<div class="nt-bottles__cta">
				<a href="<?php echo esc_url( nt_link( $button['url'] ?? '#' ) ); ?>" class="btn">
					<?php echo esc_html( $button['label'] ); ?> &rarr;
				</a>
			</div>
		<?php endif; ?>

	</div>
</section>
