<?php
/**
 * carousel_image_view - standalone, continuous auto-scroll image/video strip (marquee style).
 *
 * Pure CSS infinite scroll: the item set is rendered twice and the track is
 * animated by exactly one set-width so the loop is perfectly seamless.
 *
 * get_template_part( 'components/carousels/carousel_image_view', null, [
 *   'uid'       => 'gallery-strip',
 *   'direction' => 'ltr',        // 'rtl' (default) or 'ltr'
 *   'speed'     => 60,           // px / second
 *   'items'     => [
 *     [ 'type' => 'image', 'src' => '…/photo.jpg', 'label' => 'Project A' ],
 *   ],
 * ] );
 *
 * ITEM FIELDS: type (image|video|gif), src (required), poster, label, desc
 * OPTIONS: uid, direction, speed, pause_on_hover, card_width, card_height, card_gap
 */

defined( 'ABSPATH' ) || exit;

$uid         = esc_attr( $args['uid'] ?? 'pt-sc-' . wp_rand( 100, 999 ) );
$direction   = ( ( $args['direction'] ?? '' ) === 'ltr' ) ? 'ltr' : 'rtl';
$speed       = isset( $args['speed'] )       ? max( 5, (int) $args['speed'] ) : 60;
$card_width  = isset( $args['card_width'] )  ? (int) $args['card_width']  : 280;
$card_height = isset( $args['card_height'] ) ? (int) $args['card_height'] : 200;
$card_gap    = isset( $args['card_gap'] )    ? (int) $args['card_gap']    : 24;
$pause_hover = isset( $args['pause_on_hover'] ) ? (bool) $args['pause_on_hover'] : true;
$items       = is_array( $args['items'] ?? null ) ? $args['items'] : [];

$items = array_values( array_filter( $items, static function ( $it ) {
	return ! empty( $it['src'] );
} ) );

if ( empty( $items ) ) return;

$count      = count( $items );
$one_set_px = $count * ( $card_width + $card_gap );
$duration_s = max( 1, round( $one_set_px / $speed, 2 ) );
$anim_name  = $uid . '-scroll';

ob_start();
foreach ( $items as $i => $item ) :
	$type   = $item['type']   ?? 'image';
	$src    = esc_url( $item['src']    ?? '' );
	$poster = esc_url( $item['poster'] ?? '' );
	$label  = $item['label'] ?? '';
	$desc   = $item['desc']  ?? '';
	$is_vid = ( 'video' === $type );
	?>
	<div class="pt-sc-card">
		<div class="pt-sc-media">
			<?php if ( $is_vid ) : ?>
				<video class="pt-sc-vid" src="<?php echo $src; ?>"
					<?php if ( $poster ) : ?>poster="<?php echo $poster; ?>"<?php endif; ?>
					autoplay muted loop playsinline preload="metadata"></video>
				<span class="pt-sc-badge">▶ Video</span>
			<?php else : ?>
				<img class="pt-sc-img" src="<?php echo $src; ?>"
				     alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
			<?php endif; ?>
		</div>
		<?php if ( $label || $desc ) : ?>
		<div class="pt-sc-caption">
			<?php if ( $label ) : ?><strong><?php echo esc_html( $label ); ?></strong><?php endif; ?>
			<?php if ( $desc )  : ?><span><?php echo esc_html( $desc ); ?></span><?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php
endforeach;
$cards_html = ob_get_clean();
?>

<div class="pt-sc" id="<?php echo $uid; ?>" data-dir="<?php echo esc_attr( $direction ); ?>">
	<div class="pt-sc-viewport">
		<div class="pt-sc-track" id="<?php echo $uid; ?>-track">
			<?php
			echo $cards_html;                                  // phpcs:ignore
			echo str_replace( 'class="pt-sc-card"', 'class="pt-sc-card" aria-hidden="true"', $cards_html ); // phpcs:ignore
			?>
		</div>
	</div>
</div>

<style>
#<?php echo $uid; ?> .pt-sc-viewport { width: 100%; overflow: hidden; }

#<?php echo $uid; ?> .pt-sc-track {
	display: flex;
	flex-wrap: nowrap;
	width: max-content;
	will-change: transform;
	animation: <?php echo $anim_name; ?> <?php echo $duration_s; ?>s linear infinite;
	animation-direction: <?php echo $direction === 'ltr' ? 'reverse' : 'normal'; ?>;
}
<?php if ( $pause_hover ) : ?>
#<?php echo $uid; ?>.pt-sc:hover .pt-sc-track { animation-play-state: paused; }
<?php endif; ?>

#<?php echo $uid; ?> .pt-sc-card {
	flex: 0 0 auto;
	width: <?php echo $card_width; ?>px;
	margin-right: <?php echo $card_gap; ?>px;
}
#<?php echo $uid; ?> .pt-sc-media {
	position: relative;
	width: <?php echo $card_width; ?>px;
	height: <?php echo $card_height; ?>px;
	overflow: hidden;
	border-radius: var(--pt-radius, 12px);
}
#<?php echo $uid; ?> .pt-sc-img,
#<?php echo $uid; ?> .pt-sc-vid {
	width: 100%; height: 100%;
	object-fit: cover; display: block;
}

@keyframes <?php echo $anim_name; ?> {
	from { transform: translateX(0); }
	to   { transform: translateX(-<?php echo (int) $one_set_px; ?>px); }
}

@media (prefers-reduced-motion: reduce) {
	#<?php echo $uid; ?> .pt-sc-track { animation: none; }
}
</style>
