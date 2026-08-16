<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

$tag       = isset( $tag ) ? (string) $tag : '';
$title     = isset( $title ) ? (string) $title : '';
$sub       = isset( $sub ) ? (string) $sub : '';
$image     = isset( $image ) ? (string) $image : '';
$buttons   = ( isset( $buttons ) && is_array( $buttons ) ) ? $buttons : array();
$is_reverse = isset( $variant ) && 'reverse' === $variant;
$variant   = $is_reverse ? ' banner--reverse' : '';

$id     = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'banner';
$stamp  = ( isset( $stamp ) && is_array( $stamp ) ) ? $stamp : array();
$stamp_center = trim( (string) ( $stamp['center'] ?? '' ) );

$items = ( isset( $items ) && is_array( $items ) ) ? $items : array();
$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				if ( is_string( $item ) ) {
					return array(
						'icon'  => '',
						'label' => trim( $item ),
					);
				}
				$item = (array) $item;
				return array(
					'icon'  => (string) ( $item['icon'] ?? '' ),
					'label' => trim( (string) ( $item['label'] ?? $item['title'] ?? '' ) ),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['label'];
		}
	)
);

if ( '' === $title && '' === $sub ) {
	return;
}
?>
<?php

?>
<div class="banner tex-paper-aged-a<?php echo esc_attr( $variant ); ?>">
	<div class="banner__content tex-ink-brush-a">
		<?php if ( '' !== $tag ) : ?>
			<span class="banner__tag"><?php echo esc_html( $tag ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== $title ) : ?>
			<h2 class="banner__title"><?php echo wp_kses_post( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $sub ) : ?>
			<p class="banner__sub"><?php echo esc_html( $sub ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $items ) ) : ?>
			<ul class="banner__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="banner__list-item">
						<span class="banner__list-bullet" aria-hidden="true">&#10022;</span>
						<?php if ( '' !== $item['icon'] ) : ?>
							<span class="banner__list-icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
						<?php endif; ?>
						<span><?php echo esc_html( $item['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php if ( ! empty( $buttons ) ) : ?>
			<div class="banner__actions">
				<?php foreach ( $buttons as $btn ) :
					$btn      = (array) $btn;
					$label    = trim( (string) ( $btn['label'] ?? '' ) );
					$route    = (string) ( $btn['route'] ?? '' );
					if ( '' === $label || '' === $route ) {
						continue;
					}
					$is_ghost = 'ghost' === ( $btn['style'] ?? '' );
				?>
					<a class="btn<?php echo $is_ghost ? ' btn--outline' : ''; ?>" href="<?php echo esc_url( RouteService::url( $route ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( '' !== $image ) : ?>
		<div class="banner__media hover-zoom">
			<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy">
		</div>
	<?php endif; ?>
	<?php if ( '' !== $stamp_center ) : ?>
		<div class="banner__stamp<?php echo $is_reverse ? ' banner__stamp--reverse' : ''; ?>">
			<?php
			View::component(
				'stamp/stamp',
				array(
					'id'     => $id . '-stamp',
					'top'    => (string) ( $stamp['top'] ?? '' ),
					'center' => $stamp_center,
					'bottom' => (string) ( $stamp['bottom'] ?? '' ),
					'size'   => isset( $stamp['size'] ) ? (int) $stamp['size'] : 100,
				)
			);
			?>
		</div>
	<?php endif; ?>
</div>
