<?php
defined( 'ABSPATH' ) || exit;
$s      = ch_get_settings();
$certs  = ch_get_certifications();
$cert_heading = $s['cert_heading'] ?? 'Council Registered & Fully Compliant';
$cert_sub     = $s['cert_subtext'] ?? 'Every event we attend comes with full documentation, insurance, and food safety compliance. We take the trust of our clients and their guests very seriously.';
$cert_img     = $s['cert_image_url'] ?? get_template_directory_uri() . '/assets/images/ncass_logo.png';
?>

<section id="certifications" class="ch-certs-section">
	<div class="container">

		<div class="ch-certs-header fade-up">
			<div class="section-tag">Official & Verified</div>
			<h2 class="section-title"><?php echo wp_kses( $cert_heading, [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h2>
			<p class="section-body"><?php echo esc_html( $cert_sub ); ?></p>
		</div>

		<div class="ch-certs-layout">

			<!-- Badges grid -->
			<div class="ch-certs-grid fade-up">
				<?php foreach ( $certs as $cert ) :
					$cert = (array) $cert;
					if ( empty( $cert['title'] ) ) continue;
				?>
					<div class="ch-cert-card">
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

			<!-- Optional certificate image or trust statement -->
			<?php if ( $cert_img ) : ?>
				<div class="ch-cert-visual fade-right">
					<img src="<?php echo esc_url( $cert_img ); ?>"
						alt="The Cane House Food Hygiene Certificate"
						class="ch-cert-img" loading="lazy">
				</div>
			<?php else : ?>
				<div class="ch-cert-trust fade-right">
					<div class="ch-cert-trust__inner">
						<div class="ch-cert-trust__icon">
							<img src="<?php echo esc_url( $cert_img ); ?>" alt="Certification Icon" />
						</div>
						<div class="ch-cert-trust__title">Proudly Certified</div>
						<p class="ch-cert-trust__text">
							The Cane House operates with full compliance across all food safety, health, and event regulations applicable in the United Kingdom.
						</p>
						<div class="ch-cert-trust__tags">
							<span>FSA Registered</span>
							<span>Insured</span>
							<span>HACCP</span>
							<span>Allergen Info</span>
						</div>
						<p class="ch-cert-trust__note">
							Documentation available for venues &amp; event organisers on request.
						</p>
					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
