<?php
/**
 * VintageSoulTheme - Quality & Certifications Section
 *
 * Wireframe Layout:
 * - Left: Paginated 2x2 Grid of horizontal certification cards (handles 4, 5, 6, 8+ cards cleanly).
 * - Right: Dynamic Featured Master Accreditation Box that updates live with image assets when clicking ANY certification card.
 * - Bottom: Interactive navigation controls (Prev/Next buttons + Page dots).
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$certs_data = (array) ( JsonFileProvider::read( 'data/content/certifications.json' ) ?? array() );

$tag          = (string) ( $tag ?? ( $certs_data['tag'] ?? '' ) );
$title        = (string) ( $title ?? ( $certs_data['title'] ?? '' ) );
$body         = (string) ( $body ?? ( $certs_data['body'] ?? '' ) );
$raw_groups   = (array) ( $groups ?? ( $certs_data['groups'] ?? array() ) );
$raw_items    = (array) ( $items ?? ( $certs_data['items'] ?? array() ) );
$bg_watermark = (string) ( $bg_watermark ?? ( $certs_data['bg_watermark'] ?? '' ) );

// Build standardized slide structure from either groups or items
$slides = array();

if ( ! empty( $raw_groups ) ) {
	foreach ( $raw_groups as $g_idx => $grp ) {
		$g_master = (array) ( $grp['master'] ?? array() );
		$g_items  = (array) ( $grp['items'] ?? array() );
		$slides[] = array(
			'name'   => (string) ( $grp['name'] ?? ( 'Group ' . ( $g_idx + 1 ) ) ),
			'master' => $g_master,
			'items'  => $g_items,
		);
	}
} elseif ( ! empty( $raw_items ) ) {
	$chunks = array_chunk( $raw_items, 4 );
	$def_master = (array) ( $master ?? ( $certs_data['master'] ?? ( $raw_items[0] ?? array() ) ) );
	foreach ( $chunks as $c_idx => $c_items ) {
		$slides[] = array(
			'name'   => 'Page ' . ( $c_idx + 1 ),
			'master' => $def_master,
			'items'  => $c_items,
		);
	}
}

if ( empty( $slides ) ) {
	return;
}

$first_slide    = $slides[0];
$master_item    = (array) ( $first_slide['master'] ?? array() );
$first_seal_img = (string) ( $master_item['seal_img'] ?? '' );
?>
<section class="section section--certs section--dark-botanical certs-vintage" id="certifications">
	<!-- 1. Ambient Animated Glowing Gradient Aura -->
	<div class="certs-vintage__ambient-glow" aria-hidden="true"></div>

	<!-- 2. Botanical Watermark with Gentle Parallax Drift -->
	<?php if ( '' !== $bg_watermark ) : ?>
		<div class="certs-vintage__watermark section-cane-watermark" style="background-image: url('<?php echo esc_url( UrlHelper::resolve( $bg_watermark ) ); ?>');" aria-hidden="true"></div>
	<?php endif; ?>

	<!-- 3. Floating Vintage Botanical Light Particles -->
	<div class="certs-vintage__particles" aria-hidden="true">
		<span class="certs-particle certs-particle--1"></span>
		<span class="certs-particle certs-particle--2"></span>
		<span class="certs-particle certs-particle--3"></span>
	</div>

	<div class="container certs-vintage__container">
		
		<!-- 1. Section Header -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => $tag,
				'title'   => $title,
				'sub'     => $body,
				'variant' => 'dark',
				'ribbon'  => true,
			)
		);
		?>

		<!-- 2. Main Balanced 2-Column Stage -->
		<div class="certs-showcase-stage" id="certs-showcase-stage">
			
			<!-- Left Side: Paginated Accreditation Cards Grid -->
			<div class="certs-showcase-left">
				<div class="certs-slider-container" id="certs-slider-container">
					<?php foreach ( $slides as $slide_idx => $slide_data ) :
						$slide_items  = (array) ( $slide_data['items'] ?? array() );
						$slide_master = (array) ( $slide_data['master'] ?? array() );
					?>
						<div class="certs-slide <?php echo 0 === $slide_idx ? 'is-active' : ''; ?>" 
							 data-slide-index="<?php echo esc_attr( (string) $slide_idx ); ?>"
							 data-slide-name="<?php echo esc_attr( (string) ( $slide_data['name'] ?? '' ) ); ?>"
							 data-slide-master='<?php echo esc_attr( (string) wp_json_encode( $slide_master ) ); ?>'>
							<div class="certs-grid-2x2">
								<?php foreach ( $slide_items as $card_idx => $item ) :
									$global_idx = ( $slide_idx * 100 ) + $card_idx;
									$code       = (string) ( $item['code'] ?? 'CERT' );
									$c_name     = (string) ( $item['title'] ?? '' );
									$c_desc     = (string) ( $item['desc'] ?? '' );
									$c_icon     = (string) ( $item['icon'] ?? 'star' );
									$c_badge    = (string) ( $item['badge'] ?? '' );
								?>
									<button type="button" 
											class="cert-h-card frame--rough-cut<?php echo ( 0 === $slide_idx && 0 === $card_idx ) ? ' is-active' : ''; ?>"
											onclick="window.selectCertificationCard && window.selectCertificationCard(this)"
											data-cert-idx="<?php echo esc_attr( (string) $global_idx ); ?>"
											data-cert-code="<?php echo esc_attr( $code ); ?>"
											data-cert-title="<?php echo esc_attr( $c_name ); ?>"
											data-cert-desc="<?php echo esc_attr( $c_desc ); ?>"
											data-cert-icon="<?php echo esc_attr( $c_icon ); ?>"
											data-cert-badge="<?php echo esc_attr( $c_badge ); ?>">
										
										<!-- Left Circle Icon Seal -->
										<div class="cert-h-card__seal-wrap">
											<div class="cert-h-card__seal">
												<span class="cert-h-card__seal-icon"><?php echo IconHelper::render( $c_icon, '#f6d599', 24 ); ?></span>
											</div>
										</div>

										<!-- Right Details -->
										<div class="cert-h-card__details">
											<?php if ( '' !== $c_badge ) : ?>
												<span class="cert-h-card__auth">
													<span class="cert-h-card__check">✦</span>
													<?php echo esc_html( $c_badge ); ?>
												</span>
											<?php endif; ?>
											<h3 class="cert-h-card__title"><?php echo esc_html( $c_name ); ?></h3>
											<?php if ( '' !== $c_desc ) : ?>
												<p class="cert-h-card__desc"><?php echo esc_html( $c_desc ); ?></p>
											<?php endif; ?>
										</div>

									</button>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Carousel Navigation Controls -->
				<?php if ( count( $slides ) > 1 ) : ?>
					<div class="certs-carousel-nav">
						<button type="button" class="certs-nav-btn certs-nav-btn--prev" id="certs-prev-btn" aria-label="Previous Certifications">‹</button>
						<div class="certs-nav-dots" id="certs-dots">
							<?php foreach ( $slides as $s_idx => $s_items ) : ?>
								<button type="button" class="certs-nav-dot<?php echo 0 === $s_idx ? ' is-active' : ''; ?>" data-slide-target="<?php echo esc_attr( (string) $s_idx ); ?>" aria-label="Page <?php echo esc_attr( (string) ( $s_idx + 1 ) ); ?>"></button>
							<?php endforeach; ?>
						</div>
						<button type="button" class="certs-nav-btn certs-nav-btn--next" id="certs-next-btn" aria-label="Next Certifications">›</button>
					</div>
				<?php endif; ?>

			</div>

			<!-- Right Side: Dynamically Linked Master Certification Box -->
			<div class="certs-showcase-right">
				<div class="certs-featured-logo-card frame--ornate" id="cert-master-card">
					
					<!-- Top Wax Seal Stamp -->
					<div class="certs-featured-logo-card__seal" id="cert-master-badge-box">
						<span class="certs-seal-icon">✦</span>
						<span class="certs-seal-text" id="cert-master-badge-text"><?php echo esc_html( (string) ( $master_item['badge'] ?? 'VERIFIED' ) ); ?></span>
					</div>

					<!-- Master Accreditation Certifying Seal Image Container (Linked) -->
					<?php $master_url = (string) ( $master_item['url'] ?? '#' ); ?>
					<a id="cert-master-link" 
					   href="<?php echo esc_url( UrlHelper::resolve( $master_url ) ); ?>" 
					   target="_blank" 
					   rel="noopener noreferrer" 
					   class="certs-featured-logo-card__image-link" 
					   title="<?php echo esc_attr( (string) ( $master_item['title'] ?? 'Verify Accreditation' ) ); ?>">
						<div class="certs-featured-logo-card__image-wrap" id="cert-master-image-wrap">
							<img id="cert-master-img" 
								src="<?php echo esc_url( UrlHelper::resolve( $first_seal_img ) ); ?>" 
								alt="<?php echo esc_attr( (string) ( $master_item['title'] ?? '' ) ); ?>" 
								class="certs-featured-logo-card__img" 
								loading="lazy">
						</div>
					</a>

					<!-- Title, Authority & Verified Action Badge -->
					<div class="certs-featured-logo-card__footer">
						<?php if ( ! empty( $master_item['authority'] ) ) : ?>
							<span class="certs-featured-logo-card__auth" id="cert-master-auth">
								<span class="chk-icon">✓</span> <?php echo esc_html( (string) $master_item['authority'] ); ?>
							</span>
						<?php endif; ?>

						<h3 class="certs-featured-logo-card__title" id="cert-master-title">
							<?php echo esc_html( (string) ( $master_item['title'] ?? '' ) ); ?>
						</h3>

						<div class="certs-featured-logo-card__action-wrap">
							<a id="cert-master-action-link" 
							   href="<?php echo esc_url( UrlHelper::resolve( $master_url ) ); ?>" 
							   target="_blank" 
							   rel="noopener noreferrer" 
							   class="certs-featured-logo-card__action-btn">
								<span class="certs-featured-logo-card__action-text" id="cert-master-action">
									🛡️ <?php echo esc_html( (string) ( $master_item['action_label'] ?? '' ) ); ?> ↗
								</span>
							</a>
						</div>
					</div>

				</div>
			</div>

		</div>

	</div>
</section>

<!-- Interactive Live Certification Sync & Slide Switching Script -->
<script>
(function() {
	window.selectCertificationCard = function(card) {
		if (!card) return;

		var section = document.getElementById('certifications');
		if (!section) return;

		// 1. Highlight clicked card
		var allCards = section.querySelectorAll('.cert-h-card');
		allCards.forEach(function(c) { c.classList.remove('is-active'); });
		card.classList.add('is-active');

		// 2. Read Card Data Attributes
		var title = card.getAttribute('data-cert-title') || '';
		var badge = card.getAttribute('data-cert-badge') || '';

		// 3. Subtle highlight on master card badge if clicked
		var badgeText = document.getElementById('cert-master-badge-text');
		if (badgeText && badge) {
			badgeText.textContent = badge;
		}
	};

	function initCertsSlider() {
		var section = document.getElementById('certifications');
		if (!section) return;

		var dots = section.querySelectorAll('.certs-nav-dot');
		var slides = section.querySelectorAll('.certs-slide');
		var prevBtn = document.getElementById('certs-prev-btn');
		var nextBtn = document.getElementById('certs-next-btn');

		if (!slides.length) return;

		var currentSlide = 0;

		function goToSlide(slideIdx) {
			if (slideIdx < 0) slideIdx = slides.length - 1;
			if (slideIdx >= slides.length) slideIdx = 0;
			currentSlide = slideIdx;

			slides.forEach(function(slide, idx) {
				if (idx === currentSlide) {
					slide.classList.add('is-active');
				} else {
					slide.classList.remove('is-active');
				}
			});

			dots.forEach(function(dot, idx) {
				if (idx === currentSlide) {
					dot.classList.add('is-active');
				} else {
					dot.classList.remove('is-active');
				}
			});

			// Sync the Master Card with the newly active group's master data
			var activeSlideEl = slides[currentSlide];
			if (activeSlideEl) {
				var rawMaster = activeSlideEl.getAttribute('data-slide-master');
				if (rawMaster) {
					try {
						var parsedMaster = JSON.parse(rawMaster);
						if (parsedMaster && parsedMaster.title) {
							if (masterCard) {
								masterCard.style.opacity = '0.4';
								masterCard.style.transform = 'scale(0.99)';
								setTimeout(function() {
									masterCard.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
									masterCard.style.opacity = '1';
									masterCard.style.transform = 'scale(1)';
								}, 50);
							}
							if (badgeText && parsedMaster.badge) badgeText.textContent = parsedMaster.badge;
							if (masterImg && parsedMaster.seal_img) {
								masterImg.src = parsedMaster.seal_img;
								masterImg.alt = parsedMaster.title;
							}
							if (masterAuth) masterAuth.innerHTML = parsedMaster.authority ? '<span class="chk-icon">✓</span> ' + parsedMaster.authority : '';
							if (masterTitle) masterTitle.innerHTML = parsedMaster.title;
							if (masterAction) masterAction.innerHTML = '🛡️ ' + (parsedMaster.action_label || '') + ' ↗';
							if (masterLink && parsedMaster.url) {
								masterLink.href = parsedMaster.url;
								masterLink.title = parsedMaster.title;
							}
							if (masterActionLink && parsedMaster.url) {
								masterActionLink.href = parsedMaster.url;
							}
						}
					} catch(e) {}
				}

				// Highlight first card on slide
				var allCards = section.querySelectorAll('.cert-h-card');
				allCards.forEach(function(c) { c.classList.remove('is-active'); });
				var firstCardOnSlide = activeSlideEl.querySelector('.cert-h-card');
				if (firstCardOnSlide) {
					firstCardOnSlide.classList.add('is-active');
				}
			}
		}

		dots.forEach(function(dot) {
			dot.addEventListener('click', function(e) {
				e.preventDefault();
				var targetIdx = parseInt(dot.getAttribute('data-slide-target') || '0', 10);
				goToSlide(targetIdx);
			});
		});

		if (prevBtn) {
			prevBtn.addEventListener('click', function(e) {
				e.preventDefault();
				goToSlide(currentSlide - 1);
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function(e) {
				e.preventDefault();
				goToSlide(currentSlide + 1);
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCertsSlider);
	} else {
		initCertsSlider();
	}
})();
</script>
