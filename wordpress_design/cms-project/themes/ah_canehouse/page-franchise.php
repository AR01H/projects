<?php
/**
 * Template Name: Franchise Opportunities
 */
defined( 'ABSPATH' ) || exit;
get_header();

$locations = ch_get_franchise_locations();
$showcase  = ch_get_juice_showcase();
$settings  = ch_get_settings();
$phone     = $settings['phone'] ?? CONTACT_NUMBER;
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<section class="ch-page-hero ch-page-hero--franchise">
	<div class="container">
		<div class="fade-up" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
			<div class="section-tag">Grow With Us</div>
			<h1 class="ch-page-hero__title">Franchise <em>Opportunities</em></h1>
			<p class="ch-page-hero__desc">Join the UK's fastest-growing natural juice movement. Bring live-pressed sugarcane juice to your city - we provide everything you need to succeed.</p>
			<a href="#franchise-enquiry" class="btn-lime" style="margin-top:2rem;">Start Your Enquiry →</a>
		</div>
	</div>
</section>

<!-- ── Why franchise ─────────────────────────────────────────────────────────── -->
<section style="background:var(--ch-green-bg);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">The Opportunity</div>
			<h2 class="section-title">Why <span class="accent">The Cane House?</span></h2>
			<p class="section-body">The demand for healthy, natural drinks is booming. The UK has almost no live-press sugarcane brand - yet. Be the first in your city.</p>
		</div>
		<div class="ch-franchise-why-grid fade-up">
			<div class="ch-fw-card">
				<div class="ch-fw-icon">📈</div>
				<h3>Growing Market</h3>
				<p>The natural drinks sector is growing 15%+ year-on-year. Live-press juice is untapped in most UK cities - massive first-mover advantage awaits.</p>
			</div>
			<div class="ch-fw-card">
				<div class="ch-fw-icon">🏗️</div>
				<h3>Full Setup Support</h3>
				<p>We provide the equipment, training, branding, marketing templates, and supplier contacts. You focus on serving customers - we handle the rest.</p>
			</div>
			<div class="ch-fw-card">
				<div class="ch-fw-icon">💰</div>
				<h3>Strong Margins</h3>
				<p>Low cost ingredients, high selling price. A single busy event can generate significant returns. Scalable from a single stall to multiple locations.</p>
			</div>
			<div class="ch-fw-card">
				<div class="ch-fw-icon">🤝</div>
				<h3>Ongoing Partnership</h3>
				<p>We're not just a licensor - we're your business partner. Regular check-ins, marketing support, and a growing network of fellow franchise owners.</p>
			</div>
			<div class="ch-fw-card">
				<div class="ch-fw-icon">🌿</div>
				<h3>Ethical & Sustainable</h3>
				<p>A product you can be genuinely proud of. Natural, sustainable, and culturally resonant with communities across the UK.</p>
			</div>
			<div class="ch-fw-card">
				<div class="ch-fw-icon">⚡</div>
				<h3>Quick to Launch</h3>
				<p>Minimal setup time compared to traditional food franchise. Be ready to trade at events within weeks of joining the family.</p>
			</div>
		</div>
	</div>
</section>

<!-- ── How it works ──────────────────────────────────────────────────────────── -->
<section style="background:var(--ch-white);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">Getting Started</div>
			<h2 class="section-title">How It <span class="accent">Works</span></h2>
		</div>
		<div class="ch-steps-grid ch-steps-grid--4 fade-up">
			<div class="ch-step-card">
				<div class="ch-step-num">1</div>
				<div class="ch-step-emoji">📞</div>
				<div class="ch-step-title">Enquire</div>
				<div class="ch-step-desc">Fill in the form below or call us. We'll schedule a call to discuss the opportunity in your city.</div>
			</div>
			<div class="ch-step-card">
				<div class="ch-step-num">2</div>
				<div class="ch-step-emoji">📋</div>
				<div class="ch-step-title">Discovery Call</div>
				<div class="ch-step-desc">We walk you through the model, margins, requirements, and answer all your questions honestly.</div>
			</div>
			<div class="ch-step-card">
				<div class="ch-step-num">3</div>
				<div class="ch-step-emoji">🖊️</div>
				<div class="ch-step-title">Agreement</div>
				<div class="ch-step-desc">Sign the franchise agreement, complete training, and receive your equipment and branding pack.</div>
			</div>
			<div class="ch-step-card" style="border-color:var(--ch-lime);">
				<div class="ch-step-num" style="background:linear-gradient(135deg,var(--ch-lime-dark),var(--ch-lime));color:var(--ch-green-deep);">4</div>
				<div class="ch-step-emoji">🎉</div>
				<div class="ch-step-title">Launch!</div>
				<div class="ch-step-desc">Start trading at events in your area with full The Cane House support behind you from day one.</div>
			</div>
		</div>
	</div>
</section>

<!-- ── Locations marquee ─────────────────────────────────────────────────────── -->
<?php if ( ! empty( $locations ) ) : ?>
<div style="background:var(--ch-lime);padding:1.2rem 0;overflow:hidden;">
	<div class="ch-franchise-track" style="display:flex;width:max-content;animation:ch-scroll-left 40s linear infinite;will-change:transform;">
		<?php foreach ( $locations as $loc ) :
			$loc = (array) $loc;
		?>
			<div style="padding:0 3rem;display:flex;align-items:center;gap:0.8rem;white-space:nowrap;">
				<span style="font-size:1.5rem;"><?php echo esc_html( $loc['icon'] ?? '📍' ); ?></span>
				<span style="font-family:var(--ch-font-display);font-size:1.4rem;font-weight:900;color:var(--ch-green-deep);opacity:0.85;"><?php echo esc_html( $loc['name'] ?? '' ); ?></span>
			</div>
		<?php endforeach; ?>
		<?php foreach ( $locations as $loc ) :
			$loc = (array) $loc;
		?>
			<div style="padding:0 3rem;display:flex;align-items:center;gap:0.8rem;white-space:nowrap;">
				<span style="font-size:1.5rem;"><?php echo esc_html( $loc['icon'] ?? '📍' ); ?></span>
				<span style="font-family:var(--ch-font-display);font-size:1.4rem;font-weight:900;color:var(--ch-green-deep);opacity:0.85;"><?php echo esc_html( $loc['name'] ?? '' ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>

<!-- ── Enquiry form ───────────────────────────────────────────────────────────── -->
<section id="franchise-enquiry" style="background:var(--ch-green-deep);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-quote-layout">
			<div class="fade-left" style="color:var(--ch-white);">
				<div class="section-tag" style="color:var(--ch-lime);">Enquire Today</div>
				<h2 class="section-title" style="color:var(--ch-white);">Take the First <span class="accent" style="color:var(--ch-lime);">Step</span></h2>
				<p class="section-body" style="color:rgba(255,255,255,0.7);">Franchise enquiries are handled personally by our founder. Expect a response within 24 hours. All enquiries treated with complete confidentiality.</p>
				<?php if ( $phone ) : ?>
					<div class="ch-contact-detail" style="margin-top:2rem;">
						<div class="ch-cd-icon">📞</div>
						<div>
							<div class="ch-cd-label">Direct Line</div>
							<div class="ch-cd-val"><a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" style="color:white;"><?php echo esc_html( $phone ); ?></a></div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class="ch-contact-form fade-right">
				<div class="ch-form-title">Franchise Enquiry 🌿</div>
				<div id="ch-form-msg" class="ch-form-feedback" style="display:none;" role="alert"></div>
				<form id="ch-contact-form" novalidate>
					<?php wp_nonce_field( 'ch_contact_nonce', 'ch_contact_nonce_field' ); ?>
					<input type="hidden" name="action" value="ch_contact_submit">
					<input type="hidden" name="ch_enquiry" value="franchise">
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
						<label class="ch-form-label">City / Area You're Interested In</label>
						<input type="text" name="ch_city" class="ch-form-input" placeholder="e.g. Manchester, Leeds, Glasgow...">
					</div>
					<div class="ch-form-group">
						<label class="ch-form-label">Tell Us About Yourself</label>
						<textarea name="ch_message" class="ch-form-textarea" placeholder="Your background, why you're interested, any questions..."></textarea>
					</div>
					<button type="submit" class="ch-form-submit">Submit Franchise Enquiry →</button>
				</form>
			</div>
		</div>
	</div>
</section>

</main>
<?php get_footer(); ?>
