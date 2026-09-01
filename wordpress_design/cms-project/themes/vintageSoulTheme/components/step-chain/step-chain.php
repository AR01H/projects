<?php

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) && is_array( $items ) ? $items : array();

$default_icons = array( '📅', '🍹', '🌿', '✨', '🤝', '🌾' );

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'image'  => (string) ( $item['image'] ?? '' ),
					'icon'   => (string) ( $item['icon'] ?? '' ),
					'number' => (string) ( $item['number'] ?? '' ),
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
<div class="step-chain-wrapper">
	<ol class="step-chain-grid">
		<?php foreach ( $items as $i => $item ) :
			$step_num  = '' !== $item['number'] ? str_pad( $item['number'], 2, '0', STR_PAD_LEFT ) : str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );
			$step_icon = '' !== $item['icon'] ? $item['icon'] : ( $default_icons[ $i % count( $default_icons ) ] );
		?>
			<li class="step-chain-card frame--ornate">
				<div class="step-chain-card__header">
					<span class="step-chain-card__badge">
						<span class="step-chain-card__step-text">STEP</span>
						<strong class="step-chain-card__number"><?php echo esc_html( $step_num ); ?></strong>
					</span>
					<?php if ( '' !== $item['image'] ) : ?>
						<div class="step-chain-card__media">
							<img src="<?php echo esc_url( $item['image'] ); ?>" alt="" loading="lazy">
						</div>
					<?php else : ?>
						<span class="step-chain-card__icon" aria-hidden="true"><?php echo esc_html( $step_icon ); ?></span>
					<?php endif; ?>
				</div>

				<div class="step-chain-card__body">
					<h3 class="step-chain-card__title"><?php echo esc_html( $item['label'] ); ?></h3>
					<?php if ( '' !== $item['desc'] ) : ?>
						<p class="step-chain-card__desc"><?php echo esc_html( $item['desc'] ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $i < $count - 1 ) : ?>
					<div class="step-chain-card__connector" aria-hidden="true">
						<span class="step-chain-card__arrow">→</span>
					</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</div>

