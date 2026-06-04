<?php
defined( 'ABSPATH' ) || exit;
$s      = ch_get_settings();
$certs  = ch_get_certifications();
$cert_heading = $s['cert_heading'] ?? 'Food Safety Registered & Fully Compliant';
$cert_sub     = $s['cert_subtext'] ?? 'Every event we attend comes with full documentation, insurance, and Food Safety Registered compliance. We take the trust of our clients and their guests very seriously.';
$cert_img     = get_template_directory_uri() . '/assets/images/ncass_logo.png';
?>

<section id="certifications" class="ch-certs-section">
	<div class="container">

		<div class="ch-certs-header fade-up">
			<div class="section-tag">Official & Verified</div>
			<h2 class="section-title"><?php echo wp_kses( $cert_heading, [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h2>
			<p class="section-body"><?php echo esc_html( $cert_sub ); ?></p>
		</div>

		<div class="ch-certs-layout">

			<!-- ── Carousel ──────────────────────────────────────────────────── -->
			<div class="ch-certs-carousel" id="ch-certs-carousel">

				<div class="ch-certs-track" id="ch-certs-track">
					<?php foreach ( $certs as $i => $cert ) :
						$cert = (array) $cert;
						if ( empty( $cert['title'] ) ) continue;
					?>
						<div class="ch-cert-card<?php echo $i === 0 ? ' active' : ''; ?>">
							<div class="ch-cert-icon"><?php echo esc_html( $cert['icon'] ?? '✅' ); ?></div>
							<div class="ch-cert-body">
								<div class="ch-cert-title"><?php echo esc_html( $cert['title'] ); ?></div>
								<div class="ch-cert-desc"><?php echo esc_html( $cert['desc'] ?? '' ); ?></div>
							</div>
							<?php if ( ! empty( $cert['badge'] ) && $cert['badge'] !== "''" ) : ?>
								<span class="ch-cert-badge"><?php echo esc_html( $cert['badge'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Dots + arrows -->
				<div class="ch-certs-nav">
					<div class="ch-certs-dots" id="ch-certs-dots" role="tablist" aria-label="Certifications navigation">
						<?php foreach ( $certs as $i => $cert ) :
							$cert = (array) $cert;
							if ( empty( $cert['title'] ) ) continue;
						?>
							<button class="ch-dot<?php echo $i === 0 ? ' active' : ''; ?>"
								role="tab"
								aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
								aria-label="Certification <?php echo $i + 1; ?>"></button>
						<?php endforeach; ?>
					</div>
					<div class="ch-certs-arrows">
						<button class="ch-v-btn" id="ch-certs-prev" aria-label="Previous certification">←</button>
						<button class="ch-v-btn" id="ch-certs-next" aria-label="Next certification">→</button>
					</div>
				</div>

			</div><!-- .ch-certs-carousel -->

			<!-- Certificate image -->
			<?php if ( $cert_img ) : ?>
				<div class="ch-cert-visual fade-right">
					<img src="<?php echo esc_url( $cert_img ); ?>"
						alt="The Cane House Food Hygiene Certificate"
						class="ch-cert-img" loading="lazy">
						<span class="ch-cert-badge">We are officially member of</span>
				</div>
			<?php endif; ?>

		</div><!-- .ch-certs-layout -->
	</div>
</section>
