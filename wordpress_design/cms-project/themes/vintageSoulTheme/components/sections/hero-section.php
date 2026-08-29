<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$hero     = (array) ( $hero ?? array() );
$slides   = (array) ( $hero['slides'] ?? array() );

if ( empty( $slides ) ) {
	$slides = array(
		array(
			'media'   => array( 'type' => 'video', 'src' => 'assets/videos/hero_bg.mp4', 'poster' => 'assets/images/sugarcane/hero_juice.jpg', 'alt' => 'Live fresh sugarcane extraction' ),
			'content' => array(
				'title'     => 'WATCH IT. TASTE IT. LOVE IT.',
				'eyebrow'   => 'A Taste of Tradition',
				'checklist' => array(
					'Freshly pressed right before your eyes.',
					'100% pure sugarcane — nothing added.',
					'A taste of tradition, crafted with love.',
				),
				'buttons'   => array(
					array( 'label' => 'Visit Us', 'icon' => '📍', 'route' => 'contact' ),
					array( 'label' => 'Book Us For Your Event', 'icon' => '📅', 'route' => 'contact', 'style' => 'ghost' ),
				),
			),
		),
		array(
			'media'   => array( 'type' => 'image', 'src' => 'assets/images/sugarcane/hero_juice.jpg', 'alt' => 'Fresh sugarcane juice' ),
			'content' => array(
				'title'     => 'WATCH IT. TASTE IT. LOVE IT.',
				'eyebrow'   => 'Pure Cold Pressed',
				'checklist' => array(
					'Freshly pressed right before your eyes.',
					'100% pure sugarcane — nothing added.',
					'A taste of tradition, crafted with love.',
				),
				'buttons'   => array(
					array( 'label' => 'Visit Us', 'icon' => '📍', 'route' => 'contact' ),
					array( 'label' => 'Book Us For Your Event', 'icon' => '📅', 'route' => 'contact', 'style' => 'ghost' ),
				),
			),
		),
		array(
			'media'   => array( 'type' => 'image', 'src' => 'assets/images/sugarcane/stacks.jpg', 'alt' => 'Harvested sugarcane stalks' ),
			'content' => array(
				'title'     => 'HARVESTED PURE. PRESSED COLD.',
				'eyebrow'   => 'From Farm To Stall',
				'checklist' => array(
					'Hand-picked premium sugarcane.',
					'Cold extraction for maximum nutrients.',
					'Rich in iron, magnesium & potassium.',
				),
				'buttons'   => array(
					array( 'label' => 'All About Cane', 'icon' => '🌾', 'route' => 'history' ),
					array( 'label' => 'Find Our Stall', 'icon' => '📍', 'route' => 'contact' ),
				),
			),
		),
		array(
			'media'   => array( 'type' => 'image', 'src' => 'assets/images/sugarcane/combo.jpg', 'alt' => 'Signature cane combos' ),
			'content' => array(
				'title'     => 'SIGNATURE VINTAGE COMBOS',
				'eyebrow'   => 'Handcrafted Flavours',
				'checklist' => array(
					'Fresh ginger, mint & lime infusions.',
					'No added sugar, ice dilution or preservatives.',
					'Crafted to order in seconds.',
				),
				'buttons'   => array(
					array( 'label' => 'Explore Drinks', 'icon' => '🥤', 'route' => 'history' ),
					array( 'label' => 'Book Live Bar', 'icon' => '🎪', 'route' => 'contact' ),
				),
			),
		),
		array(
			'media'   => array( 'type' => 'image', 'src' => 'assets/images/sugarcane/story_moments.jpg', 'alt' => 'Heritage community moments' ),
			'content' => array(
				'title'     => 'MORE THAN JUST A DRINK',
				'eyebrow'   => 'Heritage & Community',
				'checklist' => array(
					'Live pressing counter for weddings & private events.',
					'London Borough & weekend market stall favourite.',
					'Loved by thousands of happy customers.',
				),
				'buttons'   => array(
					array( 'label' => 'Our Heritage', 'icon' => '📖', 'route' => 'about' ),
					array( 'label' => 'Get In Touch', 'icon' => '✉️', 'route' => 'contact' ),
				),
			),
		),
	);
}

$first_slide = (array) $slides[0];
$first_content = (array) ( $first_slide['content'] ?? array() );

$video_url   = UrlHelper::resolve( 'assets/videos/hero_bg.mp4' );
$poster_url  = UrlHelper::resolve( 'assets/images/sugarcane/hero_juice.jpg' );
?>
<section class="section section--hero hero-sugarcane" id="home-hero">
	
	<!-- Ambient Background Video (Very Light Opacity Canvas Blend) -->
	<div class="hero-ambient-bg" aria-hidden="true">
		<video class="hero-ambient-video" autoplay muted loop playsinline poster="<?php echo esc_url( $poster_url ); ?>">
			<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
		</video>
		<div class="hero-ambient-overlay"></div>
	</div>

	<!-- Foreground 2-Column Hero Stage -->
	<div class="container hero-sugarcane__container">
		<div class="hero-sugarcane__header">
			
			<!-- Left Column: Editorial Story Information -->
			<div class="hero-sugarcane__content">
				<h1 class="hero-sugarcane__title" id="hero-title"><?php echo esc_html( (string) ( $first_content['title'] ?? 'WATCH IT. TASTE IT. LOVE IT.' ) ); ?></h1>
				<p class="hero-sugarcane__eyebrow" id="hero-eyebrow"><?php echo esc_html( (string) ( $first_content['eyebrow'] ?? 'A Taste of Tradition' ) ); ?></p>

				<ul class="hero-sugarcane__checklist" id="hero-checklist">
					<?php foreach ( (array) ( $first_content['checklist'] ?? array() ) as $item ) : ?>
						<li><span class="hero-sugarcane__check">✓</span> <?php echo esc_html( (string) $item ); ?></li>
					<?php endforeach; ?>
				</ul>

				<div class="hero-sugarcane__buttons" id="hero-buttons">
					<?php foreach ( (array) ( $first_content['buttons'] ?? array() ) as $idx => $btn ) :
						$btn       = (array) $btn;
						$btn_label = (string) ( $btn['label'] ?? '' );
						$btn_route = (string) ( $btn['route'] ?? 'contact' );
						$btn_icon  = (string) ( $btn['icon'] ?? '' );
						$btn_class = 0 === $idx ? 'btn--primary-vintage' : 'btn--secondary-vintage btn--outline-vintage';
					?>
						<a class="btn <?php echo esc_attr( $btn_class ); ?>" href="<?php echo esc_url( RouteService::url( $btn_route ) ); ?>">
							<?php if ( '' !== $btn_icon ) : ?>
								<span class="btn__icon"><?php echo esc_html( $btn_icon ); ?></span>
							<?php endif; ?>
							<span><?php echo esc_html( $btn_label ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Right Column: Framed Multi-Slide Media Stage -->
			<div class="hero-sugarcane__media-wrap">
				<div class="hero-carousel-container frame--ornate" id="hero-image-carousel" data-autoplay="5000">
					
					<!-- Slides Wrapper -->
					<div class="hero-carousel-slides">
						<?php foreach ( $slides as $idx => $slide ) :
							$slide_media     = (array) ( $slide['media'] ?? array() );
							$slide_type      = (string) ( $slide_media['type'] ?? 'image' );
							$slide_img       = UrlHelper::resolve( (string) ( $slide_media['src'] ?? $slide_media['poster'] ?? 'assets/images/sugarcane/hero_juice.jpg' ) );
							$slide_video     = (string) ( $slide_media['video'] ?? ( 'video' === $slide_type ? $slide_media['src'] : '' ) );
							$slide_video_url = '' !== $slide_video ? UrlHelper::resolve( $slide_video ) : '';
							$slide_alt       = (string) ( $slide_media['alt'] ?? 'Cane House Sugarcane' );
							$slide_content   = (array) ( $slide['content'] ?? array() );
							$is_video        = 'video' === $slide_type || '' !== $slide_video_url;
						?>
							<div class="hero-carousel-slide<?php echo 0 === $idx ? ' is-active' : ''; ?>" 
								 data-index="<?php echo esc_attr( (string) $idx ); ?>"
								 data-title="<?php echo esc_attr( (string) ( $slide_content['title'] ?? '' ) ); ?>"
								 data-eyebrow="<?php echo esc_attr( (string) ( $slide_content['eyebrow'] ?? '' ) ); ?>"
								 data-checklist='<?php echo esc_attr( (string) wp_json_encode( (array) ( $slide_content['checklist'] ?? array() ) ) ); ?>'>
								<?php if ( $is_video ) : ?>
									<video class="hero-carousel-video" autoplay muted loop playsinline poster="<?php echo esc_url( $slide_img ); ?>">
										<source src="<?php echo esc_url( $slide_video_url ); ?>" type="video/mp4">
									</video>
								<?php else : ?>
									<img class="hero-carousel-image" src="<?php echo esc_url( $slide_img ); ?>" alt="<?php echo esc_attr( $slide_alt ); ?>" loading="<?php echo 0 === $idx ? 'eager' : 'lazy'; ?>">
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- Navigation Arrows (< and >) -->
					<button type="button" class="hero-carousel-arrow hero-carousel-arrow--prev" id="hero-prev" aria-label="<?php esc_attr_e( 'Previous Slide', 'vintagesoul' ); ?>">‹</button>
					<button type="button" class="hero-carousel-arrow hero-carousel-arrow--next" id="hero-next" aria-label="<?php esc_attr_e( 'Next Slide', 'vintagesoul' ); ?>">›</button>

				</div>
			</div>

		</div>
	</div>

</section>

<!-- Inline script for smooth auto-rotating Hero Carousel -->
<script>
(function() {
	function initHeroCarousel() {
		var container = document.getElementById('hero-image-carousel');
		if (!container) return;

		var slides = container.querySelectorAll('.hero-carousel-slide');
		var prevBtn = document.getElementById('hero-prev');
		var nextBtn = document.getElementById('hero-next');
		var currentIndex = 0;
		var timer = null;
		var delay = parseInt(container.getAttribute('data-autoplay') || '5000', 10);

		if (slides.length <= 1) return;

		function goToSlide(index) {
			if (index < 0) index = slides.length - 1;
			if (index >= slides.length) index = 0;
			currentIndex = index;

			slides.forEach(function(s, idx) {
				var isActive = idx === currentIndex;
				s.classList.toggle('is-active', isActive);
				var vid = s.querySelector('video');
				if (vid) {
					if (isActive) {
						vid.play().catch(function() {});
					} else {
						vid.pause();
					}
				}
			});

			// Synchronize Left Story Headline, Eyebrow & Checklist
			var activeSlide = slides[currentIndex];
			if (activeSlide) {
				var titleEl = document.getElementById('hero-title');
				var eyebrowEl = document.getElementById('hero-eyebrow');
				var checklistEl = document.getElementById('hero-checklist');

				var titleText = activeSlide.getAttribute('data-title');
				var eyebrowText = activeSlide.getAttribute('data-eyebrow');
				var checklistRaw = activeSlide.getAttribute('data-checklist');

				if (titleEl && titleText) {
					titleEl.textContent = titleText;
				}
				if (eyebrowEl && eyebrowText) {
					eyebrowEl.textContent = eyebrowText;
				}
				if (checklistEl && checklistRaw) {
					try {
						var items = JSON.parse(checklistRaw);
						if (Array.isArray(items) && items.length > 0) {
							checklistEl.innerHTML = items.map(function(item) {
								return '<li><span class="hero-sugarcane__check">✓</span> ' + item + '</li>';
							}).join('');
						}
					} catch(e) {}
				}
			}
		}

		function startTimer() {
			// Auto-scroll disabled to prevent unwanted continuous sliding while watching video
			stopTimer();
		}

		function stopTimer() {
			if (timer) {
				clearInterval(timer);
				timer = null;
			}
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function(e) {
				e.preventDefault();
				goToSlide(currentIndex - 1);
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function(e) {
				e.preventDefault();
				goToSlide(currentIndex + 1);
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initHeroCarousel);
	} else {
		initHeroCarousel();
	}
})();
</script>
