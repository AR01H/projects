<?php
/**
 * VintageSoulTheme - Reusable Luxury Vintage Story, Review & Social Media Video Lightbox Modal
 *
 * Supports high-definition photography, reviews, customer stories, and embedded
 * YouTube videos, Instagram Reels & TikTok video players with live stats.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="dialog dialog--vintage-story" id="vintage-story-modal" data-vs-dialog hidden role="dialog" aria-modal="true" aria-labelledby="vsm-author-name">
	<div class="dialog__backdrop" data-vs-dialog-close></div>
	
	<div class="dialog__panel frame--ornate vintage-story-panel">
		
		<!-- Decorative Close Button -->
		<button type="button" class="vintage-story-panel__close" data-vs-dialog-close aria-label="<?php esc_attr_e( 'Close Story', 'vintagesoul' ); ?>">
			&times;
		</button>

		<!-- 1. Embedded Video Player Header (YouTube iframe / HTML5 MP4) -->
		<div class="vintage-story-panel__video-wrap" id="vsm-video-container" style="display: none;">
			<iframe id="vsm-iframe" src="" title="Video Player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="display: none;"></iframe>
			<video id="vsm-video" src="" controls playsinline preload="metadata" style="display: none;"></video>
		</div>

		<!-- 2. Static Banner Photo Header (for photo memories / standard reviews) -->
		<div class="vintage-story-panel__media" id="vsm-media-container" style="display: none;">
			<img id="vsm-img" src="" alt="Story Image" loading="lazy">
			<div class="vintage-story-panel__media-overlay"></div>
		</div>

		<div class="vintage-story-panel__body">
			
			<!-- Top Meta: Rating & Platform Badge -->
			<div class="vintage-story-panel__top-row">
				<div class="vintage-story-panel__stars" id="vsm-stars">★★★★★</div>
				<span class="vintage-story-panel__badge" id="vsm-badge" style="display: none;"></span>
			</div>

			<!-- Optional Title -->
			<h3 class="vintage-story-panel__title" id="vsm-title" style="display: none;"></h3>

			<!-- Large Decorative Quotation Icon -->
			<div class="vintage-story-panel__quote-icon" aria-hidden="true">“</div>

			<!-- Full Quote / Caption / Story Description -->
			<div class="vintage-story-panel__quote" id="vsm-quote"></div>

			<!-- Social Engagement Realtime Stats & Action Bar -->
			<div class="vintage-story-panel__social-stats" id="vsm-social-stats" style="display: none;">
				<div class="vsm-stats-counts">
					<span class="vsm-stat" id="vsm-likes">❤️ <span></span></span>
					<span class="vsm-stat" id="vsm-comments">💬 <span></span></span>
				</div>
				<a href="#" target="_blank" rel="noopener" class="btn btn--order-now vsm-platform-btn" id="vsm-platform-link">
					<span id="vsm-platform-btn-text">WATCH ON SOCIAL</span>
				</a>
			</div>

			<!-- Author / Creator Footer -->
			<div class="vintage-story-panel__footer" id="vsm-footer">
				<div class="vintage-story-panel__author-info">
					<strong class="vintage-story-panel__author" id="vsm-author-name"></strong>
					<span class="vintage-story-panel__meta" id="vsm-meta"></span>
				</div>
			</div>

		</div>

	</div>
</div>
