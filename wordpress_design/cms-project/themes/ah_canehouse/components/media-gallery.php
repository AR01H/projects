<?php
/**
 * Media Gallery Component
 * Desktop: masonry bento grid (different sizes)
 * Mobile:  horizontal scroll-snap carousel
 * Supports images + videos (YouTube, Vimeo, mp4)
 *
 * Args:
 *  tag     (string) Eyebrow label
 *  title   (string) Heading HTML
 *  body    (string) Intro text
 *  items   (array)  [
 *    'type'      => 'image' | 'video'
 *    'src'       => image URL
 *    'thumbnail' => video poster (for video type)
 *    'video_url' => YouTube embed URL or mp4 URL
 *    'label'     => caption title
 *    'desc'      => caption description
 *  ]
 *  bg      (string) Section background CSS value
 *  id      (string) Unique ID (for multiple instances on page)
 */
defined( 'ABSPATH' ) || exit;

$tag   = $args['tag']   ?? 'Gallery';
$title = $args['title'] ?? '';
$body  = $args['body']  ?? '';
$items = $args['items'] ?? [];
$bg    = $args['bg']    ?? 'var(--ch-white)';
$uid   = esc_attr( $args['id'] ?? 'ch-mg-' . wp_rand( 100, 999 ) );

if ( empty( $items ) ) return;

$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];

// Bento size pattern — cycles every 6 items
$size_cycle = [ 'wide', 'normal', 'tall', 'normal', 'normal', 'wide' ];

$lightbox_id = $uid . '-lb';
?>

<section class="ch-mg-section" style="background:<?php echo esc_attr( $bg ); ?>;" id="<?php echo $uid; ?>-section">
<div class="container">

	<?php if ( $title ) : ?>
	<div class="ch-mg-header fade-up">
		<?php if ( $tag ) : ?><div class="section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
		<h2 class="section-title"><?php echo wp_kses( $title, $allowed ); ?></h2>
		<?php if ( $body ) : ?><p class="section-body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
	</div>
	<?php endif; ?>

	<!-- Desktop: bento grid  |  Mobile: scroll carousel (same markup, CSS drives both) -->
	<div class="ch-mg-grid" id="<?php echo $uid; ?>">
		<?php foreach ( $items as $i => $item ) :
			$type    = $item['type']      ?? 'image';
			$src     = $type === 'video'
			           ? esc_url( $item['thumbnail'] ?? '' )
			           : esc_url( $item['src']       ?? '' );
			$vid_url = esc_url( $item['video_url'] ?? '' );
			$label   = $item['label'] ?? '';
			$desc    = $item['desc']  ?? '';
			$size    = $size_cycle[ $i % 6 ];
			$is_vid  = ( 'video' === $type );
		?>
		<div class="ch-mg-item ch-mg-item--<?php echo $size; ?><?php echo $is_vid ? ' ch-mg-item--video' : ''; ?>"
			<?php if ( $is_vid && $vid_url ) : ?>
				data-video="<?php echo $vid_url; ?>" data-lb="<?php echo esc_attr( $lightbox_id ); ?>"
				role="button" tabindex="0" aria-label="Play: <?php echo esc_attr( $label ); ?>"
			<?php elseif ( $src ) : ?>
				data-src="<?php echo $src; ?>" data-lb="<?php echo esc_attr( $lightbox_id ); ?>"
				data-index="<?php echo (int) $i; ?>"
				role="button" tabindex="0" aria-label="View: <?php echo esc_attr( $label ); ?>"
			<?php endif; ?>>

			<?php if ( $src ) : ?>
			<img src="<?php echo $src; ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy" class="ch-mg-img">
			<?php endif; ?>

			<?php if ( $is_vid ) : ?>
			<div class="ch-mg-play-btn" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
			</div>
			<?php endif; ?>

			<?php if ( $label ) : ?>
			<div class="ch-mg-caption">
				<strong><?php echo esc_html( $label ); ?></strong>
				<?php if ( $desc ) : ?><span><?php echo esc_html( $desc ); ?></span><?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>

</div>
</section>

<!-- Lightbox dialog -->
<dialog class="ch-mg-lb" id="<?php echo esc_attr( $lightbox_id ); ?>" aria-label="Media viewer">
	<button class="ch-mg-lb-close" id="<?php echo $uid; ?>-lb-close" aria-label="Close">&times;</button>
	<div class="ch-mg-lb-inner">
		<button class="ch-mg-lb-arrow ch-mg-lb-prev" id="<?php echo $uid; ?>-lb-prev" aria-label="Previous">&#8592;</button>
		<div class="ch-mg-lb-media" id="<?php echo $uid; ?>-lb-media"></div>
		<button class="ch-mg-lb-arrow ch-mg-lb-next" id="<?php echo $uid; ?>-lb-next" aria-label="Next">&#8594;</button>
	</div>
	<p class="ch-mg-lb-caption" id="<?php echo $uid; ?>-lb-cap"></p>
</dialog>

<script>
(function () {
    var section  = document.getElementById('<?php echo $uid; ?>-section');
    var lb       = document.getElementById('<?php echo esc_js( $lightbox_id ); ?>');
    var lbMedia  = document.getElementById('<?php echo $uid; ?>-lb-media');
    var lbCap    = document.getElementById('<?php echo $uid; ?>-lb-cap');
    var lbClose  = document.getElementById('<?php echo $uid; ?>-lb-close');
    var lbPrev   = document.getElementById('<?php echo $uid; ?>-lb-prev');
    var lbNext   = document.getElementById('<?php echo $uid; ?>-lb-next');
    if (!section || !lb) return;

    // Collect image items for prev/next navigation
    var imgItems = Array.prototype.slice.call(section.querySelectorAll('.ch-mg-item[data-src]'));
    var current  = 0;

    function clearMedia() {
        lbMedia.innerHTML = '';
    }

    function showImage(index) {
        current = (index + imgItems.length) % imgItems.length;
        var el  = imgItems[current];
        clearMedia();
        var img = document.createElement('img');
        img.src = el.dataset.src;
        img.alt = el.querySelector('img') ? el.querySelector('img').alt : '';
        lbMedia.appendChild(img);
        lbCap.textContent = el.querySelector('strong') ? el.querySelector('strong').textContent : '';
        lbPrev.style.display = lbNext.style.display = imgItems.length > 1 ? '' : 'none';
    }

    function showVideo(url, label) {
        clearMedia();
        var isMp4 = /\.mp4(\?|$)/i.test(url);
        if (isMp4) {
            var vid = document.createElement('video');
            vid.src = url; vid.controls = true; vid.autoplay = true;
            lbMedia.appendChild(vid);
        } else {
            // YouTube / Vimeo embed
            var ifr = document.createElement('iframe');
            ifr.src = url + (url.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1';
            ifr.allow = 'autoplay; fullscreen'; ifr.allowFullscreen = true;
            lbMedia.appendChild(ifr);
        }
        lbCap.textContent = label || '';
        lbPrev.style.display = lbNext.style.display = 'none';
    }

    function openLb() { lb.showModal(); document.body.style.overflow = 'hidden'; }
    function closeLb() { lb.close(); clearMedia(); document.body.style.overflow = ''; }

    // Item click
    section.querySelectorAll('.ch-mg-item').forEach(function (el) {
        function activate() {
            if (el.dataset.video) {
                showVideo(el.dataset.video, el.querySelector('strong') ? el.querySelector('strong').textContent : '');
                openLb();
            } else if (el.dataset.src) {
                showImage(parseInt(el.dataset.index || 0, 10));
                openLb();
            }
        }
        el.addEventListener('click', activate);
        el.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(); } });
    });

    // Nav
    if (lbClose) lbClose.addEventListener('click', closeLb);
    if (lbPrev)  lbPrev.addEventListener('click',  function () { showImage(current - 1); });
    if (lbNext)  lbNext.addEventListener('click',  function () { showImage(current + 1); });

    // Close on backdrop click / Escape
    lb.addEventListener('click', function (e) { if (e.target === lb) closeLb(); });
    lb.addEventListener('cancel', function (e) { e.preventDefault(); closeLb(); });

    // Keyboard arrow navigation
    document.addEventListener('keydown', function (e) {
        if (!lb.open) return;
        if (e.key === 'ArrowLeft')  { if (lbPrev.style.display !== 'none') showImage(current - 1); }
        if (e.key === 'ArrowRight') { if (lbNext.style.display !== 'none') showImage(current + 1); }
    });

    // Touch swipe in lightbox
    var touchStartX = 0;
    lb.addEventListener('touchstart', function (e) { touchStartX = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) < 40) return;
        if (dx < 0 && lbNext.style.display !== 'none') showImage(current + 1);
        if (dx > 0 && lbPrev.style.display !== 'none') showImage(current - 1);
    }, { passive: true });
})();
</script>
