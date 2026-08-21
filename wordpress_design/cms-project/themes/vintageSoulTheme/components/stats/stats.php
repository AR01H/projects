<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\Formatter;

$items  = isset( $items ) && is_array( $items ) ? $items : array();
$ground = ! isset( $ground ) || (bool) $ground;

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'label' => trim( (string) ( $item['label'] ?? '' ) ),
					'value' => $item['value'] ?? null,
					'icon'  => sanitize_file_name( (string) ( $item['icon'] ?? '' ) ),
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
<div class="stats<?php echo $ground ? ' tex-ground-cane-a' : ''; ?>">
	<?php foreach ( $items as $item ) : ?>
		<div class="stats__item">
			<?php
			// Resolve the icon name against a real file rather than trusting the
			// JSON value straight into a url().
			$icon_uri = '';
			if ( '' !== $item['icon'] && is_file( VINTAGESOUL_DIR . '/assets/svg/icons/' . $item['icon'] . '.svg' ) ) {
				$icon_uri = VINTAGESOUL_URI . '/assets/svg/icons/' . $item['icon'] . '.svg';
			}
			?>
			<?php if ( '' !== $icon_uri ) : ?>
				<span class="stats__icon" aria-hidden="true" style="--stats-icon: url('<?php echo esc_url( $icon_uri ); ?>');"></span>
			<?php endif; ?>
			<span class="stats__value"><?php echo esc_html( Formatter::compact_number( (float) $item['value'] ) ); ?></span>
			<span class="stats__label"><?php echo esc_html( $item['label'] ); ?></span>
		</div>
	<?php endforeach; ?>
</div>
