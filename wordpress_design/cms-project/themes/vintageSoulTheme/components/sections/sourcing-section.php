<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$sourcing_data = (array) ( JsonFileProvider::read( 'data/content/sourcing.json' ) ?? array() );

$tag          = (string) ( $tag ?? ( $sourcing_data['tag'] ?? 'Our Heritage Sourcing' ) );
$title        = (string) ( $title ?? ( $sourcing_data['title'] ?? 'ETHICAL <em>Farm-To-Press</em> SOURCING' ) );
$eyebrow      = (string) ( $eyebrow ?? ( $sourcing_data['eyebrow'] ?? 'From Farm To Cold Extraction' ) );
$body         = (string) ( $body ?? ( $sourcing_data['body'] ?? 'We hand-select only mature, sunshine-ripened sugarcane grown without chemical ripeners. Pressed the traditional way on cold brass rolls to preserve all natural vitamins, live enzymes, and authentic sweetness.' ) );
$sign_lines   = (array) ( $sign_lines ?? ( $sourcing_data['sign_lines'] ?? array( 'FRESH CANE', 'PREMIUM QUALITY', 'DAILY COLD PRESSED', '100% PURE & NATURAL' ) ) );
$pillars      = (array) ( $pillars ?? ( $sourcing_data['pillars'] ?? array() ) );
$bg_watermark = (string) ( $bg_watermark ?? ( $sourcing_data['bg_watermark'] ?? '' ) );

$gallery = (array) ( $sourcing_data['gallery'] ?? array() );
if ( empty( $gallery ) ) {
	$fallback_img = (string) ( $sourcing_data['image'] ?? 'assets/images/sugarcane/stacks.jpg' );
	$gallery      = array(
		array(
			'image'       => $fallback_img,
			'title'       => 'Farm-Fresh Mature Cane Stacks',
			'caption'     => 'Hand-cut sugarcane harvested at peak sweetness brix.',
			'stamp_line1' => 'EST. 2014',
			'stamp_line2' => 'FARM FRESH',
			'stamp_line3' => 'STACKS',
		),
	);
}
?>
<section class="section section--sourcing sourcing-vintage paper-rough" id="sourcing">
	<?php if ( '' !== $bg_watermark ) : ?>
		<div class="section-cane-watermark" style="background-image: url('<?php echo esc_url( UrlHelper::resolve( $bg_watermark ) ); ?>');" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="container sourcing-vintage__container">
		
		<!-- Section Header -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'    => $tag,
				'title'  => 'ETHICAL <em>Farm-To-Press</em> SOURCING',
				'sub'    => $body,
				'ribbon' => true,
			)
		);
		?>

		<!-- 2-Column Traditional Layout -->
		<div class="sourcing-vintage__grid">
			
			<!-- Left: 3 Traditional Quality Pillar Cards -->
			<div class="sourcing-vintage__pillars">
				<?php foreach ( $pillars as $pillar ) : ?>
					<div class="sourcing-pillar-card frame--ornate">
						<div class="sourcing-pillar-card__icon-box">
							<span class="sourcing-pillar-card__num"><?php echo esc_html( $pillar['num'] ); ?></span>
							<span class="sourcing-pillar-card__icon"><?php echo IconHelper::render( (string) $pillar['icon'], '#f6d599', 18 ); // phpcs:ignore ?></span>
						</div>
						<div class="sourcing-pillar-card__content">
							<h3 class="sourcing-pillar-card__title"><?php echo esc_html( $pillar['title'] ); ?></h3>
							<p class="sourcing-pillar-card__desc"><?php echo esc_html( $pillar['desc'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>

				<!-- Action Buttons -->
				<div class="sourcing-vintage__actions">
					<a class="btn btn--primary-vintage btn--order-now" href="<?php echo esc_url( RouteService::url( 'history' ) ); ?>">
						<span class="btn__icon"><?php echo IconHelper::render( 'sugarcane', '#f6d599', 15 ); // phpcs:ignore ?></span>
						<span>ALL ABOUT CANE</span>
					</a>
					<a class="btn btn--secondary-vintage btn--outline-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
						<span class="btn__icon"><?php echo IconHelper::render( 'pin', '#f6d599', 15 ); // phpcs:ignore ?></span>
						<span>FIND OUR STALL</span>
					</a>
				</div>
			</div>

			<!-- Right: Multi-Photo Sourcing Carousel Showcase -->
			<div class="sourcing-vintage__media-wrap">
				<div class="sourcing-vintage__photo-frame frame--ornate" id="sourcing-photo-carousel">
					
					<!-- Slides Wrapper -->
					<div class="sourcing-carousel__slides" id="sourcing-slides-container">
						<?php foreach ( $gallery as $s_idx => $slide ) :
							$slide_img   = UrlHelper::resolve( (string) ( $slide['image'] ?? 'assets/images/sugarcane/stacks.jpg' ) );
							$slide_ttl   = (string) ( $slide['title'] ?? 'Farm-Fresh Cane' );
							$slide_cap   = (string) ( $slide['caption'] ?? '' );
							$stamp_1     = (string) ( $slide['stamp_line1'] ?? 'EST. 2014' );
							$stamp_2     = (string) ( $slide['stamp_line2'] ?? 'FARM FRESH' );
							$stamp_3     = (string) ( $slide['stamp_line3'] ?? 'HARVEST' );
						?>
							<div class="sourcing-carousel__slide<?php echo 0 === $s_idx ? ' is-active' : ''; ?>" data-slide-idx="<?php echo esc_attr( (string) $s_idx ); ?>">
								<img src="<?php echo esc_url( $slide_img ); ?>" alt="<?php echo esc_attr( $slide_ttl ); ?>" loading="lazy" class="sourcing-vintage__img">
								
								<!-- Slide Overlay Badge & Caption -->
								<div class="sourcing-carousel__overlay">
									<div class="sourcing-carousel__badge">
										<span>STAGE <?php echo esc_html( (string) ( $s_idx + 1 ) ); ?> OF <?php echo esc_html( (string) count( $gallery ) ); ?></span>
									</div>
									<div class="sourcing-carousel__caption">
										<h4 class="sourcing-carousel__title"><?php echo esc_html( $slide_ttl ); ?></h4>
										<?php if ( '' !== $slide_cap ) : ?>
											<p class="sourcing-carousel__desc"><?php echo esc_html( $slide_cap ); ?></p>
										<?php endif; ?>
									</div>
								</div>

								<!-- Stamp Circle -->
								<div class="sourcing-vintage__stamp stamp-circle">
									<span class="stamp-circle__line1"><?php echo esc_html( $stamp_1 ); ?></span>
									<span class="stamp-circle__line2"><?php echo esc_html( $stamp_2 ); ?></span>
									<span class="stamp-circle__line3"><?php echo esc_html( $stamp_3 ); ?></span>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- Navigation Controls -->
					<?php if ( count( $gallery ) > 1 ) : ?>
						<button type="button" class="sourcing-carousel__btn sourcing-carousel__btn--prev" id="sourcing-prev-btn" aria-label="<?php esc_attr_e( 'Previous Photo', 'vintagesoul' ); ?>">‹</button>
						<button type="button" class="sourcing-carousel__btn sourcing-carousel__btn--next" id="sourcing-next-btn" aria-label="<?php esc_attr_e( 'Next Photo', 'vintagesoul' ); ?>">›</button>
						
						<!-- Pagination Dots -->
						<div class="sourcing-carousel__dots" id="sourcing-dots-container">
							<?php foreach ( $gallery as $s_idx => $slide ) : ?>
								<button type="button" 
										class="sourcing-carousel__dot<?php echo 0 === $s_idx ? ' is-active' : ''; ?>" 
										data-slide-to="<?php echo esc_attr( (string) $s_idx ); ?>" 
										aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'vintagesoul' ), $s_idx + 1 ) ); ?>">
								</button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

				</div>

				<!-- Wooden Tradition Signboard Banner -->
				<div class="sourcing-signboard">
					<div class="sourcing-signboard__inner">
						<?php foreach ( $sign_lines as $idx => $line ) : ?>
							<?php if ( $idx > 0 ) : ?><span class="sourcing-signboard__dot">◆</span><?php endif; ?>
							<span class="sourcing-signboard__text"><?php echo esc_html( (string) $line ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		</div>

	</div>
</section>

<!-- Sourcing Multi-Photo Carousel Script -->
<script>
(function() {
	function initSourcingCarousel() {
		var carousel = document.getElementById('sourcing-photo-carousel');
		if (!carousel) return;

		var slides = carousel.querySelectorAll('.sourcing-carousel__slide');
		var dots = carousel.querySelectorAll('.sourcing-carousel__dot');
		var prevBtn = document.getElementById('sourcing-prev-btn');
		var nextBtn = document.getElementById('sourcing-next-btn');

		if (slides.length <= 1) return;

		var currentIndex = 0;
		var autoTimer = null;

		function showSlide(index) {
			if (index < 0) index = slides.length - 1;
			if (index >= slides.length) index = 0;
			currentIndex = index;

			slides.forEach(function(slide, idx) {
				if (idx === currentIndex) {
					slide.classList.add('is-active');
					slide.style.opacity = '1';
					slide.style.visibility = 'visible';
					slide.style.zIndex = '2';
				} else {
					slide.classList.remove('is-active');
					slide.style.opacity = '0';
					slide.style.visibility = 'hidden';
					slide.style.zIndex = '1';
				}
			});

			dots.forEach(function(dot, idx) {
				if (idx === currentIndex) {
					dot.classList.add('is-active');
				} else {
					dot.classList.remove('is-active');
				}
			});
		}

		function startAuto() {
			stopAuto();
			autoTimer = setInterval(function() {
				showSlide(currentIndex + 1);
			}, 4200);
		}

		function stopAuto() {
			if (autoTimer) {
				clearInterval(autoTimer);
				autoTimer = null;
			}
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function(e) {
				e.preventDefault();
				showSlide(currentIndex - 1);
				startAuto();
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function(e) {
				e.preventDefault();
				showSlide(currentIndex + 1);
				startAuto();
			});
		}

		dots.forEach(function(dot) {
			dot.addEventListener('click', function(e) {
				e.preventDefault();
				var targetIdx = parseInt(this.getAttribute('data-slide-to'), 10);
				if (!isNaN(targetIdx)) {
					showSlide(targetIdx);
					startAuto();
				}
			});
		});

		carousel.addEventListener('mouseenter', stopAuto);
		carousel.addEventListener('mouseleave', startAuto);

		// Touch swipe support
		var touchStartX = 0;
		carousel.addEventListener('touchstart', function(e) {
			touchStartX = e.changedTouches[0].screenX;
			stopAuto();
		}, { passive: true });

		carousel.addEventListener('touchend', function(e) {
			var touchEndX = e.changedTouches[0].screenX;
			var diff = touchEndX - touchStartX;
			if (Math.abs(diff) > 40) {
				if (diff < 0) {
					showSlide(currentIndex + 1);
				} else {
					showSlide(currentIndex - 1);
				}
			}
			startAuto();
		}, { passive: true });

		showSlide(0);
		startAuto();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initSourcingCarousel);
	} else {
		initSourcingCarousel();
	}
})();
</script>
