<?php
defined( 'ABSPATH' ) || exit;
$packages = ch_get_hire_packages();
$features = ch_get_hire_features();
$settings = ch_get_settings();
?>

<section id="hire" class="ch-hire-section">
	<div class="ch-hire-container">

		<div class="ch-hire__header fade-up">
			<div class="ch-section-tag">Live Juice Stall Hire</div>
			<h2 class="ch-section-title">Bring Us to <span class="accent">Your Event</span></h2>
			<p class="ch-section-body">Elevate your celebration with our premium live-pressed sugarcane juice experience - perfect for weddings, Mehndi nights, Eid parties, Diwali celebrations, and corporate gatherings.</p>
		</div>

		<!-- ── Carousel wrapper ───────────────────────────────────────────────── -->
		<div class="ch-hire-carousel ch-carousel" id="ch-hire-carousel" style="--cc-items-visible: 3;">
			<div class="ch-carousel__viewport">
				<div class="ch-hire-track ch-carousel__track" id="ch-hire-track">
					<?php foreach ( $packages as $i => $pkg ) :
						$pkg = (array) $pkg;
					?>
						<div class="ch-hire-card ch-carousel__item fade-up">
							<div class="ch-h-card-icon" aria-hidden="true"><?php echo esc_html( $pkg['icon'] ?? '🎉' ); ?></div>
							<h3><?php echo esc_html( $pkg['title'] ?? '' ); ?></h3>
							<p><?php echo esc_html( $pkg['desc'] ?? '' ); ?></p>
							<?php if ( ! empty( $pkg['items'] ) ) : ?>
								<ul class="ch-h-card-list">
									<?php foreach ( (array) $pkg['items'] as $item ) : ?>
										<li><?php echo esc_html( $item ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Dots + arrows -->
			<div class="ch-hire-nav ch-carousel__nav">
				<div class="ch-hire-dots ch-carousel__dots" id="ch-hire-dots" role="tablist" aria-label="Event packages navigation">
					<?php foreach ( $packages as $i => $_ ) : ?>
						<button class="ch-dot ch-carousel__dot"
							role="tab"
							aria-label="Package <?php echo $i + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="ch-hire-arrows ch-carousel__arrows">
					<button class="ch-v-btn ch-carousel__arrow" data-dir="prev" aria-label="Previous package">←</button>
					<button class="ch-v-btn ch-carousel__arrow" data-dir="next" aria-label="Next package">→</button>
				</div>
			</div>

		</div><!-- .ch-hire-carousel -->

		<!-- Features ribbon -->
		<!-- <div class="ch-hire-features-bar fade-up">
			<?php foreach ( $features as $feat ) :
				$feat = (array) $feat;
			?>
				<div class="ch-h-feature">
					<span class="ch-hf-icon" aria-hidden="true"><?php echo esc_html( $feat['icon'] ?? '✓' ); ?></span>
					<div class="ch-hf-text"><?php echo esc_html( $feat['text'] ?? '' ); ?></div>
				</div>
			<?php endforeach; ?>
		</div> -->

		<!-- <div class="ch-hire-cta fade-up">
			<a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="btn-outline">
				View & Hire →
			</a>
		</div> -->

	</div>
</section>
