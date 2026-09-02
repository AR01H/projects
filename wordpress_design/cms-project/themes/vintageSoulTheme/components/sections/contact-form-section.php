<?php
/**
 * Contact Form Section (embeddable on Home page)
 * Renders a compact side-by-side contact form + quick info card with distinct Phone and WhatsApp items.
 */

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$contact_data = (array) ( JsonFileProvider::read( 'data/content/contact-page.json' ) ?? array() );
$c_info       = (array) ( $contact_data['contact_info'] ?? array() );

$phone        = (string) ( $phone ?? ( $c_info['phone'] ?? SettingsService::phone() ) );
$whatsapp     = (string) ( $whatsapp ?? ( $c_info['whatsapp'] ?? SettingsService::whatsapp() ) );
$whatsapp_url = (string) ( $whatsapp_url ?? ( $c_info['whatsapp_url'] ?? SettingsService::whatsapp_url() ) );
$email        = (string) ( $email ?? ( $c_info['email'] ?? SettingsService::email() ) );
$address      = (string) ( $address ?? ( $c_info['address'] ?? SettingsService::address() ) );
$hours        = (string) ( $hours ?? ( $c_info['hours'] ?? SettingsService::opening_hours() ) );
?>

<section class="section contact-home-section" id="connect">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'    => (string) ( $contact_data['hero']['tag'] ?? 'Let\'s Connect' ),
				'title'  => (string) ( $contact_data['hero']['title'] ?? 'GET IN <em>Touch</em>' ),
				'sub'    => (string) ( $contact_data['hero']['sub'] ?? 'Have questions, want to book us for an event, or looking to franchise? We\'d love to hear from you.' ),
				'ribbon' => true,
			)
		);
		?>

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
							<label class="form-label" for="home-contact-phone">PHONE NUMBER <span class="required">*</span></label>
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
								<?php foreach ( (array) ( $contact_data['enquiry_types'] ?? array() ) as $eq ) : ?>
									<option value="<?php echo esc_attr( (string) ( $eq['value'] ?? 'general' ) ); ?>"><?php echo esc_html( (string) ( $eq['label'] ?? '' ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label class="form-label" for="home-contact-message">YOUR MESSAGE <span class="required">*</span></label>
						<textarea class="form-textarea" id="home-contact-message" name="message" rows="3" placeholder="Tell us about your event, question, or enquiry..." required></textarea>
					</div>

					<div class="form-group form-group--agree">
						<label class="form-checkbox-label" for="home-contact-agree">
							<input type="checkbox" class="form-checkbox" id="home-contact-agree" name="agree" required checked>
							<span class="form-checkbox-text">
								I agree to the <a href="<?php echo esc_url( home_url( '/privacy' ) ); ?>" target="_blank" rel="noopener">Privacy Policy</a> &amp; consent to The Cane House contacting me regarding my enquiry.
							</span>
						</label>
					</div>

					<div class="form-actions">
						<button class="btn btn--submit-inquiry" type="submit">
							<span class="btn__text">SEND YOUR MESSAGE</span>
							<span class="btn__arrow" aria-hidden="true">→</span>
						</button>
						<div class="form-note">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#caa06d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
							<span>We never share your details. Zero spam.</span>
						</div>
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
						<?php
						$clean_phone = preg_replace( '/[^\d+]/', '', $phone );
						$clean_wa    = preg_replace( '/[^\d+]/', '', $whatsapp );
						$is_same_num = ( '' !== $clean_phone && $clean_phone === $clean_wa );
						?>
						<?php if ( $is_same_num ) : ?>
							<div class="contact-info-item">
								<div class="contact-info-item__icon-box">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
								</div>
								<div class="contact-info-item__body">
									<span class="contact-info-item__label">PHONE &amp; WHATSAPP</span>
									<a class="contact-info-item__val" href="tel:<?php echo esc_attr( $clean_phone ); ?>"><?php echo esc_html( $phone ); ?></a>
								</div>
							</div>
						<?php else : ?>
							<?php if ( '' !== $phone ) : ?>
								<div class="contact-info-item">
									<div class="contact-info-item__icon-box">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
									</div>
									<div class="contact-info-item__body">
										<span class="contact-info-item__label">DIRECT PHONE</span>
										<a class="contact-info-item__val" href="tel:<?php echo esc_attr( $clean_phone ); ?>"><?php echo esc_html( $phone ); ?></a>
									</div>
								</div>
							<?php endif; ?>

							<?php if ( '' !== $whatsapp ) : ?>
								<div class="contact-info-item">
									<div class="contact-info-item__icon-box">
										<?php echo IconHelper::render( 'whatsapp', '#f6d599', 20 ); // phpcs:ignore ?>
									</div>
									<div class="contact-info-item__body">
										<span class="contact-info-item__label">WHATSAPP CHAT</span>
										<a class="contact-info-item__val" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $whatsapp ); ?></a>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( '' !== $email ) : ?>
							<div class="contact-info-item">
								<div class="contact-info-item__icon-box">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
								</div>
								<div class="contact-info-item__body">
									<span class="contact-info-item__label">EMAIL ENQUIRIES</span>
									<a class="contact-info-item__val" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( '' !== $address ) : ?>
							<div class="contact-info-item">
								<div class="contact-info-item__icon-box">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
								</div>
								<div class="contact-info-item__body">
									<span class="contact-info-item__label">LOCATION</span>
									<span class="contact-info-item__text"><?php echo esc_html( $address ); ?></span>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( '' !== $hours ) : ?>
							<div class="contact-info-item">
								<div class="contact-info-item__icon-box">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
								</div>
								<div class="contact-info-item__body">
									<span class="contact-info-item__label">OPENING HOURS</span>
									<span class="contact-info-item__text"><?php echo esc_html( $hours ); ?></span>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="contact-home-event-badge">
					<span class="contact-home-event-badge__icon">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 10 8-7 8 7-2 11H6L4 10Z"/><path d="M12 3v18"/><path d="M8 21v-7a4 4 0 0 1 8 0v7"/></svg>
					</span>
					<div>
						<strong><?php echo esc_html( (string) ( $contact_data['event_bar']['title'] ?? 'BOOK OUR LIVE CANE BAR' ) ); ?></strong>
						<span><?php echo esc_html( (string) ( $contact_data['event_bar']['sub'] ?? 'Weddings · Birthdays · Festivals · Corporate' ) ); ?></span>
					</div>
				</div>

				<div class="contact-home-social-row">
					<a class="social-pill" href="<?php echo esc_url( SettingsService::social_url( 'facebook', 'https://facebook.com/thecanehouseuk' ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
						<span>Facebook</span>
					</a>
					<a class="social-pill" href="<?php echo esc_url( SettingsService::social_url( 'instagram', 'https://instagram.com/thecanehouseuk' ) ); ?>" target="_blank" rel="noopener" aria-label="Instagram">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
						<span>Instagram</span>
					</a>
					<a class="social-pill" href="<?php echo esc_url( SettingsService::whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
						<?php echo IconHelper::render( 'whatsapp', 'currentColor', 14 ); // phpcs:ignore ?>
						<span>WhatsApp</span>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
