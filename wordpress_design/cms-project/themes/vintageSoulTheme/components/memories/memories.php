<?php

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) && is_array( $items ) ? $items : array();

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'image'   => (string) ( $item['image'] ?? '' ),
					'caption' => trim( (string) ( $item['caption'] ?? '' ) ),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['image'];
		}
	)
);

if ( empty( $items ) ) {
	return;
}

$presets = array( 'a', 'b', 'c', 'd', 'e' );
?>
<?php

?>
<div class="memories tex-leaf-fall-a">
	<?php foreach ( $items as $i => $item ) :
		$preset = $presets[ $i % count( $presets ) ];
	?>
		<figure class="memories__item">
			<div class="memory-card-<?php echo esc_attr( $preset ); ?>">
				<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['caption'] ); ?>" loading="lazy">
			</div>
			<?php if ( '' !== $item['caption'] ) : ?>
				<figcaption class="memories__caption"><?php echo esc_html( $item['caption'] ); ?></figcaption>
			<?php endif; ?>
		</figure>
	<?php endforeach; ?>
</div>
