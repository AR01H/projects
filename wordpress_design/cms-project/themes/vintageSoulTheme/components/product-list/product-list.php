<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$tag   = isset( $tag ) ? trim( (string) $tag ) : '';
$items = isset( $items ) && is_array( $items ) ? $items : array();

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item   = (array) $item;
				$button = (array) ( $item['button'] ?? array() );
				return array(
					'image'  => (string) ( $item['image'] ?? '' ),
					'name'   => trim( (string) ( $item['name'] ?? '' ) ),
					'desc'   => (string) ( $item['desc'] ?? '' ),
					'price'  => (string) ( $item['price'] ?? '' ),
					'button' => array(
						'label' => trim( (string) ( $button['label'] ?? '' ) ),
						'route' => (string) ( $button['route'] ?? '' ),
					),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['name'];
		}
	)
);

if ( empty( $items ) ) {
	return;
}

$edge_shapes = array( 'tex-edge-a', 'tex-edge-rough-a' );
?>
<div class="product-list">
	<?php if ( '' !== $tag ) : ?>
		<span class="badge badge--primary product-list__tag"><?php echo esc_html( $tag ); ?></span>
	<?php endif; ?>
	<ul class="product-list__items">
		<?php foreach ( $items as $i => $item ) : ?>
			<li class="product-list__item card">
				<?php if ( '' !== $item['image'] ) : ?>
					<div class="card__media hover-zoom <?php echo esc_attr( $edge_shapes[ $i % count( $edge_shapes ) ] ); ?>">
						<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy">
					</div>
				<?php endif; ?>
				<div class="card__body">
					<h3 class="card__title product-list__name"><?php echo esc_html( $item['name'] ); ?></h3>
					<?php if ( '' !== $item['desc'] ) : ?>
						<p class="product-list__desc"><?php echo esc_html( $item['desc'] ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $item['price'] ) : ?>
						<span class="product-list__price"><?php echo esc_html( $item['price'] ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $item['button']['label'] && '' !== $item['button']['route'] ) : ?>
						<a class="btn btn--sm product-list__cta" href="<?php echo esc_url( RouteService::url( $item['button']['route'] ) ); ?>"><?php echo esc_html( $item['button']['label'] ); ?></a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
