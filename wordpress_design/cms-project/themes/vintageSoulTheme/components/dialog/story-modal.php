<?php
/**
 * VintageSoulTheme - Clean Luxury Image & Media Lightbox Modal
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="dialog dialog--vintage-story" id="vintage-story-modal" data-vs-dialog hidden role="dialog" aria-modal="true" aria-labelledby="vsm-title">
	<div class="dialog__backdrop" data-vs-dialog-close></div>
	
	<div class="dialog__panel vintage-story-panel">
		
		<!-- Decorative Close Button -->
		<button type="button" class="vintage-story-panel__close" data-vs-dialog-close aria-label="<?php esc_attr_e( 'Close', 'vintagesoul' ); ?>">
			<svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<line x1="1" y1="1" x2="13" y2="13"></line>
				<line x1="13" y1="1" x2="1" y2="13"></line>
			</svg>
		</button>

		<!-- 1. Embedded Video Player (YouTube / MP4) -->
		<div class="vintage-story-panel__video-wrap" id="vsm-video-container" style="display: none;">
			<iframe id="vsm-iframe" src="" title="Video Player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="display: none;"></iframe>
			<video id="vsm-video" src="" controls playsinline preload="metadata" style="display: none;"></video>
		</div>

		<!-- 2. Full Image Display -->
		<div class="vintage-story-panel__media" id="vsm-media-container" style="display: none;">
			<img id="vsm-img" src="" alt="" loading="lazy" onerror="this.style.display='none';if(this.parentElement)this.parentElement.style.display='none';">
		</div>

		<!-- 3. Minimal Clean Title & Text Caption -->
		<div class="vintage-story-panel__body" id="vsm-body-container">
			<h3 class="vintage-story-panel__title" id="vsm-title" style="display: none;"></h3>
			<div class="vintage-story-panel__quote" id="vsm-quote" style="display: none;"></div>
		</div>

	</div>
</div>
