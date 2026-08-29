<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$showcase_data = (array) ( $showcase_data ?? array() );
$videos        = (array) ( $showcase_data['videos'] ?? array() );

if ( empty( $videos ) ) {
	return;
}

$first_vid = (array) $videos[0];
$first_poster = UrlHelper::resolve( (string) ( $first_vid['poster'] ?? 'assets/images/sugarcane/hero_juice.jpg' ) );
$first_src    = (string) ( $first_vid['src'] ?? '' );
$first_src_url = '' !== $first_src ? UrlHelper::resolve( $first_src ) : '';
$first_youtube = (string) ( $first_vid['youtube_id'] ?? '' );
?>
<section class="section section--video-showcase video-showcase-vintage torn-dark-block grain-dark" id="video-showcase">
	<div class="container video-showcase__container">
		
		<!-- Main 2-Column Balanced Showcase -->
		<div class="video-showcase__top-grid">
			
			<!-- Left: Video Player Area -->
			<div class="video-showcase__player-wrap">
				<div class="video-showcase__player-card frame--ornate" id="video-showcase-player-box">
					
					<!-- Direct Video Element -->
					<video id="video-showcase-video" class="video-showcase__video" controls playsinline poster="<?php echo esc_url( $first_poster ); ?>">
						<?php if ( '' !== $first_src_url ) : ?>
							<source src="<?php echo esc_url( $first_src_url ); ?>" type="video/mp4">
						<?php endif; ?>
					</video>

					<!-- YouTube Iframe Container -->
					<div id="video-showcase-youtube-wrap" class="video-showcase__youtube-wrap" style="display: none;">
						<iframe id="video-showcase-iframe" class="video-showcase__iframe" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>

				</div>
			</div>

			<!-- Right: Editorial Story Info -->
			<div class="video-showcase__story-wrap">
				<span class="vintage-ribbon-tag vintage-ribbon-tag--gold" id="video-showcase-tag">
					<span><?php echo esc_html( (string) ( $first_vid['tag'] ?? 'Featured Video' ) ); ?></span>
				</span>

				<h2 class="video-showcase__title" id="video-showcase-title"><?php echo esc_html( (string) ( $first_vid['title'] ?? '' ) ); ?></h2>
				<p class="section-eyebrow" id="video-showcase-eyebrow"><?php echo esc_html( (string) ( $first_vid['eyebrow'] ?? '' ) ); ?></p>
				
				<p class="video-showcase__desc" id="video-showcase-desc"><?php echo esc_html( (string) ( $first_vid['description'] ?? '' ) ); ?></p>

				<ul class="video-showcase__highlights" id="video-showcase-highlights">
					<?php foreach ( (array) ( $first_vid['highlights'] ?? array() ) as $hl ) : ?>
						<li><span class="video-showcase__check">✓</span> <?php echo esc_html( (string) $hl ); ?></li>
					<?php endforeach; ?>
				</ul>

				<div class="video-showcase__cta-wrap">
					<a class="btn btn--primary-vintage btn--order-now" id="video-showcase-btn" href="<?php echo esc_url( RouteService::url( (string) ( $first_vid['cta']['route'] ?? 'contact' ) ) ); ?>">
						<span class="btn__icon" id="video-showcase-btn-icon"><?php echo esc_html( (string) ( $first_vid['cta']['icon'] ?? '📍' ) ); ?></span>
						<span id="video-showcase-btn-label"><?php echo esc_html( (string) ( $first_vid['cta']['label'] ?? 'VISIT OUR STALL' ) ); ?></span>
					</a>
				</div>
			</div>

		</div>

		<!-- Bottom 100% Full-Width Horizontal Scrollable Playlist Bar (15 Videos) -->
		<div class="video-showcase__playlist-bar">
			<div class="video-showcase__playlist-container">
				<button type="button" class="playlist-nav-btn playlist-nav-btn--prev" id="video-scroll-prev" aria-label="<?php esc_attr_e( 'Scroll Left', 'vintagesoul' ); ?>">‹</button>
				<div class="video-showcase__playlist-track-wrap">
					<div class="video-showcase__playlist-track" id="video-playlist-track">
						<?php foreach ( $videos as $v_idx => $v_item ) :
							$v_poster   = UrlHelper::resolve( (string) ( $v_item['poster'] ?? 'assets/images/sugarcane/hero_juice.jpg' ) );
							$v_src      = (string) ( $v_item['src'] ?? '' );
							$v_src_url  = '' !== $v_src ? UrlHelper::resolve( $v_src ) : '';
							$v_yt       = (string) ( $v_item['youtube_id'] ?? '' );
						?>
							<button type="button" 
									class="video-playlist-item<?php echo 0 === $v_idx ? ' is-active' : ''; ?>"
									data-video-idx="<?php echo esc_attr( (string) $v_idx ); ?>"
									data-video-type="<?php echo esc_attr( '' !== $v_yt ? 'youtube' : 'video' ); ?>"
									data-video-src="<?php echo esc_attr( $v_src_url ); ?>"
									data-video-poster="<?php echo esc_attr( $v_poster ); ?>"
									data-video-youtube="<?php echo esc_attr( $v_yt ); ?>"
									data-video-title="<?php echo esc_attr( (string) ( $v_item['title'] ?? '' ) ); ?>"
									data-video-eyebrow="<?php echo esc_attr( (string) ( $v_item['eyebrow'] ?? '' ) ); ?>"
									data-video-tag="<?php echo esc_attr( (string) ( $v_item['tag'] ?? '' ) ); ?>"
									data-video-desc="<?php echo esc_attr( (string) ( $v_item['description'] ?? '' ) ); ?>"
									data-video-highlights='<?php echo esc_attr( (string) wp_json_encode( (array) ( $v_item['highlights'] ?? array() ) ) ); ?>'
									data-video-cta-label="<?php echo esc_attr( (string) ( $v_item['cta']['label'] ?? 'LEARN MORE' ) ); ?>"
									data-video-cta-route="<?php echo esc_attr( (string) ( $v_item['cta']['route'] ?? 'contact' ) ); ?>"
									data-video-cta-icon="<?php echo esc_attr( (string) ( $v_item['cta']['icon'] ?? '👉' ) ); ?>">
								<div class="video-playlist-item__thumb">
									<img src="<?php echo esc_url( $v_poster ); ?>" alt="<?php echo esc_attr( (string) ( $v_item['title'] ?? '' ) ); ?>" loading="lazy">
									<span class="video-playlist-item__play-icon">▶</span>
									<span class="video-playlist-item__duration"><?php echo esc_html( (string) ( $v_item['duration'] ?? '1:00' ) ); ?></span>
								</div>
								<div class="video-playlist-item__info">
									<strong class="video-playlist-item__title"><?php echo esc_html( (string) ( $v_item['title'] ?? '' ) ); ?></strong>
									<span class="video-playlist-item__tag"><?php echo esc_html( (string) ( $v_item['tag'] ?? 'Video' ) ); ?></span>
								</div>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
				<button type="button" class="playlist-nav-btn playlist-nav-btn--next" id="video-scroll-next" aria-label="<?php esc_attr_e( 'Scroll Right', 'vintagesoul' ); ?>">›</button>
			</div>
		</div>

	</div>
</section>

<!-- Interactive Video Switching & Horizontal Scroll Logic -->
<script>
(function() {
	function initVideoShowcase() {
		var section = document.getElementById('video-showcase');
		if (!section) return;

		var videoEl = document.getElementById('video-showcase-video');
		var ytWrap = document.getElementById('video-showcase-youtube-wrap');
		var ytIframe = document.getElementById('video-showcase-iframe');

		var titleEl = document.getElementById('video-showcase-title');
		var eyebrowEl = document.getElementById('video-showcase-eyebrow');
		var tagEl = document.getElementById('video-showcase-tag');
		var descEl = document.getElementById('video-showcase-desc');
		var highlightsEl = document.getElementById('video-showcase-highlights');
		var btnEl = document.getElementById('video-showcase-btn');
		var btnLabelEl = document.getElementById('video-showcase-btn-label');
		var btnIconEl = document.getElementById('video-showcase-btn-icon');

		var track = document.getElementById('video-playlist-track');
		var prevBtn = document.getElementById('video-scroll-prev');
		var nextBtn = document.getElementById('video-scroll-next');
		var items = section.querySelectorAll('.video-playlist-item');

		// Horizontal scroll buttons
		if (track && prevBtn && nextBtn) {
			prevBtn.addEventListener('click', function(e) {
				e.preventDefault();
				track.scrollBy({ left: -320, behavior: 'smooth' });
			});
			nextBtn.addEventListener('click', function(e) {
				e.preventDefault();
				track.scrollBy({ left: 320, behavior: 'smooth' });
			});
		}

		// Video item click handling
		items.forEach(function(item) {
			item.addEventListener('click', function(e) {
				e.preventDefault();

				items.forEach(function(it) { it.classList.remove('is-active'); });
				item.classList.add('is-active');

				// Scroll active item smoothly into center view
				if (track) {
					var itemOffset = item.offsetLeft - (track.clientWidth / 2) + (item.clientWidth / 2);
					track.scrollTo({ left: itemOffset, behavior: 'smooth' });
				}

				var type = item.getAttribute('data-video-type');
				var src = item.getAttribute('data-video-src');
				var poster = item.getAttribute('data-video-poster');
				var ytId = item.getAttribute('data-video-youtube');

				var title = item.getAttribute('data-video-title');
				var eyebrow = item.getAttribute('data-video-eyebrow');
				var tag = item.getAttribute('data-video-tag');
				var desc = item.getAttribute('data-video-desc');
				var highlightsRaw = item.getAttribute('data-video-highlights');
				var ctaLabel = item.getAttribute('data-video-cta-label');
				var ctaRoute = item.getAttribute('data-video-cta-route');
				var ctaIcon = item.getAttribute('data-video-cta-icon');

				// Update Video Player
				if (type === 'youtube' && ytId) {
					if (videoEl) {
						videoEl.pause();
						videoEl.style.display = 'none';
					}
					if (ytWrap && ytIframe) {
						ytWrap.style.display = 'block';
						ytIframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(ytId) + '?autoplay=1&rel=0';
					}
				} else {
					if (ytWrap && ytIframe) {
						ytIframe.src = '';
						ytWrap.style.display = 'none';
					}
					if (videoEl) {
						videoEl.style.display = 'block';
						if (poster) videoEl.poster = poster;
						if (src) {
							videoEl.src = src;
							videoEl.load();
							videoEl.play().catch(function() {});
						}
					}
				}

				// Update Story Info
				if (titleEl && title) titleEl.textContent = title;
				if (eyebrowEl && eyebrow) eyebrowEl.textContent = eyebrow;
				if (tagEl && tag) {
					var span = tagEl.querySelector('span');
					if (span) span.textContent = tag;
				}
				if (descEl && desc) descEl.textContent = desc;

				if (highlightsEl && highlightsRaw) {
					try {
						var parsedHl = JSON.parse(highlightsRaw);
						if (Array.isArray(parsedHl)) {
							highlightsEl.innerHTML = parsedHl.map(function(h) {
								return '<li><span class="video-showcase__check">✓</span> ' + h + '</li>';
							}).join('');
						}
					} catch(e) {}
				}

				if (btnLabelEl && ctaLabel) btnLabelEl.textContent = ctaLabel;
				if (btnIconEl && ctaIcon) btnIconEl.textContent = ctaIcon;
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initVideoShowcase);
	} else {
		initVideoShowcase();
	}
})();
</script>
