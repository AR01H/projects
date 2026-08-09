<?php
/**
 * Signature Bottled Blends - vintage juice-bottle lineup.
 *
 * A horizontal "shelf" of sepia-toned bottled drinks, matching the reference
 * design's signature-flavours row. Everything (bottle images, names, taglines,
 * heading, button) is read from admin/data/signature_flavours.json - nothing
 * is hardcoded. Sepia tone is applied in CSS so any photo blends into the
 * vintage palette.
 *
 * Switch data per page with `source` (defaults to signature_flavours).
 */
defined( 'ABSPATH' ) || exit;

$sf_source = ( isset( $source ) && $source ) ? (string) $source : 'signature_flavours';
$data      = App_Helpers::data( $sf_source );
$bottles   = $data['bottles'] ?? array();
if ( empty( $bottles ) ) {
	return;
}

$tag    = $data['tag']   ?? '';
$title  = $data['title'] ?? '';
$sub    = $data['sub']   ?? '';
$button = $data['button'] ?? array();
?>
<section class="app-bottles" id="signature-bottles">
	<div class="container">

		<div class="app-bottles__header">
			<?php if ( $tag ) : ?>
				<span class="app-section-tag"><?php echo esc_html( $tag ); ?></span>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="app-bottles__title"><?php echo wp_kses( $title, array( 'em' => array(), 'span' => array( 'class' => array() ) ) ); ?></h2>
			<?php endif; ?>
			<?php if ( $sub ) : ?>
				<p class="app-bottles__sub"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
		</div>

		<div class="app-bottles__shelf">
			<?php foreach ( $bottles as $bottle ) :
				$bottle  = (array) $bottle;
				$name    = $bottle['name']    ?? '';
				$tagline = $bottle['tagline'] ?? '';
				$image   = $bottle['image']   ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
			?>
				<figure class="app-bottle">
					<div class="app-bottle__frame">
						<?php if ( $image ) : ?>
							<img src="<?php echo esc_url( $image ); ?>"
							     alt="<?php echo esc_attr( $name ); ?>"
							     class="app-bottle__img"
							     loading="lazy">
						<?php else : ?>
							<span class="app-bottle__placeholder" aria-hidden="true">🍾</span>
						<?php endif; ?>
					</div>
					<figcaption class="app-bottle__cap">
						<span class="app-bottle__name"><?php echo esc_html( $name ); ?></span>
						<?php if ( $tagline ) : ?>
							<span class="app-bottle__tagline"><?php echo esc_html( $tagline ); ?></span>
						<?php endif; ?>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $button['label'] ) ) : ?>
			<div class="app-bottles__cta">
				<a href="<?php echo esc_url( App_Helpers::link( $button['url'] ?? '#' ) ); ?>" class="btn">
					<?php echo esc_html( $button['label'] ); ?> &rarr;
				</a>
			</div>
		<?php endif; ?>

	</div>
</section>
