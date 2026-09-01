<?php
/**
 * VintageSoulTheme - Quality & Certifications Section
 *
 * Wireframe Layout:
 * - Left: 2x2 Grid of horizontal certification cards (4 cards per slide) with circular badges on the left of each card.
 * - Right: Featured large Logo & Master Accreditation Showcase card with 3 quality guarantee check points.
 * - Bottom: Interactive pagination / navigation dots matching the exact number of slides.
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$title = (string) ( $title ?? 'Quality / Certifications' );
$body  = (string) ( $body ?? 'Certified and verified by leading food safety and quality institutions across the UK and internationally.' );
$items = (array) ( $items ?? array() );

if ( empty( $items ) ) {
	$items = array(
		array(
			'code'      => '5★ HYGIENE',
			'title'     => 'Food Hygiene Rating 5',
			'desc'      => 'Awarded the top 5-Star rating by the UK Food Standards Agency for impeccable cleanliness.',
			'authority' => 'UK Food Standards Agency',
			'icon'      => '⭐',
		),
		array(
			'code'      => 'ISO 22000',
			'title'     => 'ISO 22000:2018',
			'desc'      => 'International Food Safety Management System standard certifying our sterile cold-press chain.',
			'authority' => 'ISO Standards',
			'icon'      => '🛡️',
		),
		array(
			'code'      => 'ORGANIC',
			'title'     => 'Soil Association Organic',
			'desc'      => '100% sustainably grown sugarcane harvested without synthetic pesticides or chemicals.',
			'authority' => 'Organic UK',
			'icon'      => '🌿',
		),
		array(
			'code'      => 'SALSA',
			'title'     => 'SALSA Accreditation',
			'desc'      => 'Safe and Local Supplier Approval ensuring the highest operational integrity for UK artisans.',
			'authority' => 'SALSA UK',
			'icon'      => '📜',
		),
	);
}

// Split into slides of 4 cards each (2x2 grid)
$slides       = array_chunk( $items, 4 );
$bg_watermark = (string) ( $bg_watermark ?? ( $certs_data['bg_watermark'] ?? 'assets/images/backgrounds/sugarcane_farm_plantation_engraving.jpg' ) );
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
				'tag'     => 'Trust & Standards',
				'title'   => 'QUALITY &amp; <em>Certifications</em>',
				'eyebrow' => 'Verified Food Safety & Accreditation',
				'body'    => $body,
				'ribbon'  => true,
			)
		);
		?>

		<!-- 2. Main 2-Column Showcase Layout (2x2 Grid Left + Large Featured Logo Box Right) -->
		<div class="certs-showcase-stage">
			
			<!-- Left Side: Paginated 2x2 Grid Slides -->
			<div class="certs-showcase-left">
				<div class="certs-slider-container" id="certs-slider-container">
					<?php foreach ( $slides as $slide_idx => $slide_cards ) : ?>
						<div class="certs-slide <?php echo 0 === $slide_idx ? 'is-active' : ''; ?>" data-slide-index="<?php echo esc_attr( $slide_idx ); ?>">
							<div class="certs-grid-2x2">
								<?php foreach ( $slide_cards as $card_idx => $item ) :
									$code      = (string) ( $item['code'] ?? 'CERT' );
									$c_name    = (string) ( $item['title'] ?? '' );
									$c_desc    = (string) ( $item['desc'] ?? '' );
									$c_auth    = (string) ( $item['authority'] ?? 'Verified Standard' );
									$c_icon    = (string) ( $item['icon'] ?? '⭐' );
									$c_partner = (string) ( $item['partner'] ?? '' );
								?>
									<div class="cert-h-card frame--rough-cut">
										
										<!-- Left Circle Emblem / Seal -->
										<div class="cert-h-card__seal-wrap">
											<div class="cert-h-card__seal">
												<?php if ( '' !== $c_partner ) : ?>
													<img src="<?php echo esc_url( UrlHelper::resolve( $c_partner ) ); ?>" alt="<?php echo esc_attr( $code ); ?>" class="cert-h-card__seal-img" loading="lazy">
												<?php else : ?>
													<span class="cert-h-card__seal-icon"><?php echo esc_html( $c_icon ); ?></span>
													<span class="cert-h-card__seal-code"><?php echo esc_html( $code ); ?></span>
												<?php endif; ?>
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
											<div class="cert-h-card__tag">
												<span class="cert-h-card__tag-star">★</span>
												<span>OFFICIALLY VERIFIED</span>
											</div>
										</div>

									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Right Side: Featured Master Certifying Authorities & Standards Box -->
			<div class="certs-showcase-right">
				<div class="certs-featured-logo-card frame--ornate">
					
					<!-- Top Wax Seal Stamp -->
					<div class="certs-featured-logo-card__seal">
						<span class="certs-seal-icon">🏆</span>
						<span class="certs-seal-text">VERIFIED</span>
					</div>

					<!-- Master Accreditation Certifying Seal -->
					<div class="certs-featured-logo-card__image-wrap">
						<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/partners/master-accreditation-seal.svg' ) ); ?>" alt="UK Food Standards Agency & ISO Accredited Quality Seal" class="certs-featured-logo-card__img" loading="lazy">
					</div>

					<h3 class="certs-featured-logo-card__title">
						OFFICIAL UK <em>Accreditations</em>
					</h3>

					<p class="certs-featured-logo-card__desc">
						Independently verified & certified by the <strong>UK Food Standards Agency</strong>, <strong>ISO 22000:2018</strong>, <strong>Soil Association Organic</strong>, and <strong>SALSA UK</strong> for sterile cold-press safety.
					</p>

					<!-- Quality Guarantee Checklist (3 Pillars) -->
					<ul class="certs-featured-logo-card__checklist">
						<li>
							<span class="chk-icon">✓</span>
							<span>FSA 5-Star Highest Rating</span>
						</li>
						<li>
							<span class="chk-icon">✓</span>
							<span>ISO 22000 Certified Cold-Chain</span>
						</li>
						<li>
							<span class="chk-icon">✓</span>
							<span>100% Soil Association Organic</span>
						</li>
					</ul>

					<div class="certs-featured-logo-card__badge-tag">
						<span>UK CERTIFIED ARTISAN JUICE</span>
					</div>

				</div>
			</div>

		</div>

		<!-- 3. Bottom Pagination / Carousel Dots (matches count of slides) -->
		<?php if ( count( $slides ) > 1 ) : ?>
			<div class="certs-carousel-dots" id="certs-carousel-dots" aria-label="Certifications navigation">
				<?php foreach ( $slides as $s_i => $s_data ) : ?>
					<button type="button" 
							class="certs-dot <?php echo 0 === $s_i ? 'is-active' : ''; ?>" 
							data-target-slide="<?php echo esc_attr( $s_i ); ?>"
							aria-label="<?php echo esc_attr( 'Page ' . ( $s_i + 1 ) ); ?>">
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>

<!-- Dots Interaction Script -->
<script>
(function() {
	function initCertsSlider() {
		var dots = document.querySelectorAll('#certs-carousel-dots .certs-dot');
		var slides = document.querySelectorAll('#certs-slider-container .certs-slide');
		if (!dots.length || !slides.length) return;

		dots.forEach(function(dot) {
			dot.addEventListener('click', function() {
				var targetIdx = parseInt(dot.getAttribute('data-target-slide') || '0', 10);
				
				dots.forEach(function(d) { d.classList.remove('is-active'); });
				dot.classList.add('is-active');

				slides.forEach(function(slide, idx) {
					if (idx === targetIdx) {
						slide.classList.add('is-active');
					} else {
						slide.classList.remove('is-active');
					}
				});
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCertsSlider);
	} else {
		initCertsSlider();
	}
})();
</script>
