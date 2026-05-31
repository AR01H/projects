<?php
defined( 'ABSPATH' ) || exit;
$locations = ch_get_franchise_locations();
$showcase  = ch_get_juice_showcase();
$settings  = ch_get_settings();
$phone     = $settings['phone'] ?? CONTACT_NUMBER;
?>

<section id="franchise" class="ch-franchise-section">
	<div class="ch-franchise-inner fade-up">
		<div class="ch-section-tag" style="justify-content:center;color:var(--ch-green-deep);">Grow With Us</div>
		<h2 class="ch-section-title">Franchise <span class="accent" style="color:var(--ch-green-mid);">Opportunities</span></h2>
		<p class="ch-section-body">Be part of the fresh juice revolution. Bring the live-pressed cane experience to your city. Join our growing network of franchise partners across the UK.</p>

		<div class="ch-juice-showcase">
			<div class="ch-showcase-container" id="ch-showcase-track">
				<?php foreach ( $showcase as $idx => $card ) :
					$card   = (array) $card;
					$cls    = 'ch-showcase-card';
					if ( $idx === 0 ) $cls .= ' active';
					if ( $idx === 1 ) $cls .= ' next';
					if ( $idx === count( $showcase ) - 1 ) $cls .= ' prev';
				?>
					<div class="<?php echo esc_attr( $cls ); ?>" data-index="<?php echo $idx; ?>">
						<img src="<?php echo esc_url( $card['image'] ?? '' ); ?>"
							alt="<?php echo esc_attr( $card['title'] ?? '' ); ?>" loading="lazy">
						<div class="ch-showcase-info">
							<h3><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
							<p><?php echo esc_html( $card['desc'] ?? '' ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="ch-showcase-controls">
				<button class="ch-s-btn" id="ch-showcase-prev" aria-label="<?php esc_attr_e( 'Previous', 'ch-theme' ); ?>">←</button>
				<button class="ch-s-btn" id="ch-showcase-next" aria-label="<?php esc_attr_e( 'Next', 'ch-theme' ); ?>">→</button>
			</div>
		</div>

		<div class="ch-franchise-marquee" aria-hidden="true">
			<div class="ch-franchise-track">
				<?php foreach ( $locations as $loc ) :
					$loc = (array) $loc;
				?>
					<div class="ch-f-item">
						<span class="ch-f-icon"><?php echo esc_html( $loc['icon'] ?? '📍' ); ?></span>
						<span class="ch-f-name"><?php echo esc_html( $loc['name'] ?? '' ); ?></span>
					</div>
				<?php endforeach; ?>
				<?php foreach ( $locations as $loc ) :
					$loc = (array) $loc;
				?>
					<div class="ch-f-item">
						<span class="ch-f-icon"><?php echo esc_html( $loc['icon'] ?? '📍' ); ?></span>
						<span class="ch-f-name"><?php echo esc_html( $loc['name'] ?? '' ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="ch-franchise-contact">
			<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"
					class="ch-contact-pill">
					📞 <?php echo esc_html( $phone ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
