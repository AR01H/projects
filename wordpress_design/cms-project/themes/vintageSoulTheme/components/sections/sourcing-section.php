<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$sourcing_data = (array) ( JsonFileProvider::read( 'data/content/sourcing.json' ) ?? array() );

$tag          = (string) ( $tag ?? ( $sourcing_data['tag'] ?? '' ) );
$title        = (string) ( $title ?? ( $sourcing_data['title'] ?? '' ) );
$eyebrow      = (string) ( $eyebrow ?? ( $sourcing_data['eyebrow'] ?? '' ) );
$body         = (string) ( $body ?? ( $sourcing_data['body'] ?? '' ) );
$sign_lines   = (array) ( $sign_lines ?? ( $sourcing_data['sign_lines'] ?? array() ) );
$pillars      = (array) ( $pillars ?? ( $sourcing_data['pillars'] ?? array() ) );
$buttons      = (array) ( $buttons ?? ( $sourcing_data['buttons'] ?? array() ) );
$bg_watermark = (string) ( $bg_watermark ?? ( $sourcing_data['bg_watermark'] ?? '' ) );
$gallery      = (array) ( $sourcing_data['gallery'] ?? array() );
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
				'title'  => $title,
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
							<span class="sourcing-pillar-card__num"><?php echo esc_html( (string) ( $pillar['num'] ?? '' ) ); ?></span>
							<span class="sourcing-pillar-card__icon"><?php echo IconHelper::render( (string) ( $pillar['icon'] ?? 'plant' ), '#f6d599', 18 ); // phpcs:ignore ?></span>
						</div>
						<div class="sourcing-pillar-card__content">
							<h3 class="sourcing-pillar-card__title"><?php echo esc_html( (string) ( $pillar['title'] ?? '' ) ); ?></h3>
							<p class="sourcing-pillar-card__desc"><?php echo esc_html( (string) ( $pillar['desc'] ?? '' ) ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>

				<!-- Action Buttons -->
				<?php if ( ! empty( $buttons ) ) : ?>
					<div class="sourcing-vintage__actions">
						<?php foreach ( $buttons as $btn ) :
							$btn_lbl   = (string) ( $btn['label'] ?? '' );
							$btn_icon  = (string) ( $btn['icon'] ?? 'sugarcane' );
							$btn_route = (string) ( $btn['route'] ?? ( $btn['link'] ?? 'contact' ) );
							$btn_style = (string) ( $btn['style'] ?? 'primary' );
							$btn_url   = ( 0 === strpos( $btn_route, '/' ) || 0 === strpos( $btn_route, 'http' ) )
								? $btn_route
								: RouteService::url( $btn_route );
							$btn_class = 'secondary' === $btn_style || 'outline' === $btn_style
								? 'btn btn--secondary-vintage btn--outline-vintage'
								: 'btn btn--primary-vintage btn--order-now';
						?>
							<a class="<?php echo esc_attr( $btn_class ); ?>" href="<?php echo esc_url( $btn_url ); ?>">
								<span class="btn__icon"><?php echo IconHelper::render( $btn_icon, '#f6d599', 15 ); // phpcs:ignore ?></span>
								<span><?php echo esc_html( $btn_lbl ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Right: Multi-Photo Sourcing Carousel Showcase -->
			<?php if ( ! empty( $gallery ) ) : ?>
				<div class="sourcing-vintage__media-wrap">
					<div class="sourcing-vintage__photo-frame frame--ornate" id="sourcing-photo-carousel">
						
						<!-- Slides Wrapper -->
						<div class="sourcing-carousel__slides" id="sourcing-slides-container">
							<?php foreach ( $gallery as $s_idx => $slide ) :
								$slide_img   = UrlHelper::resolve( (string) ( $slide['image'] ?? '' ) );
								$slide_ttl   = (string) ( $slide['title'] ?? '' );
								$slide_cap   = (string) ( $slide['caption'] ?? '' );
								$stamp_1     = (string) ( $slide['stamp_line1'] ?? '' );
								$stamp_2     = (string) ( $slide['stamp_line2'] ?? '' );
								$stamp_3     = (string) ( $slide['stamp_line3'] ?? '' );
							?>
								<div class="sourcing-carousel__slide<?php echo 0 === $s_idx ? ' is-active' : ''; ?>" data-slide-idx="<?php echo esc_attr( (string) $s_idx ); ?>">
									<img src="<?php echo esc_url( $slide_img ); ?>" alt="<?php echo esc_attr( $slide_ttl ); ?>" loading="lazy" class="sourcing-vintage__img">
									
									<!-- Slide Overlay Badge & Caption -->
									<div class="sourcing-carousel__overlay">
										<div class="sourcing-carousel__badge">
											<span><?php echo esc_html( (string) ( $s_idx + 1 ) ); ?> / <?php echo esc_html( (string) count( $gallery ) ); ?></span>
										</div>
										<div class="sourcing-carousel__caption">
											<?php if ( '' !== $slide_ttl ) : ?>
												<h4 class="sourcing-carousel__title"><?php echo esc_html( $slide_ttl ); ?></h4>
											<?php endif; ?>
											<?php if ( '' !== $slide_cap ) : ?>
												<p class="sourcing-carousel__desc"><?php echo esc_html( $slide_cap ); ?></p>
											<?php endif; ?>
										</div>
									</div>

									<!-- Stamp Circle -->
									<?php if ( '' !== $stamp_1 || '' !== $stamp_2 || '' !== $stamp_3 ) : ?>
										<div class="sourcing-vintage__stamp stamp-circle">
											<span class="stamp-circle__line1"><?php echo esc_html( $stamp_1 ); ?></span>
											<span class="stamp-circle__line2"><?php echo esc_html( $stamp_2 ); ?></span>
											<span class="stamp-circle__line3"><?php echo esc_html( $stamp_3 ); ?></span>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>

						<!-- Navigation Controls -->
						<?php if ( count( $gallery ) > 1 ) : ?>
							<button type="button" class="sourcing-carousel__btn sourcing-carousel__btn--prev" id="sourcing-prev-btn" aria-label="Previous Slide">‹</button>
							<button type="button" class="sourcing-carousel__btn sourcing-carousel__btn--next" id="sourcing-next-btn" aria-label="Next Slide">›</button>
							
							<!-- Pagination Dots -->
							<div class="sourcing-carousel__dots" id="sourcing-dots-container">
								<?php foreach ( $gallery as $s_idx => $slide ) : ?>
									<button type="button" 
											class="sourcing-carousel__dot<?php echo 0 === $s_idx ? ' is-active' : ''; ?>" 
											data-slide-to="<?php echo esc_attr( (string) $s_idx ); ?>" 
											aria-label="Slide <?php echo esc_attr( (string) ( $s_idx + 1 ) ); ?>">
									</button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

					</div>

					<!-- Wooden Tradition Signboard Banner -->
					<?php if ( ! empty( $sign_lines ) ) : ?>
						<div class="sourcing-signboard">
							<div class="sourcing-signboard__inner">
								<?php foreach ( $sign_lines as $idx => $line ) : ?>
									<?php if ( $idx > 0 ) : ?><span class="sourcing-signboard__dot">◆</span><?php endif; ?>
									<span class="sourcing-signboard__text"><?php echo esc_html( (string) $line ); ?></span>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

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
