<?php
defined( 'ABSPATH' ) || exit;
get_header();

$settings = ch_get_settings();
$phone    = $settings['phone'] ?? CONTACT_NUMBER;
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ──────────────────────────────────────────────────────────────────── -->
<section class="services-hero">
	<div class="container">
		<div class="services-hero-inner fade-up">
			<div class="section-tag">What We Offer</div>
			<h1 class="section-title">Our <span class="accent">Services</span></h1>
			<p class="section-body">
				From fresh juice orders to full live-press event experiences - we bring the authentic taste of sugarcane to every occasion.
			</p>
		</div>
	</div>
</section>

<!-- ── Services Grid ─────────────────────────────────────────────────────────── -->
<section class="services-list">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">Everything We Do</div>
			<h2 class="section-title">Fresh Juice. <span class="accent">Every Way.</span></h2>
			<p class="section-body" style="margin-inline:auto;">Discover how The Cane House can serve you - whether it's a single cup or a full event setup.</p>
		</div>
		<div class="services-grid">
			<?php
			$services = ch_get_services();
			if ( ! empty( $services ) ) :
				foreach ( $services as $svc ) :
					$svc   = (array) $svc;
					$img   = esc_url( $svc['image_url'] ?? '' );
					$title = esc_html( $svc['title'] ?? '' );
					$desc  = wp_kses_post( $svc['description'] ?? '' );
					$icon  = wp_kses_post( $svc['icon'] ?? '' );
					$det   = wp_kses_post( $svc['details'] ?? '' );
			?>
				<div class="service-card fade-up">
					<?php if ( $img ) : ?>
						<div class="service-image"><img src="<?php echo $img; ?>" alt="<?php echo $title; ?>" loading="lazy"></div>
					<?php endif; ?>
					<div class="service-content">
						<?php if ( $icon ) : ?><div class="service-icon"><?php echo $icon; ?></div><?php endif; ?>
						<h3><?php echo $title; ?></h3>
						<p><?php echo $desc; ?></p>
						<?php if ( $det ) : ?><div class="service-details"><?php echo $det; ?></div><?php endif; ?>
					</div>
				</div>
			<?php
				endforeach;
			else :
				// Default services if DB is empty
				$default_svcs = [
					[ 'icon' => '🥤', 'title' => 'Fresh Juice Orders',        'desc' => 'Walk-up or pre-order your perfect sugarcane blend. Choose your size, cane type, texture, and flavour.' ],
					[ 'icon' => '💒', 'title' => 'Wedding &amp; Events Hire', 'desc' => 'Live-pressed juice stall for your wedding reception, Mehndi, Sangeet, or post-ceremony celebration.' ],
					[ 'icon' => '🏢', 'title' => 'Corporate Events',          'desc' => 'A refreshing, healthy alternative for office wellness days, product launches, and exhibitions.' ],
					[ 'icon' => '🎉', 'title' => 'Private Parties',           'desc' => 'Birthdays, garden parties, community festivals - we bring the live-press experience to you.' ],
					[ 'icon' => '🤝', 'title' => 'Franchise Opportunities',   'desc' => 'Join our growing network. Bring live-pressed sugarcane juice to your city with full support.' ],
					[ 'icon' => '📦', 'title' => 'Bulk &amp; Catering',       'desc' => 'Large-volume juice supply for festivals, markets, and corporate catering needs across the UK.' ],
				];
				foreach ( $default_svcs as $s ) : ?>
					<div class="service-card fade-up">
						<div class="service-content">
							<div class="service-icon"><?php echo $s['icon']; ?></div>
							<h3><?php echo $s['title']; ?></h3>
							<p><?php echo $s['desc']; ?></p>
						</div>
					</div>
				<?php endforeach;
			endif; ?>
		</div>
	</div>
</section>

<!-- ── How We Work ────────────────────────────────────────────────────────────── -->
<section style="background:var(--ch-white);padding:5rem 2rem;">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">The Process</div>
			<h2 class="section-title">Simple to <span class="accent">Book</span></h2>
			<p class="section-body" style="margin-inline:auto;">Getting The Cane House to your event is easy - just a few steps and we take care of the rest.</p>
		</div>
		<div class="ch-steps-grid ch-steps-grid--4" style="max-width:900px;margin-inline:auto;">
			<div class="ch-step-card fade-up">
				<div class="ch-step-num">1</div>
				<div class="ch-step-emoji">📞</div>
				<div class="ch-step-title">Contact Us</div>
				<div class="ch-step-desc">Reach out by phone, WhatsApp, or email with your event details.</div>
			</div>
			<div class="ch-step-card fade-up">
				<div class="ch-step-num">2</div>
				<div class="ch-step-emoji">💬</div>
				<div class="ch-step-title">We Quote</div>
				<div class="ch-step-desc">We'll tailor a package to your event size, location, and requirements.</div>
			</div>
			<div class="ch-step-card fade-up">
				<div class="ch-step-num">3</div>
				<div class="ch-step-emoji">📅</div>
				<div class="ch-step-title">Book a Date</div>
				<div class="ch-step-desc">Confirm your booking with a simple deposit to secure the date.</div>
			</div>
			<div class="ch-step-card fade-up" style="border-color:var(--ch-lime);">
				<div class="ch-step-num" style="background:linear-gradient(135deg,var(--ch-lime-dark),var(--ch-lime));color:var(--ch-green-deep);">4</div>
				<div class="ch-step-emoji">🎉</div>
				<div class="ch-step-title">We Arrive!</div>
				<div class="ch-step-desc">We set up, press live, and serve your guests the freshest cane juice they've ever tasted.</div>
			</div>
		</div>
	</div>
</section>

<!-- ── CTA ───────────────────────────────────────────────────────────────────── -->
<section class="services-cta">
	<div class="container">
		<div class="cta-box fade-up">
			<h2>Ready to Experience Fresh?</h2>
			<p>Whether you're ordering for yourself or planning a major event, we're here to serve the freshest juice possible.</p>
			<div class="cta-buttons">
				<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-lime">🌿 Get a Quote</a>
				<a href="<?php echo esc_url( home_url( '/#build' ) ); ?>" class="btn-outline">🥤 Build Your Juice</a>
			</div>
			<?php if ( $phone ) : ?>
				<p style="margin-top:1.5rem;font-size:0.85rem;color:rgba(255,255,255,0.6);">Or call us directly:
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" style="color:var(--ch-lime);font-weight:700;"><?php echo esc_html( $phone ); ?></a>
				</p>
			<?php endif; ?>
		</div>
	</div>
</section>

</main>

<?php get_footer(); ?>
