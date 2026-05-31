<?php
/**
 * Template Name: Events & Hire
 */
defined( 'ABSPATH' ) || exit;
get_header();

$packages = ch_get_hire_packages();
$features = ch_get_hire_features();
$settings = ch_get_settings();
$phone    = $settings['phone'] ?? CONTACT_NUMBER;
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<section class="ch-page-hero ch-page-hero--events">
	<div class="container">
		<div class="fade-up" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
			<div class="section-tag">Live Juice Stall Hire</div>
			<h1 class="ch-page-hero__title">Events & <em>Hire</em></h1>
			<p class="ch-page-hero__desc">Bring The Cane House to your celebration. Live-pressed sugarcane juice - a unique, healthy, and unforgettable experience for your guests.</p>
			<div style="display:flex;gap:1rem;margin-top:2rem;flex-wrap:wrap;justify-content:center;">
				<a href="#quote" class="btn-lime">🌿 Get a Free Quote</a>
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" class="btn-outline ch-btn-outline-light">📞 Call Us</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<!-- ── Features bar ─────────────────────────────────────────────────────────── -->
<div class="ch-features-ribbon">
	<div class="container">
		<div class="ch-ribbon-grid">
			<?php foreach ( $features as $feat ) :
				$feat = (array) $feat;
			?>
				<div class="ch-ribbon-item">
					<span class="ch-ribbon-icon"><?php echo esc_html( $feat['icon'] ?? '✓' ); ?></span>
					<span class="ch-ribbon-text"><?php echo esc_html( $feat['text'] ?? '' ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<!-- ── Book Your Order wizard (banner + popup) ──────────────────────────────── -->
<?php get_template_part( 'components/booking-wizard' ); ?>

<!-- ── Event packages ────────────────────────────────────────────────────────── -->
<section style="background:var(--ch-white);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">Event Types</div>
			<h2 class="section-title">We Cater for <span class="accent">Every Occasion</span></h2>
			<p class="section-body">Whether it's 50 guests or 500, The Cane House brings the freshest live-press experience to your event.</p>
		</div>
		<div class="ch-packages-grid fade-up">
			<?php foreach ( $packages as $pkg ) :
				$pkg = (array) $pkg;
			?>
				<div class="ch-package-card">
					<div class="ch-package-card__icon"><?php echo esc_html( $pkg['icon'] ?? '🎉' ); ?></div>
					<h3 class="ch-package-card__title"><?php echo esc_html( $pkg['title'] ?? '' ); ?></h3>
					<p class="ch-package-card__desc"><?php echo esc_html( $pkg['desc'] ?? '' ); ?></p>
					<?php if ( ! empty( $pkg['items'] ) ) : ?>
						<ul class="ch-package-list">
							<?php foreach ( (array) $pkg['items'] as $item ) : ?>
								<li><?php echo esc_html( $item ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<a href="#quote" class="ch-package-card__cta">Enquire →</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ── Why choose us ─────────────────────────────────────────────────────────── -->
<section style="background:var(--ch-green-bg);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-why-grid">
			<div class="fade-left">
				<div class="section-tag">Why Choose Us</div>
				<h2 class="section-title">What Makes Us <span class="accent">Different</span></h2>
				<p class="section-body">We're not just another catering option - we're an experience your guests will be talking about for weeks.</p>
				<div class="ch-why-list">
					<div class="ch-why-item">
						<div class="ch-why-icon">👀</div>
						<div>
							<strong>Live Pressing in Front of Guests</strong>
							<p>Watching fresh cane being pressed is a spectacle in itself - creating a natural talking point and crowd magnet at your event.</p>
						</div>
					</div>
					<div class="ch-why-item">
						<div class="ch-why-icon">🌿</div>
						<div>
							<strong>100% Natural - No Compromise</strong>
							<p>Everything we serve is pure, natural, and fresh. No artificial syrups, no chemicals - just real sugarcane juice.</p>
						</div>
					</div>
					<div class="ch-why-item">
						<div class="ch-why-icon">📋</div>
						<div>
							<strong>Fully Insured & Certified</strong>
							<p>We carry full public liability insurance and comply with all food hygiene regulations - complete peace of mind for you.</p>
						</div>
					</div>
					<div class="ch-why-item">
						<div class="ch-why-icon">🤝</div>
						<div>
							<strong>Flexible & Responsive</strong>
							<p>We work around your schedule, venue, and guest count. Packages from 50 to 1,000+ guests across the UK.</p>
						</div>
					</div>
				</div>
			</div>
			<div class="ch-why-visual fade-right">
				<img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=600&h=700&q=80" alt="The Cane House at an event" loading="lazy" style="width:100%;height:100%;object-fit:cover;border-radius:20px;">
			</div>
		</div>
	</div>
</section>

<!-- ── Quote form ─────────────────────────────────────────────────────────────── -->
<section id="quote" style="background:var(--ch-green-deep);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-quote-layout">
			<div class="fade-left" style="color:var(--ch-white);">
				<div class="section-tag" style="color:var(--ch-lime);">Get in Touch</div>
				<h2 class="section-title" style="color:var(--ch-white);">Request a <span class="accent" style="color:var(--ch-lime);">Free Quote</span></h2>
				<p class="section-body" style="color:rgba(255,255,255,0.7);">Tell us about your event and we'll come back to you within 24 hours with a personalised package and price.</p>
				<?php if ( $phone ) : ?>
					<div class="ch-contact-detail" style="margin-top:2rem;">
						<div class="ch-cd-icon">📞</div>
						<div>
							<div class="ch-cd-label">Call or WhatsApp</div>
							<div class="ch-cd-val"><a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" style="color:white;"><?php echo esc_html( $phone ); ?></a></div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class="ch-contact-form fade-right">
				<div class="ch-form-title">Tell Us About Your Event 🌿</div>
				<div id="ch-form-msg" class="ch-form-feedback" style="display:none;" role="alert"></div>
				<form id="ch-contact-form" novalidate>
					<?php wp_nonce_field( 'ch_contact_nonce', 'ch_contact_nonce_field' ); ?>
					<input type="hidden" name="action" value="ch_contact_submit">
					<input type="hidden" name="ch_enquiry" value="event">
					<div class="ch-form-group">
						<label class="ch-form-label">Your Name</label>
						<input type="text" name="ch_name" class="ch-form-input" placeholder="Full name" required>
					</div>
					<div class="ch-form-group">
						<label class="ch-form-label">Email</label>
						<input type="email" name="ch_email" class="ch-form-input" placeholder="you@email.com" required>
					</div>
					<div class="ch-form-group">
						<label class="ch-form-label">Phone / WhatsApp</label>
						<input type="tel" name="ch_phone" class="ch-form-input" placeholder="+44 ...">
					</div>
					<div class="ch-form-group">
						<label class="ch-form-label">Event Type</label>
						<select name="ch_event_type" class="ch-form-select">
							<option value="">Select event type...</option>
							<option>Wedding / Walima</option>
							<option>Mehndi / Sangeet</option>
							<option>Eid Celebration</option>
							<option>Birthday Party</option>
							<option>Corporate Event</option>
							<option>Community Festival</option>
							<option>Other</option>
						</select>
					</div>
					<div class="ch-form-group">
						<label class="ch-form-label">Message (date, location, guest count…)</label>
						<textarea name="ch_message" class="ch-form-textarea" placeholder="Tell us more - event date, venue, number of guests..."></textarea>
					</div>
					<button type="submit" class="ch-form-submit">Send Event Enquiry 🥤</button>
				</form>
			</div>
		</div>
	</div>
</section>

</main>
<?php get_footer(); ?>
