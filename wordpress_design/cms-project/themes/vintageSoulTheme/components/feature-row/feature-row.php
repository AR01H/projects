<?php

defined( 'ABSPATH' ) || exit;

$known_svg_icons = array( 'leaf', 'flame', 'wheat', 'building', 'scooter', 'bottles' );

$items = isset( $items ) && is_array( $items ) ? $items : array();

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) use ( $known_svg_icons ) {
				$item      = (array) $item;
				$icon_name = (string) ( $item['icon'] ?? '' );
				return array(
					'icon'     => $icon_name,
					'icon_svg' => in_array( $icon_name, $known_svg_icons, true ) ? $icon_name : '',
					'title'    => trim( (string) ( $item['label'] ?? $item['title'] ?? '' ) ),
					'text'     => (string) ( $item['note'] ?? $item['text'] ?? '' ),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['title'];
		}
	)
);

if ( empty( $items ) ) {
	return;
}

$number  = isset( $number ) ? (string) $number : '';
$heading = isset( $heading ) ? (string) $heading : '';
$boxed   = isset( $variant ) && 'boxed' === $variant;
$class   = 'feature-row' . ( $boxed ? ' feature-row--boxed' : '' );
?>
<div class="<?php echo esc_attr( $class ); ?>">
	<?php if ( $boxed && ( '' !== $number || '' !== $heading ) ) : ?>
		<span class="feature-row__tab">
			<?php if ( '' !== $number ) : ?><span class="feature-row__tab-number"><?php echo esc_html( $number ); ?></span><?php endif; ?>
			<?php if ( '' !== $heading ) : ?><span class="feature-row__tab-label"><?php echo esc_html( $heading ); ?></span><?php endif; ?>
		</span>
	<?php endif; ?>
	<div class="feature-row__items">
		<?php foreach ( $items as $item ) : ?>
			<div class="feature-row__item">
				<?php if ( '' !== $item['icon_svg'] ) : ?>
					<span class="feature-row__icon feature-row__icon--svg feature-row__icon--<?php echo esc_attr( $item['icon_svg'] ); ?>" aria-hidden="true"></span>
				<?php elseif ( '' !== $item['icon'] ) : ?>
					<span class="feature-row__icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
				<?php endif; ?>
				<h3 class="feature-row__title"><?php echo esc_html( $item['title'] ); ?></h3>
				<?php if ( '' !== $item['text'] ) : ?>
					<p class="feature-row__text"><?php echo esc_html( $item['text'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
