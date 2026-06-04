<?php
/**
 * Showcase Carousel - infinite auto-scroll, fixed-size cards, single row.
 *
 * Args:
 *  tag      (string)  Eyebrow tag.             Default: 'Showcase'
 *  title    (string)  Heading HTML.            Default: ''
 *  body     (string)  Intro text.              Default: ''
 *  bg       (string)  Section background CSS.  Default: 'var(--client-color-11)'
 *  id       (string)  Unique ID for JS hooks.  Default: auto
 *  autoplay (int)     Auto-advance ms.         Default: 2500
 *  items    (array)   [
 *    'type'   => 'image' | 'gif' | 'video'
 *    'src'    => media URL
 *    'poster' => poster image for video (optional)
 *    'label'  => caption title
 *    'desc'   => caption description
 *  ]
 */
defined( 'ABSPATH' ) || exit;

$tag      = $args['tag']      ?? 'Showcase';
$title    = $args['title']    ?? '';
$body     = $args['body']     ?? '';
$bg       = $args['bg']       ?? 'var(--client-color-11)';
$autoplay = isset( $args['autoplay'] ) ? (int) $args['autoplay'] : 2500;
$items    = $args['items']    ?? [];
$uid      = esc_attr( $args['id'] ?? 'ch-sc-' . wp_rand( 100, 999 ) );

if ( empty( $items ) ) return;
?>

<section class="ch-sc-section" style="background:<?php echo esc_attr( $bg ); ?>;" id="<?php echo $uid; ?>-section">
	<div class="container">

		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $body,
		] ); ?>

		<div class="ch-sc" id="<?php echo $uid; ?>" data-autoplay="<?php echo esc_attr( $autoplay ); ?>">
			<div class="ch-sc-viewport" id="<?php echo $uid; ?>-vp">
				<div class="ch-sc-track" id="<?php echo $uid; ?>-track">
					<?php foreach ( $items as $i => $item ) :
						$type   = $item['type']   ?? 'image';
						$src    = esc_url( $item['src']    ?? '' );
						$poster = esc_url( $item['poster'] ?? '' );
						$label  = $item['label'] ?? '';
						$desc   = $item['desc']  ?? '';
						$is_vid = ( 'video' === $type );
						if ( ! $src ) continue;
					?>
					<div class="ch-sc-card" data-index="<?php echo (int) $i; ?>">
						<div class="ch-sc-media">
							<?php if ( $is_vid ) : ?>
								<video class="ch-sc-vid" src="<?php echo $src; ?>"
									<?php if ( $poster ) : ?>poster="<?php echo $poster; ?>"<?php endif; ?>
									autoplay muted loop playsinline preload="metadata"></video>
							<?php else : ?>
								<img class="ch-sc-img" src="<?php echo $src; ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
							<?php endif; ?>
							<?php if ( $is_vid ) : ?><span class="ch-sc-badge">▶ Video</span><?php endif; ?>
						</div>
						<?php if ( $label || $desc ) : ?>
						<div class="ch-sc-caption">
							<?php if ( $label ) : ?><strong><?php echo esc_html( $label ); ?></strong><?php endif; ?>
							<?php if ( $desc )  : ?><span><?php echo esc_html( $desc ); ?></span><?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	</div>
</section>

<style>
#<?php echo $uid; ?> .ch-sc-viewport {
	width: 100%;
}
#<?php echo $uid; ?> .ch-sc-track {
	display: grid;
	grid-auto-flow: column;
	grid-auto-columns: 280px;
	gap: 24px;
	will-change: transform;
}
#<?php echo $uid; ?> .ch-sc-card {
	width: 280px;
}
#<?php echo $uid; ?> .ch-sc-media {
	width: 280px;
	height: 200px;
	overflow: hidden;
	border-radius: 12px;
}
#<?php echo $uid; ?> .ch-sc-img,
#<?php echo $uid; ?> .ch-sc-vid {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}
</style>

<script>
(function () {
	var root  = document.getElementById('<?php echo $uid; ?>');
	if (!root) return;
	var track = document.getElementById('<?php echo $uid; ?>-track');
	var vp    = document.getElementById('<?php echo $uid; ?>-vp');
	var cards = Array.prototype.slice.call(track.querySelectorAll('.ch-sc-card'));
	if (!cards.length) return;

	var autoplay = parseInt(root.dataset.autoplay || '2500', 10);
	var total    = cards.length;
	var index    = 0;
	var timer    = null;

	/* Clone all cards for seamless infinite loop */
	cards.forEach(function(c){ track.appendChild(c.cloneNode(true)); });
	track.style.transition = 'none';

	function cardStep() {
		/* card width (280) + gap (24) */
		return 280 + 24;
	}

	function go(i) {
		index = i;
		track.style.transition = 'transform 0.8s ease';
		track.style.transform  = 'translateX(' + (-index * cardStep()) + 'px)';
		if (index >= total) {
			setTimeout(function () {
				track.style.transition = 'none';
				index = index - total;
				track.style.transform  = 'translateX(' + (-index * cardStep()) + 'px)';
			}, 820);
		}
	}

	function nextSlide() { go(index + 1); }

	function startAuto() {
		if (autoplay > 0 && total > 1) timer = setInterval(nextSlide, autoplay);
	}
	function stopAuto() { if (timer) { clearInterval(timer); timer = null; } }

	root.addEventListener('mouseenter', stopAuto);
	root.addEventListener('mouseleave', startAuto);

	/* Touch swipe */
	var startX = 0, dragging = false;
	vp.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; dragging = true; stopAuto(); }, { passive: true });
	vp.addEventListener('touchend', function (e) {
		if (!dragging) return;
		dragging = false;
		var dx = e.changedTouches[0].clientX - startX;
		if (Math.abs(dx) > 40) { dx < 0 ? go(index + 1) : go(index <= 0 ? total - 1 : index - 1); }
		startAuto();
	}, { passive: true });

	go(0);
	startAuto();
})();
</script>