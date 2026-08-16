<?php

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) && is_array( $items ) ? $items : array();

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'src'      => (string) ( $item['src'] ?? '' ),
					'category' => trim( (string) ( $item['category'] ?? '' ) ),
					'label'    => (string) ( $item['label'] ?? '' ),
					'desc'     => (string) ( $item['desc'] ?? '' ),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['src'];
		}
	)
);

if ( empty( $items ) ) {
	return;
}

$categories = ( isset( $categories ) && is_array( $categories ) ) ? array_values( array_filter( array_map( 'strval', $categories ) ) ) : array();
$id         = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'gallery';
?>
<div class="gallery" id="<?php echo esc_attr( $id ); ?>"<?php echo ! empty( $categories ) ? ' data-vs-gallery' : ''; ?>>
	<?php if ( ! empty( $categories ) ) : ?>
		<div class="gallery__tabs" role="tablist">
			<?php foreach ( $categories as $i => $cat ) : ?>
				<button type="button" class="gallery__tab<?php echo ( 0 === $i ) ? ' is-active' : ''; ?>" data-gallery-filter="<?php echo esc_attr( $cat ); ?>" role="tab" aria-selected="<?php echo ( 0 === $i ) ? 'true' : 'false'; ?>">
					<?php echo esc_html( $cat ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<div class="gallery__grid">
		<?php foreach ( $items as $item ) : ?>
			<?php

			?>
			<figure class="gallery__item hover-zoom tex-film-grain-a tex-dust-a" data-gallery-category="<?php echo esc_attr( $item['category'] ); ?>">
				<img src="<?php echo esc_url( $item['src'] ); ?>" alt="<?php echo esc_attr( '' !== $item['label'] ? $item['label'] : $item['desc'] ); ?>" loading="lazy">
				<?php if ( '' !== $item['label'] ) : ?>
					<figcaption class="gallery__caption">
						<span class="gallery__label"><?php echo esc_html( $item['label'] ); ?></span>
						<?php if ( '' !== $item['desc'] ) : ?>
							<span class="gallery__desc"><?php echo esc_html( $item['desc'] ); ?></span>
						<?php endif; ?>
					</figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
</div>
