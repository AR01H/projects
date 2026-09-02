<?php
/**
 * VintageSoulTheme - Interactive Media & Video Showcase Section
 * Supports HD Photography, YouTube Video Streaming, and Local Uploaded MP4 Videos.
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;

$showcase_data = (array) ( $showcase_data ?? array() );
$videos        = (array) ( $showcase_data['videos'] ?? array() );

if ( empty( $videos ) ) {
	return;
}

if ( ! function_exists( 'vintagesoul_extract_youtube_id' ) ) {
	function vintagesoul_extract_youtube_id( string $input ): string {
		$input = trim( $input );
		if ( preg_match( '/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $input, $matches ) ) {
			return $matches[1];
		}
		return 11 === strlen( $input ) ? $input : '';
	}
}

$first_vid     = (array) $videos[0];
$first_poster  = UrlHelper::resolve( (string) ( $first_vid['poster'] ?? 'assets/images/sugarcane/hero_juice.jpg' ) );
$first_src     = (string) ( $first_vid['src'] ?? '' );
$first_src_url = '' !== $first_src ? UrlHelper::resolve( $first_src ) : '';
$first_youtube = vintagesoul_extract_youtube_id( (string) ( $first_vid['youtube_id'] ?? '' ) );
$first_type    = (string) ( $first_vid['type'] ?? ( '' !== $first_youtube ? 'youtube' : ( '' !== $first_src ? 'video' : 'photo' ) ) );
?>
<section class="section section--video-showcase video-showcase-vintage torn-dark-block grain-dark" id="video-showcase">
	<div class="container video-showcase__container">
		
		<!-- Main 2-Column Balanced Showcase -->
		<div class="video-showcase__top-grid">
			
			<!-- Left: Video & Media Player Stage -->
			<div class="video-showcase__player-wrap">
				<div class="video-showcase__player-card frame--rough-cut frame--ornate" id="video-showcase-player-box">
					
					<!-- 1. Direct HTML5 Video Player -->
					<video id="video-showcase-video" 
						class="video-showcase__video<?php echo 'video' !== $first_type ? ' is-hidden' : ''; ?>" 
						controls 
						playsinline 
						poster="<?php echo esc_url( $first_poster ); ?>" 
						style="<?php echo 'video' === $first_type ? 'display:block;' : 'display:none;'; ?>">
						<?php if ( '' !== $first_src_url ) : ?>
							<source src="<?php echo esc_url( $first_src_url ); ?>" type="video/mp4">
						<?php endif; ?>
					</video>

					<!-- 2. YouTube Iframe Container -->
					<div id="video-showcase-youtube-wrap" 
						class="video-showcase__youtube-wrap<?php echo 'youtube' !== $first_type ? ' is-hidden' : ''; ?>" 
						style="<?php echo 'youtube' === $first_type ? 'display:block;' : 'display:none;'; ?>">
						<iframe 
							id="video-showcase-iframe" 
							class="video-showcase__iframe" 
							src="<?php echo 'youtube' === $first_type && '' !== $first_youtube ? esc_url( 'https://www.youtube.com/embed/' . rawurlencode( $first_youtube ) . '?rel=0&enablejsapi=1' ) : ''; ?>" 
							frameborder="0" 
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
							allowfullscreen
						></iframe>
					</div>

					<!-- 3. High-Definition Photo Showcase Container -->
					<div id="video-showcase-photo-wrap" 
						class="video-showcase__photo-wrap<?php echo 'photo' !== $first_type ? ' is-hidden' : ''; ?>" 
						style="<?php echo 'photo' === $first_type ? 'display:block;' : 'display:none;'; ?>">
						<img id="video-showcase-photo" 
							class="video-showcase__photo" 
							src="<?php echo esc_url( $first_poster ); ?>" 
							alt="<?php echo esc_attr( (string) ( $first_vid['title'] ?? 'Showcase Photo' ) ); ?>" 
							loading="lazy">
					</div>

				</div>
			</div>

			<!-- Right: Editorial Story Info -->
			<div class="video-showcase__story-wrap">
				<span class="vintage-ribbon-tag vintage-ribbon-tag--gold" id="video-showcase-tag">
					<span><?php echo esc_html( (string) ( $first_vid['tag'] ?? 'Featured Showcase' ) ); ?></span>
				</span>

				<h2 class="video-showcase__title" id="video-showcase-title"><?php echo esc_html( (string) ( $first_vid['title'] ?? '' ) ); ?></h2>
				<p class="section-eyebrow" id="video-showcase-eyebrow"><?php echo esc_html( (string) ( $first_vid['eyebrow'] ?? '' ) ); ?></p>
				
				<p class="video-showcase__desc" id="video-showcase-desc"><?php echo esc_html( (string) ( $first_vid['description'] ?? '' ) ); ?></p>

				<ul class="video-showcase__highlights" id="video-showcase-highlights">
					<?php foreach ( (array) ( $first_vid['highlights'] ?? array() ) as $hl ) : ?>
						<li>
							<span class="video-showcase__check">✓</span>
							<span class="video-showcase__highlight-text"><?php echo esc_html( (string) $hl ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="video-showcase__cta-wrap">
					<a class="btn btn--primary-vintage btn--order-now" id="video-showcase-btn" href="<?php echo esc_url( RouteService::url( (string) ( $first_vid['cta']['route'] ?? 'contact' ) ) ); ?>">
						<span class="btn__icon" id="video-showcase-btn-icon">✦</span>
						<span id="video-showcase-btn-label"><?php echo esc_html( (string) ( $first_vid['cta']['label'] ?? 'VISIT OUR STALL' ) ); ?></span>
					</a>
				</div>
			</div>

		</div>

		<!-- Bottom 100% Full-Width Horizontal Scrollable Playlist Bar (15 Items) -->
		<div class="video-showcase__playlist-bar card--rough-cut-dark">
			<div class="video-showcase__playlist-container">
				<button type="button" class="playlist-nav-btn playlist-nav-btn--prev" id="video-scroll-prev" aria-label="<?php esc_attr_e( 'Scroll Left', 'vintagesoul' ); ?>">‹</button>
				<div class="video-showcase__playlist-track-wrap">
					<div class="video-showcase__playlist-track" id="video-playlist-track">
						<?php foreach ( $videos as $v_idx => $v_item ) :
							$v_poster   = UrlHelper::resolve( (string) ( $v_item['poster'] ?? 'assets/images/sugarcane/hero_juice.jpg' ) );
							$v_src      = (string) ( $v_item['src'] ?? '' );
							$v_src_url  = '' !== $v_src ? UrlHelper::resolve( $v_src ) : '';
							$v_yt       = vintagesoul_extract_youtube_id( (string) ( $v_item['youtube_id'] ?? '' ) );
							$v_type     = (string) ( $v_item['type'] ?? ( '' !== $v_yt ? 'youtube' : ( '' !== $v_src ? 'video' : 'photo' ) ) );
							$c_route    = (string) ( $v_item['cta']['route'] ?? 'contact' );
							$c_url      = RouteService::url( $c_route );
						?>
							<button type="button" 
									class="video-playlist-item card--rough-cut-dark<?php echo 0 === $v_idx ? ' is-active' : ''; ?>"
									onclick="window.switchShowcaseMedia && window.switchShowcaseMedia(this)"
									data-video-idx="<?php echo esc_attr( (string) $v_idx ); ?>"
									data-video-type="<?php echo esc_attr( $v_type ); ?>"
									data-video-src="<?php echo esc_attr( $v_src_url ); ?>"
									data-video-poster="<?php echo esc_attr( $v_poster ); ?>"
									data-video-youtube="<?php echo esc_attr( $v_yt ); ?>"
									data-video-title="<?php echo esc_attr( (string) ( $v_item['title'] ?? '' ) ); ?>"
									data-video-eyebrow="<?php echo esc_attr( (string) ( $v_item['eyebrow'] ?? '' ) ); ?>"
									data-video-tag="<?php echo esc_attr( (string) ( $v_item['tag'] ?? '' ) ); ?>"
									data-video-desc="<?php echo esc_attr( (string) ( $v_item['description'] ?? '' ) ); ?>"
									data-video-highlights='<?php echo esc_attr( (string) wp_json_encode( (array) ( $v_item['highlights'] ?? array() ) ) ); ?>'
									data-video-cta-label="<?php echo esc_attr( (string) ( $v_item['cta']['label'] ?? 'LEARN MORE' ) ); ?>"
									data-video-cta-url="<?php echo esc_attr( $c_url ); ?>">
								<div class="video-playlist-item__thumb frame--rough-cut">
									<img src="<?php echo esc_url( $v_poster ); ?>" alt="<?php echo esc_attr( (string) ( $v_item['title'] ?? '' ) ); ?>" loading="lazy">
									<span class="video-playlist-item__play-icon">
										<?php if ( 'photo' === $v_type ) : ?>
											<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
										<?php else : ?>
											<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
										<?php endif; ?>
									</span>
									<span class="video-playlist-item__duration"><?php echo esc_html( (string) ( $v_item['duration'] ?? '1:00' ) ); ?></span>
								</div>
								<div class="video-playlist-item__info">
									<strong class="video-playlist-item__title"><?php echo esc_html( (string) ( $v_item['title'] ?? '' ) ); ?></strong>
									<span class="video-playlist-item__tag"><?php echo esc_html( (string) ( $v_item['tag'] ?? 'Showcase' ) ); ?></span>
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

<!-- Interactive Showcase Media Switching Script -->
<script>
(function() {
	function parseYouTubeId(input) {
		if (!input) return '';
		input = String(input).trim();
		var match = input.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
		return match ? match[1] : (input.length === 11 ? input : '');
	}

	function hideMedia(el) {
		if (!el) return;
		el.classList.add('is-hidden');
		el.style.setProperty('display', 'none', 'important');
	}

	function showMedia(el) {
		if (!el) return;
		el.classList.remove('is-hidden');
		el.style.setProperty('display', 'block', 'important');
	}

	window.switchShowcaseMedia = function(item) {
		if (!item) return;

		var section = document.getElementById('video-showcase');
		if (!section) return;

		var items = section.querySelectorAll('.video-playlist-item');
		items.forEach(function(it) { it.classList.remove('is-active'); });
		item.classList.add('is-active');

		var track = document.getElementById('video-playlist-track');
		if (track) {
			var itemOffset = item.offsetLeft - (track.clientWidth / 2) + (item.clientWidth / 2);
			track.scrollTo({ left: Math.max(0, itemOffset), behavior: 'smooth' });
		}

		var videoEl = document.getElementById('video-showcase-video');
		var ytWrap = document.getElementById('video-showcase-youtube-wrap');
		var ytIframe = document.getElementById('video-showcase-iframe');
		var photoWrap = document.getElementById('video-showcase-photo-wrap');
		var photoEl = document.getElementById('video-showcase-photo');

		var titleEl = document.getElementById('video-showcase-title');
		var eyebrowEl = document.getElementById('video-showcase-eyebrow');
		var tagEl = document.getElementById('video-showcase-tag');
		var descEl = document.getElementById('video-showcase-desc');
		var highlightsEl = document.getElementById('video-showcase-highlights');
		var btnEl = document.getElementById('video-showcase-btn');
		var btnLabelEl = document.getElementById('video-showcase-btn-label');

		var type = item.getAttribute('data-video-type') || 'photo';
		var src = item.getAttribute('data-video-src') || '';
		var poster = item.getAttribute('data-video-poster') || '';
		var ytRaw = item.getAttribute('data-video-youtube') || '';
		var ytId = parseYouTubeId(ytRaw);

		var title = item.getAttribute('data-video-title') || '';
		var eyebrow = item.getAttribute('data-video-eyebrow') || '';
		var tag = item.getAttribute('data-video-tag') || '';
		var desc = item.getAttribute('data-video-desc') || '';
		var highlightsRaw = item.getAttribute('data-video-highlights') || '';
		var ctaLabel = item.getAttribute('data-video-cta-label') || 'LEARN MORE';
		var ctaUrl = item.getAttribute('data-video-cta-url') || '#';

		// Visual feedback transition on big card
		var playerBox = document.getElementById('video-showcase-player-box');
		var storyWrap = section.querySelector('.video-showcase__story-wrap');
		if (playerBox) {
			playerBox.style.opacity = '0.3';
			playerBox.style.transform = 'scale(0.985)';
		}
		if (storyWrap) {
			storyWrap.style.opacity = '0.4';
		}

		setTimeout(function() {
			if (playerBox) {
				playerBox.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
				playerBox.style.opacity = '1';
				playerBox.style.transform = 'scale(1)';
			}
			if (storyWrap) {
				storyWrap.style.transition = 'opacity 0.35s ease';
				storyWrap.style.opacity = '1';
			}
		}, 60);

		// 1. Switch Media Stage (YouTube / MP4 Video / Photo)
		if (type === 'youtube' && ytId) {
			if (videoEl) {
				videoEl.pause();
				videoEl.currentTime = 0;
			}
			hideMedia(videoEl);
			hideMedia(photoWrap);
			showMedia(ytWrap);
			if (ytIframe) {
				ytIframe.src = 'https://www.youtube.com/embed/' + encodeURIComponent(ytId) + '?autoplay=1&rel=0&enablejsapi=1';
			}
		} else if (type === 'video' || (src && src.indexOf('.mp4') !== -1)) {
			if (ytIframe) ytIframe.src = 'about:blank';
			hideMedia(ytWrap);
			hideMedia(photoWrap);
			showMedia(videoEl);
			if (videoEl) {
				if (poster) videoEl.poster = poster;
				videoEl.innerHTML = '<source src="' + src + '" type="video/mp4">';
				videoEl.load();
				var p = videoEl.play();
				if (p && typeof p.catch === 'function') {
					p.catch(function() {});
				}
			}
		} else {
			// Photo / Image Mode
			if (ytIframe) ytIframe.src = 'about:blank';
			if (videoEl) {
				videoEl.pause();
				videoEl.currentTime = 0;
			}
			hideMedia(ytWrap);
			hideMedia(videoEl);
			showMedia(photoWrap);
			if (photoEl) {
				photoEl.src = poster || src;
				photoEl.alt = title;
			}
		}

		// 2. Update Story Info
		if (titleEl && title) titleEl.textContent = title;
		if (eyebrowEl && eyebrow) eyebrowEl.textContent = eyebrow;
		if (tagEl && tag) {
			var span = tagEl.querySelector('span');
			if (span) span.textContent = tag;
		}
		if (descEl && desc) descEl.textContent = desc;

		// 3. Update Highlights with Same-Line Checkmark Structure
		if (highlightsEl && highlightsRaw) {
			try {
				var parsedHl = JSON.parse(highlightsRaw);
				if (Array.isArray(parsedHl)) {
					highlightsEl.innerHTML = parsedHl.map(function(h) {
						return '<li><span class="video-showcase__check">✓</span> <span class="video-showcase__highlight-text">' + h + '</span></li>';
					}).join('');
				}
			} catch(e) {}
		}

		// 4. Update CTA Button
		if (btnLabelEl && ctaLabel) btnLabelEl.textContent = ctaLabel;
		if (btnEl && ctaUrl) btnEl.href = ctaUrl;
	};

	function initVideoShowcase() {
		var section = document.getElementById('video-showcase');
		if (!section) return;

		var track = document.getElementById('video-playlist-track');
		var prevBtn = document.getElementById('video-scroll-prev');
		var nextBtn = document.getElementById('video-scroll-next');

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

		// Event delegation on track for 100% reliable clicks
		if (track) {
			track.addEventListener('click', function(e) {
				var item = e.target.closest('.video-playlist-item');
				if (item) {
					e.preventDefault();
					window.switchShowcaseMedia(item);
				}
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initVideoShowcase);
	} else {
		initVideoShowcase();
	}
})();
</script>
