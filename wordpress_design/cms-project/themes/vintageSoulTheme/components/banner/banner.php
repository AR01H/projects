<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

$tag       = isset( $tag ) ? (string) $tag : '';
$title     = isset( $title ) ? (string) $title : '';
$sub       = isset( $sub ) ? (string) $sub : '';
$image     = isset( $image ) ? (string) $image : '';
$image_alt = isset( $image_alt ) ? (string) $image_alt : '';
$video     = isset( $video ) ? (string) $video : ''; // optional - image doubles as its poster, same convention hero-carousel already uses
$buttons   = ( isset( $buttons ) && is_array( $buttons ) ) ? $buttons : array();
$is_reverse = isset( $variant ) && 'reverse' === $variant;
$variant   = $is_reverse ? ' banner--reverse' : '';

$id     = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'banner';
$stamp  = ( isset( $stamp ) && is_array( $stamp ) ) ? $stamp : array();
$stamp_center = trim( (string) ( $stamp['center'] ?? '' ) );

$body  = ( isset( $body ) && is_array( $body ) ) ? array_values( array_filter( array_map( 'trim', array_map( 'strval', $body ) ) ) ) : array();
$items = ( isset( $items ) && is_array( $items ) ) ? $items : array();
$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				if ( is_string( $item ) ) {
					return array(
						'icon'  => '',
						'label' => trim( $item ),
						'text'  => '',
					);
				}
				$item = (array) $item;
				return array(
					'icon'  => (string) ( $item['icon'] ?? '' ),
					'label' => trim( (string) ( $item['label'] ?? $item['title'] ?? '' ) ),
					'text'  => trim( (string) ( $item['text'] ?? $item['note'] ?? '' ) ),
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
			<p class="banner__sub"><?php echo wp_kses_post( $sub ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $body ) ) : ?>
			<?php foreach ( $body as $paragraph ) : ?>
				<p class="banner__body"><?php echo wp_kses_post( $paragraph ); ?></p>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( ! empty( $items ) ) : ?>
			<ul class="banner__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="banner__list-item">
						<span class="banner__list-bullet" aria-hidden="true">&#10022;</span>
						<?php if ( '' !== $item['icon'] ) : ?>
							<span class="banner__list-icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
						<?php endif; ?>
						<span class="banner__list-label">
							<?php echo esc_html( $item['label'] ); ?>
							<?php if ( '' !== $item['text'] ) : ?>
								<small class="banner__list-note"><?php echo esc_html( $item['text'] ); ?></small>
							<?php endif; ?>
						</span>
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
	<?php if ( '' !== $video ) : ?>
		<div class="banner__media hover-zoom">
			<video
				src="<?php echo esc_url( $video ); ?>"
				<?php echo ( '' !== $image ) ? 'poster="' . esc_url( $image ) . '"' : ''; ?>
				muted
				loop
				playsinline
				autoplay
				preload="metadata"
			></video>
		</div>
	<?php elseif ( '' !== $image ) : ?>
		<div class="banner__media hover-zoom">
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
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
