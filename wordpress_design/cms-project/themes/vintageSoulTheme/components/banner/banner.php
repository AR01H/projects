<?php
/**
 * VintageSoulTheme - Editorial Banner & Multi-Feature Showcase
 * Follows full luxury vintage design system: ornate frames, 2-column feature card grids, and botanical accents.
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$tag        = isset( $tag ) ? (string) $tag : '';
$title      = isset( $title ) ? (string) $title : '';
$sub        = isset( $sub ) ? (string) $sub : '';
$image      = isset( $image ) ? UrlHelper::resolve( (string) $image ) : '';
$image_alt  = isset( $image_alt ) ? (string) $image_alt : (string) strip_tags( $title );
$video      = isset( $video ) ? (string) $video : '';
$buttons    = ( isset( $buttons ) && is_array( $buttons ) ) ? $buttons : array();
$is_reverse = isset( $variant ) && 'reverse' === $variant;
$variant    = $is_reverse ? ' banner--reverse' : '';

$id           = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'banner';
$stamp        = ( isset( $stamp ) && is_array( $stamp ) ) ? $stamp : array();
$stamp_center = trim( (string) ( $stamp['center'] ?? '' ) );

$body  = ( isset( $body ) && is_array( $body ) ) ? array_values( array_filter( array_map( 'trim', array_map( 'strval', $body ) ) ) ) : array();
$items = ( isset( $items ) && is_array( $items ) ) ? $items : array();

if ( '' === $title && '' === $sub && empty( $items ) ) {
	return;
}
?>
<div class="banner banner--vintage<?php echo esc_attr( $variant ); ?>">
	
	<!-- 1. Left Editorial Content & Feature Cards -->
	<div class="banner__content">
		<?php if ( '' !== $tag ) : ?>
			<span class="banner__tag section-eyebrow"><?php echo esc_html( $tag ); ?></span>
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
			<div class="banner__cards-grid">
				<?php foreach ( $items as $item ) :
					$item_title = is_array( $item ) ? (string) ( $item['title'] ?? $item['label'] ?? '' ) : (string) $item;
					$item_desc  = is_array( $item ) ? (string) ( $item['desc'] ?? $item['text'] ?? '' ) : '';
					$item_icon  = is_array( $item ) ? (string) ( $item['icon'] ?? 'leaf' ) : 'sugarcane';
					$item_tag   = is_array( $item ) ? (string) ( $item['tag'] ?? '' ) : '';
					$icon_svg   = IconHelper::render( $item_icon, '#8e622d', 20 );
				?>
					<div class="banner-card card--rough-cut">
						<div class="banner-card__header">
							<span class="banner-card__icon"><?php echo $icon_svg; // phpcs:ignore ?></span>
							<?php if ( '' !== $item_tag ) : ?>
								<span class="banner-card__tag"><?php echo esc_html( $item_tag ); ?></span>
							<?php endif; ?>
						</div>
						<h3 class="banner-card__title"><?php echo esc_html( $item_title ); ?></h3>
						<?php if ( '' !== $item_desc ) : ?>
							<p class="banner-card__desc"><?php echo esc_html( $item_desc ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $buttons ) ) : ?>
			<div class="banner__actions">
				<?php foreach ( $buttons as $idx => $btn ) :
					$btn       = (array) $btn;
					$label     = trim( (string) ( $btn['label'] ?? '' ) );
					$route     = (string) ( $btn['route'] ?? 'contact' );
					$b_icon    = (string) ( $btn['icon'] ?? '' );
					$is_ghost  = 'ghost' === ( $btn['style'] ?? '' ) || $idx > 0;
					$btn_class = $is_ghost ? 'btn--secondary-vintage btn--outline-vintage' : 'btn--primary-vintage';
				?>
					<a class="btn <?php echo esc_attr( $btn_class ); ?>" href="<?php echo esc_url( RouteService::url( $route ) ); ?>">
						<?php if ( '' !== $b_icon ) : ?>
							<span class="btn__icon"><?php echo IconHelper::render( $b_icon, '#f6d599', 15 ); // phpcs:ignore ?></span>
						<?php endif; ?>
						<span><?php echo esc_html( $label ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<!-- 2. Right Framed Media Stage -->
	<?php if ( '' !== $video || '' !== $image ) : ?>
		<div class="banner__media-wrap">
			<div class="banner__photo-frame frame--ornate hover-zoom">
				<?php if ( '' !== $video ) : ?>
					<video
						src="<?php echo esc_url( $video ); ?>"
						<?php echo ( '' !== $image ) ? 'poster="' . esc_url( $image ) . '"' : ''; ?>
						muted
						loop
						playsinline
						autoplay
						preload="metadata"
						class="banner__img"
					></video>
				<?php else : ?>
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy" class="banner__img">
				<?php endif; ?>

				<?php if ( '' !== $stamp_center || ! empty( $stamp ) ) :
					$top_txt = (string) ( $stamp['top'] ?? 'EST. 2014' );
					$ctr_txt = '' !== $stamp_center ? $stamp_center : (string) ( $stamp['center'] ?? '100% PURE' );
					$btm_txt = (string) ( $stamp['bottom'] ?? 'HERITAGE' );
				?>
					<div class="banner__stamp stamp-circle">
						<span class="stamp-circle__line1"><?php echo esc_html( $top_txt ); ?></span>
						<span class="stamp-circle__line2"><?php echo esc_html( $ctr_txt ); ?></span>
						<span class="stamp-circle__line3"><?php echo esc_html( $btm_txt ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

</div>
