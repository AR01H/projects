<?php

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) && is_array( $items ) ? $items : array();

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'image' => (string) ( $item['image'] ?? '' ),
					'label' => trim( (string) ( $item['label'] ?? '' ) ),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['image'] && '' !== $item['label'];
		}
	)
);

if ( empty( $items ) ) {
	return;
}
?>
<div class="photo-grid">
	<?php foreach ( $items as $item ) : ?>
		<div class="photo-grid__item">
			<div class="photo-grid__media hover-zoom">
				<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['label'] ); ?>" loading="lazy">
			</div>
			<span class="photo-grid__label"><?php echo esc_html( $item['label'] ); ?></span>
		</div>
	<?php endforeach; ?>
</div>
