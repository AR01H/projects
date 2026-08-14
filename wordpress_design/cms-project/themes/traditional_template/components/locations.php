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
$data       = App_Helpers::data( $loc_source );
$items      = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}
$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
?>
<section class="app-locations" id="locations">
	<div class="container">

		<?php get_template_part( 'components/section-heading/section-header', null, array(
	'tag'   => $tag ?? '',
	'title' => $title ?? '',
	'body'  => $sub ?? ''
) ); ?>

		<div class="app-locations__grid">
			<?php foreach ( $items as $item ) :
				$item = (array) $item;
				$name = $item['name'] ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
				$phone = $item['phone'] ?? '';
			?>
				<article class="app-location">
					<h3 class="app-location__name"><?php echo esc_html( $name ); ?></h3>
					<?php if ( ! empty( $item['address'] ) ) : ?>
						<p class="app-location__addr"><?php echo esc_html( $item['address'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $item['hours'] ) ) : ?>
						<p class="app-location__meta"><?php echo esc_html( $item['hours'] ); ?></p>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<a class="app-location__meta app-location__phone"
						   href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
							<?php echo esc_html( $phone ); ?>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $item['map_url'] ) ) : ?>
						<a class="app-location__link" href="<?php echo esc_url( $item['map_url'] ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Get directions', NT_TEXT_DOMAIN ); ?> &rarr;
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
