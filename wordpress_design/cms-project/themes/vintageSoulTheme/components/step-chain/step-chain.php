<?php

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) && is_array( $items ) ? $items : array();

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'image'  => (string) ( $item['image'] ?? '' ),
					'icon'   => (string) ( $item['icon'] ?? '' ),
					'number' => (string) ( $item['number'] ?? '' ),
					// Content JSON in this theme names this field 'title' as often as
					// 'label' (serve-steps.json, lifecycle.json). Accept both, or the
					// filter below drops every item and the section renders empty.
					'label'  => trim( (string) ( $item['label'] ?? $item['title'] ?? '' ) ),
					'desc'   => (string) ( $item['desc'] ?? '' ),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['label'];
		}
	)
);

if ( empty( $items ) ) {
	return;
}

$count = count( $items );
?>
<ol class="step-chain">
	<?php foreach ( $items as $i => $item ) : ?>
		<li class="step-chain__item">
			<span class="step-chain__circle" aria-hidden="true">
				<?php if ( '' !== $item['image'] ) : ?>
					<img src="<?php echo esc_url( $item['image'] ); ?>" alt="" loading="lazy">
				<?php elseif ( '' !== $item['icon'] ) : ?>
					<span class="step-chain__icon"><?php echo esc_html( $item['icon'] ); ?></span>
				<?php endif; ?>
			</span>
			<span class="step-chain__label">
				<?php if ( '' !== $item['number'] ) : ?>
					<span class="step-chain__number"><?php echo esc_html( $item['number'] ); ?>.</span>
				<?php endif; ?>
				<?php echo esc_html( $item['label'] ); ?>
			</span>
			<?php if ( '' !== $item['desc'] ) : ?>
				<p class="step-chain__desc"><?php echo esc_html( $item['desc'] ); ?></p>
			<?php endif; ?>
		</li>
		<?php if ( $i < $count - 1 ) : ?>
			<li class="step-chain__arrow" aria-hidden="true">&rarr;</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ol>
