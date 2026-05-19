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

		<div class="ch-hire-grid">
			<?php foreach ( $packages as $pkg ) :
				$pkg = (array) $pkg;
			?>
				<div class="ch-hire-card fade-up">
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

		<div class="ch-hire-features-bar fade-up">
			<?php foreach ( $features as $feat ) :
				$feat = (array) $feat;
			?>
				<div class="ch-h-feature">
					<span class="ch-hf-icon" aria-hidden="true"><?php echo esc_html( $feat['icon'] ?? '✓' ); ?></span>
					<div class="ch-hf-text"><?php echo esc_html( $feat['text'] ?? '' ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="ch-hire-cta fade-up">
			<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-lime">
				Get a Custom Quote →
			</a>
		</div>
	</div>
</section>
