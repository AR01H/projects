<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\Formatter;

$items = isset( $items ) && is_array( $items ) ? $items : array();

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'label' => trim( (string) ( $item['label'] ?? '' ) ),
					'value' => $item['value'] ?? null,
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['label'] && null !== $item['value'] && is_numeric( $item['value'] );
		}
	)
);

if ( empty( $items ) ) {
	return;
}
?>
<?php

?>
<div class="stats tex-ground-cane-a">
	<?php foreach ( $items as $item ) : ?>
		<div class="stats__item">
			<span class="stats__value"><?php echo esc_html( Formatter::compact_number( (float) $item['value'] ) ); ?></span>
			<span class="stats__label"><?php echo esc_html( $item['label'] ); ?></span>
		</div>
	<?php endforeach; ?>
</div>
