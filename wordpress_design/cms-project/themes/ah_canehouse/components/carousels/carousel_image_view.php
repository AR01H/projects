<?php
/**
 * carousel_image_view — standalone, continuous auto-scroll image/video strip (marquee style).
 *
 * Pure CSS infinite scroll: the item set is rendered twice and the track is
 * animated by exactly one set-width, so the loop is perfectly seamless — no
 * gaps, no snap-back. Works in both directions independently.
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  QUICK EXAMPLE (copy-paste ready)                               │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  get_template_part( 'components/carousels/carousel_image_view', null, [
 *    'uid'       => 'gallery-strip',   // unique — never reuse on the same page
 *    'direction' => 'ltr',             // 'rtl' (default) or 'ltr'
 *    'speed'     => 60,                // scroll speed in pixels / second
 *    'items'     => [
 *      [ 'type' => 'image', 'src' => '…/photo.jpg',  'label' => 'Machine A' ],
 *      [ 'type' => 'video', 'src' => '…/clip.mp4', 'poster' => '…/thumb.jpg', 'desc' => 'Live press' ],
 *    ],
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  ALL AVAILABLE OPTIONS                                          │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  'uid'            string  Unique id prefix for hooks/animation. Default: auto-generated
 *  'direction'      string  Scroll direction.                     Default: 'rtl'
 *                           'rtl' → track moves LEFT  (cards drift right→left)
 *                           'ltr' → track moves RIGHT (cards drift left→right)
 *  'speed'          int     Scroll speed in pixels per second.    Default: 60
 *                           Higher = faster. (Replaces the old per-step autoplay.)
 *  'pause_on_hover' bool    Pause the scroll while hovered.       Default: true
 *  'card_width'     int     Card width in px.                     Default: 280
 *  'card_height'    int     Card / media height in px.            Default: 200
 *  'card_gap'       int     Gap between cards in px.              Default: 24
 *
 *  ITEM FIELDS  (one array entry per card)
 *  'type'    string  'image' | 'gif' | 'video'                   Default: 'image'
 *  'src'     string  Media URL                                   (required — card skipped if empty)
 *  'poster'  string  Poster image URL for video                  (optional)
 *  'label'   string  Caption heading
 *  'desc'    string  Caption sub-text
 */

defined( 'ABSPATH' ) || exit;

/* ── Args ──────────────────────────────────────────────────────────────────── */

$uid         = esc_attr( $args['uid'] ?? 'ch-sc-' . wp_rand( 100, 999 ) );
$direction   = ( ( $args['direction'] ?? '' ) === 'ltr' ) ? 'ltr' : 'rtl';
$speed       = isset( $args['speed'] )       ? max( 5, (int) $args['speed'] ) : 60;
$card_width  = isset( $args['card_width'] )  ? (int) $args['card_width']  : 280;
$card_height = isset( $args['card_height'] ) ? (int) $args['card_height'] : 200;
$card_gap    = isset( $args['card_gap'] )    ? (int) $args['card_gap']    : 24;
$pause_hover = isset( $args['pause_on_hover'] ) ? (bool) $args['pause_on_hover'] : true;
$items       = is_array( $args['items'] ?? null ) ? $args['items'] : [];

/* Keep only items that have a usable src */
$items = array_values( array_filter( $items, static function ( $it ) {
	return ! empty( $it['src'] );
} ) );

if ( empty( $items ) ) return;

/* One full set width (each card carries its gap as margin-right, so the maths is exact) */
$count        = count( $items );
$one_set_px   = $count * ( $card_width + $card_gap );
$duration_s   = max( 1, round( $one_set_px / $speed, 2 ) );
$anim_name    = $uid . '-scroll';

/* ── Render the card markup once, reuse it for both loop passes ─────────────── */
ob_start();
foreach ( $items as $i => $item ) :
	$type   = $item['type']   ?? 'image';
	$src    = esc_url( $item['src']    ?? '' );
	$poster = esc_url( $item['poster'] ?? '' );
	$label  = $item['label'] ?? '';
	$desc   = $item['desc']  ?? '';
	$is_vid = ( 'video' === $type );
	?>
	<div class="ch-sc-card">
		<div class="ch-sc-media">
			<?php if ( $is_vid ) : ?>
				<video class="ch-sc-vid" src="<?php echo $src; ?>"
					<?php if ( $poster ) : ?>poster="<?php echo $poster; ?>"<?php endif; ?>
					autoplay muted loop playsinline preload="metadata"></video>
				<span class="ch-sc-badge">▶ Video</span>
			<?php else : ?>
				<img class="ch-sc-img" src="<?php echo $src; ?>"
				     alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
			<?php endif; ?>
		</div>
		<?php if ( $label || $desc ) : ?>
		<div class="ch-sc-caption">
			<?php if ( $label ) : ?><strong><?php echo esc_html( $label ); ?></strong><?php endif; ?>
			<?php if ( $desc )  : ?><span><?php echo esc_html( $desc ); ?></span><?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php
endforeach;
$cards_html = ob_get_clean();
?>

<div class="ch-sc" id="<?php echo $uid; ?>" data-dir="<?php echo esc_attr( $direction ); ?>">
	<div class="ch-sc-viewport">
		<div class="ch-sc-track" id="<?php echo $uid; ?>-track">
			<?php
			/* Two identical passes → seamless infinite loop. aria-hidden on the clone. */
			echo $cards_html;                                  // phpcs:ignore -- pre-escaped above
			echo str_replace( 'class="ch-sc-card"', 'class="ch-sc-card" aria-hidden="true"', $cards_html ); // phpcs:ignore
			?>
		</div>
	</div>
</div><!-- .ch-sc -->

<style>
/* Instance-specific: dimensions and animation only.
   Base layout/cosmetic rules live in carousel-image-scroll.css. */

/* Card dimensions — each card carries its trailing gap so the 2-set loop is pixel-perfect */
#<?php echo $uid; ?> .ch-sc-card  { width: <?php echo $card_width; ?>px; margin-right: <?php echo $card_gap; ?>px; }
#<?php echo $uid; ?> .ch-sc-media { width: <?php echo $card_width; ?>px; height: <?php echo $card_height; ?>px; }

/* Animation — name and duration are per-instance */
#<?php echo $uid; ?> .ch-sc-track {
	animation: <?php echo $anim_name; ?> <?php echo $duration_s; ?>s linear infinite;
	animation-direction: <?php echo $direction === 'ltr' ? 'reverse' : 'normal'; ?>;
<?php if ( ! $pause_hover ) : ?>
	/* pause_on_hover disabled for this instance — override the shared rule */
<?php endif; ?>
}
<?php if ( ! $pause_hover ) : ?>
#<?php echo $uid; ?>.ch-sc:hover .ch-sc-track { animation-play-state: running; }
<?php endif; ?>

@keyframes <?php echo $anim_name; ?> {
	from { transform: translateX(0); }
	to   { transform: translateX(-<?php echo (int) $one_set_px; ?>px); }
}
</style>
