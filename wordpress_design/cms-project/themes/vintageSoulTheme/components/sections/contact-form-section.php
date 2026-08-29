<?php
/**
 * Contact Form Section (embeddable on Home page)
 * Renders a compact side-by-side contact form + quick info card.
 */

use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$phone = (string) ( $phone ?? '+44 7770 461 999' );
$email = (string) ( $email ?? 'thecanehouseuk@gmail.com' );
?>

<section class="section contact-home-section" id="connect">
	<div class="container">
		<h2 class="events-vintage__title">GET IN TOUCH</h2>
		<p class="events-vintage__sub" style="text-align:center; margin-bottom:24px;">Have a question, want to book an event, or interested in franchising? Drop us a message.</p>

		<div class="contact-home-grid">
			<!-- Left: Form -->
			<div class="contact-home-form-card">
				<form class="vintage-form" method="post" action="#">
					<div class="form-row form-row--duo">
						<div class="form-group">
							<label class="form-label" for="home-contact-name">YOUR NAME <span class="required">*</span></label>
							<input class="form-input" type="text" id="home-contact-name" name="name" placeholder="e.g. Ramesh Patel" required>
						</div>
						<div class="form-group">
							<label class="form-label" for="home-contact-phone">PHONE / WHATSAPP <span class="required">*</span></label>
							<input class="form-input" type="tel" id="home-contact-phone" name="phone" placeholder="+44 7770 000 000" required>
						</div>
					</div>

					<div class="form-row form-row--duo">
						<div class="form-group">
							<label class="form-label" for="home-contact-email">EMAIL <span class="required">*</span></label>
							<input class="form-input" type="email" id="home-contact-email" name="email" placeholder="you@example.com" required>
						</div>
						<div class="form-group">
							<label class="form-label" for="home-contact-type">ENQUIRY TYPE</label>
							<select class="form-select" id="home-contact-type" name="enquiry_type">
								<option value="event">Event / Live Cane Bar</option>
								<option value="franchise">Franchise Partnership</option>
								<option value="bulk">Wholesale / Bulk Order</option>
								<option value="general">General Enquiry</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label class="form-label" for="home-contact-message">YOUR MESSAGE <span class="required">*</span></label>
						<textarea class="form-textarea" id="home-contact-message" name="message" rows="3" placeholder="Tell us about your event, question, or enquiry..." required></textarea>
					</div>

					<div class="form-actions">
						<button class="btn btn--submit-inquiry" type="submit">
							<span class="btn__text">SEND YOUR MESSAGE</span>
							<span class="btn__arrow" aria-hidden="true">→</span>
						</button>
						<span class="form-note">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#caa06d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
							We never share your details. Zero spam.
						</span>
					</div>
				</form>
			</div>

			<!-- Right: Quick Contact Info -->
			<div class="contact-home-info-col">
				<div class="contact-home-info-card card--paper-cut">
					<div class="contact-info-card__header">
						<span class="contact-info-card__badge">DIRECT CONCIERGE</span>
						<h3 class="contact-info-card__title">QUICK CONTACT</h3>
					</div>
					<div class="contact-info-items">
						<div class="contact-info-item">
							<div class="contact-info-item__icon-box">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
							</div>
							<div class="contact-info-item__body">
								<span class="contact-info-item__label">CALL &amp; WHATSAPP</span>
								<a class="contact-info-item__val" href="tel:+447770461999"><?php echo esc_html( $phone ); ?></a>
							</div>
						</div>
						<div class="contact-info-item">
							<div class="contact-info-item__icon-box">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
							</div>
							<div class="contact-info-item__body">
								<span class="contact-info-item__label">EMAIL ENQUIRIES</span>
								<a class="contact-info-item__val" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
							</div>
						</div>
						<div class="contact-info-item">
							<div class="contact-info-item__icon-box">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
							</div>
							<div class="contact-info-item__body">
								<span class="contact-info-item__label">LOCATION</span>
								<span class="contact-info-item__text">Sutton, Greater London, UK</span>
							</div>
						</div>
						<div class="contact-info-item">
							<div class="contact-info-item__icon-box">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
							</div>
							<div class="contact-info-item__body">
								<span class="contact-info-item__label">OPENING HOURS</span>
								<span class="contact-info-item__text">Mon – Sun: 9:00 AM – 9:00 PM</span>
							</div>
						</div>
					</div>
				</div>

				<div class="contact-home-event-badge">
					<span class="contact-home-event-badge__icon">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 10 8-7 8 7-2 11H6L4 10Z"/><path d="M12 3v18"/><path d="M8 21v-7a4 4 0 0 1 8 0v7"/></svg>
					</span>
					<div>
						<strong>BOOK OUR LIVE CANE BAR</strong>
						<span>Weddings · Birthdays · Festivals · Corporate</span>
					</div>
				</div>

				<div class="contact-home-social-row">
					<a class="social-pill" href="https://facebook.com/thecanehouseuk" target="_blank" rel="noopener">Facebook</a>
					<a class="social-pill" href="https://instagram.com/thecanehouseuk" target="_blank" rel="noopener">Instagram</a>
					<a class="social-pill" href="https://wa.me/447770461999" target="_blank" rel="noopener">WhatsApp</a>
				</div>
			</div>
		</div>
	</div>
</section>
