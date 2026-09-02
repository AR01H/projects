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

$title = (string) ( $title ?? ( $certs_data['title'] ?? 'Quality / Certifications' ) );
$body  = (string) ( $body ?? ( $certs_data['body'] ?? 'Certified and verified by leading food safety and quality institutions across the UK and internationally.' ) );
$items = (array) ( $items ?? ( $certs_data['items'] ?? array() ) );

if ( empty( $items ) ) {
	$items = (array) ( $certs_data['items'] ?? array() );
}

// Split into slides of 4 cards each (2x2 grid per page)
$slides       = array_chunk( $items, 4 );
$bg_watermark = (string) ( $bg_watermark ?? ( $certs_data['bg_watermark'] ?? 'assets/images/backgrounds/sugarcane_farm_plantation_engraving.jpg' ) );

$first_item     = ! empty( $items[0] ) ? (array) $items[0] : array();
$first_seal_img = (string) ( $first_item['seal_img'] ?? 'assets/images/partners/food-hygiene-5.svg' );
$first_checklist = (array) ( $first_item['checklist'] ?? array(
	'FSA 5-Star Maximum Hygiene Score',
	'Full Food Safety Management Compliance',
	'Regular Unannounced Independent Audits',
) );
?>
<section class="section section--certs certs-vintage paper-rough" id="certifications">
	<?php if ( '' !== $bg_watermark ) : ?>
		<div class="section-cane-watermark" style="background-image: url('<?php echo esc_url( UrlHelper::resolve( $bg_watermark ) ); ?>');" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="container certs-vintage__container">
		
		<!-- 1. Section Header -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'    => (string) ( $certs_data['tag'] ?? 'Trust & Standards' ),
				'title'  => (string) ( $certs_data['title'] ?? 'FOOD SAFETY REGISTERED &amp; <em>Fully Compliant</em>' ),
				'sub'    => $body,
				'ribbon' => true,
			)
		);
		?>

		<!-- 2. Main Balanced 2-Column Stage (Left: 2x2 Grid with multi-page support • Right: Live Linked Master Accreditation Card) -->
		<div class="certs-showcase-stage" id="certs-showcase-stage">
			
			<!-- Left Side: Paginated Accreditation Cards Grid -->
			<div class="certs-showcase-left">
				<div class="certs-slider-container" id="certs-slider-container">
					<?php foreach ( $slides as $slide_idx => $slide_cards ) : ?>
						<div class="certs-slide <?php echo 0 === $slide_idx ? 'is-active' : ''; ?>" data-slide-index="<?php echo esc_attr( (string) $slide_idx ); ?>">
							<div class="certs-grid-2x2">
								<?php foreach ( $slide_cards as $card_idx => $item ) :
									$global_idx = ( $slide_idx * 4 ) + $card_idx;
									$code       = (string) ( $item['code'] ?? 'CERT' );
									$c_name     = (string) ( $item['title'] ?? '' );
									$c_desc     = (string) ( $item['desc'] ?? '' );
									$c_auth     = (string) ( $item['authority'] ?? 'Verified Standard' );
									$c_icon     = (string) ( $item['icon'] ?? '⭐' );
									$c_badge    = (string) ( $item['badge'] ?? 'OFFICIALLY VERIFIED' );
									$c_action   = (string) ( $item['action_label'] ?? 'UK CERTIFIED ARTISAN JUICE' );
									$c_seal_img = UrlHelper::resolve( (string) ( $item['seal_img'] ?? 'assets/images/partners/master-accreditation-seal.svg' ) );
									$c_chk      = (array) ( $item['checklist'] ?? array() );
								?>
									<button type="button" 
											class="cert-h-card frame--rough-cut<?php echo 0 === $global_idx ? ' is-active' : ''; ?>"
											onclick="window.selectCertificationCard && window.selectCertificationCard(this)"
											data-cert-idx="<?php echo esc_attr( (string) $global_idx ); ?>"
											data-cert-code="<?php echo esc_attr( $code ); ?>"
											data-cert-title="<?php echo esc_attr( $c_name ); ?>"
											data-cert-authority="<?php echo esc_attr( $c_auth ); ?>"
											data-cert-desc="<?php echo esc_attr( $c_desc ); ?>"
											data-cert-icon="<?php echo esc_attr( $c_icon ); ?>"
											data-cert-badge="<?php echo esc_attr( $c_badge ); ?>"
											data-cert-action="<?php echo esc_attr( $c_action ); ?>"
											data-cert-seal-img="<?php echo esc_url( $c_seal_img ); ?>"
											data-cert-checklist='<?php echo esc_attr( (string) wp_json_encode( $c_chk ) ); ?>'>
										
										<!-- Left Circle Icon Seal (No text clutter, perfectly centered) -->
										<div class="cert-h-card__seal-wrap">
											<div class="cert-h-card__seal">
												<span class="cert-h-card__seal-icon"><?php echo IconHelper::render( $c_icon, '#f6d599', 24 ); ?></span>
											</div>
										</div>

										<!-- Right Details -->
										<div class="cert-h-card__details">
											<span class="cert-h-card__auth">
												<span class="cert-h-card__check">✓</span>
												<?php echo esc_html( $c_auth ); ?>
											</span>
											<h3 class="cert-h-card__title"><?php echo esc_html( $c_name ); ?></h3>
											<p class="cert-h-card__desc"><?php echo esc_html( $c_desc ); ?></p>
										</div>

									</button>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Slider Navigation Dots (If 5, 6, 8 or more items exist) -->
				<?php if ( count( $slides ) > 1 ) : ?>
					<div class="certs-carousel-dots" id="certs-carousel-dots" aria-label="Certifications navigation">
						<?php foreach ( $slides as $s_i => $s_data ) : ?>
							<button type="button" 
									class="certs-dot <?php echo 0 === $s_i ? 'is-active' : ''; ?>" 
									data-target-slide="<?php echo esc_attr( (string) $s_i ); ?>"
									aria-label="<?php echo esc_attr( 'Page ' . ( $s_i + 1 ) ); ?>">
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Right Side: Dynamically Linked Master Certification Box -->
			<div class="certs-showcase-right">
				<div class="certs-featured-logo-card frame--ornate" id="cert-master-card">
					
					<!-- Top Wax Seal Stamp -->
					<div class="certs-featured-logo-card__seal" id="cert-master-badge-box">
						<span class="certs-seal-icon" id="cert-master-badge-icon">🏆</span>
						<span class="certs-seal-text" id="cert-master-badge-text"><?php echo esc_html( (string) ( $first_item['badge'] ?? 'VERIFIED' ) ); ?></span>
					</div>

					<!-- Master Accreditation Certifying Seal Image Container -->
					<div class="certs-featured-logo-card__image-wrap" id="cert-master-image-wrap">
						<img id="cert-master-img" 
							src="<?php echo esc_url( UrlHelper::resolve( $first_seal_img ) ); ?>" 
							alt="<?php echo esc_attr( (string) ( $first_item['title'] ?? 'Accreditation Seal' ) ); ?>" 
							class="certs-featured-logo-card__img" 
							loading="lazy">
					</div>

					<!-- Title & Subtitle Banner -->
					<div class="certs-featured-logo-card__footer">
						<h3 class="certs-featured-logo-card__title" id="cert-master-title">
							<?php echo esc_html( (string) ( $first_item['title'] ?? 'Hygiene Standards' ) ); ?>
						</h3>
						<span class="certs-featured-logo-card__action-text" id="cert-master-action">
							<?php echo esc_html( (string) ( $first_item['action_label'] ?? 'THE CANE HOUSE FOOD HYGIENE CERTIFIED' ) ); ?>
						</span>
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
		var authority = card.getAttribute('data-cert-authority') || '';
		var desc = card.getAttribute('data-cert-desc') || '';
		var badge = card.getAttribute('data-cert-badge') || 'VERIFIED';
		var action = card.getAttribute('data-cert-action') || 'UK CERTIFIED ARTISAN JUICE';
		var sealImg = card.getAttribute('data-cert-seal-img') || '';
		var checklistRaw = card.getAttribute('data-cert-checklist') || '';

		// 3. Elements in Master Card
		var masterCard = document.getElementById('cert-master-card');
		var badgeText = document.getElementById('cert-master-badge-text');
		var masterImg = document.getElementById('cert-master-img');
		var masterTitle = document.getElementById('cert-master-title');
		var masterDesc = document.getElementById('cert-master-desc');
		var masterChecklist = document.getElementById('cert-master-checklist');
		var masterAction = document.getElementById('cert-master-action');

		// Visual update transition
		if (masterCard) {
			masterCard.style.opacity = '0.4';
			masterCard.style.transform = 'scale(0.99)';
			setTimeout(function() {
				masterCard.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
				masterCard.style.opacity = '1';
				masterCard.style.transform = 'scale(1)';
			}, 50);
		}

		if (badgeText) badgeText.textContent = badge;
		if (masterImg && sealImg) {
			masterImg.src = sealImg;
			masterImg.alt = title;
		}
		if (masterTitle) masterTitle.innerHTML = title;
		if (masterDesc) masterDesc.textContent = desc;
		if (masterAction) masterAction.textContent = action;

		if (masterChecklist && checklistRaw) {
			try {
				var parsedChk = JSON.parse(checklistRaw);
				if (Array.isArray(parsedChk) && parsedChk.length > 0) {
					masterChecklist.innerHTML = parsedChk.map(function(item) {
						return '<li><span class="chk-icon">✓</span> <span>' + item + '</span></li>';
					}).join('');
				}
			} catch(e) {}
		}
	};

	function initCertsSlider() {
		var section = document.getElementById('certifications');
		if (!section) return;

		var dots = section.querySelectorAll('.certs-dot');
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

			// Select the first card on the newly visible slide
			var activeSlideEl = slides[currentSlide];
			if (activeSlideEl) {
				var firstCardOnSlide = activeSlideEl.querySelector('.cert-h-card');
				if (firstCardOnSlide) {
					window.selectCertificationCard(firstCardOnSlide);
				}
			}
		}

		dots.forEach(function(dot) {
			dot.addEventListener('click', function(e) {
				e.preventDefault();
				var targetIdx = parseInt(dot.getAttribute('data-target-slide') || '0', 10);
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
