<?php
/**
 * Locations - a "find us" grid of outlets / branches.
 *
 * GENERIC: any list of places (stores, clinics, venues, depots). Switch data
 * per page with `source`.
 * Data: { tag, title (em allowed), sub, items[] { name, address, hours, phone, map_url } }
 */
defined( 'ABSPATH' ) || exit;

$loc_source = ( isset( $source ) && $source ) ? (string) $source : 'locations';
$data       = nt_data( $loc_source );
$items      = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}
$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
?>
<section class="nt-locations" id="locations">
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

		<div class="nt-locations__grid">
			<?php foreach ( $items as $item ) :
				$item = (array) $item;
				$name = $item['name'] ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
				$phone = $item['phone'] ?? '';
			?>
				<article class="nt-location">
					<h3 class="nt-location__name"><?php echo esc_html( $name ); ?></h3>
					<?php if ( ! empty( $item['address'] ) ) : ?>
						<p class="nt-location__addr"><?php echo esc_html( $item['address'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $item['hours'] ) ) : ?>
						<p class="nt-location__meta"><?php echo esc_html( $item['hours'] ); ?></p>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<a class="nt-location__meta nt-location__phone"
						   href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
							<?php echo esc_html( $phone ); ?>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $item['map_url'] ) ) : ?>
						<a class="nt-location__link" href="<?php echo esc_url( $item['map_url'] ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Get directions', NT_TEXT_DOMAIN ); ?> &rarr;
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
