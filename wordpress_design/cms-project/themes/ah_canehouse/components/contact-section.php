<?php
defined( 'ABSPATH' ) || exit;
$settings      = ch_get_settings();
$contact       = ch_get_contact_settings();
$phone         = $settings['phone']   ?? CONTACT_NUMBER;
$email         = $settings['email']   ?? CONTACT_EMAIL;
$website       = $settings['website'] ?? 'www.thecanehouse.co.uk';
$nonce         = wp_create_nonce( 'ch_contact_nonce' );

$contact_details = [
    'address'             => [ 'icon' => '📍', 'label' => 'Address' ],
    'business_hours'      => [ 'icon' => '🕐', 'label' => 'Business Hours' ],
    'response_time'       => [ 'icon' => '⚡', 'label' => 'Response Time' ],
    'events_info_text'    => [ 'icon' => '🎪', 'label' => 'Events &amp; Hire' ],
    'franchise_info_text' => [ 'icon' => '🤝', 'label' => 'Franchise' ],
];


?>

<section id="contact" class="ch-contact-section">
	<div class="ch-contact-info fade-left">
		<div class="ch-section-tag">Say Hello</div>
		<h2 class="ch-section-title">Get in <span class="accent">Touch</span></h2>
		<p class="ch-section-body" style="margin-top:.8rem;margin-bottom:2rem;">Questions about our juices, booking us for your event, or interested in franchise opportunities? We'd love to hear from you.</p>

		<?php if ( $phone ) : ?>
			<div class="ch-contact-detail">
				<div class="ch-cd-icon" aria-hidden="true">📞</div>
				<div>
					<div class="ch-cd-label">Call Us</div>
					<div class="ch-cd-val">
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>">
							<?php echo esc_html( $phone ); ?>
						</a>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $email ) : ?>
			<div class="ch-contact-detail">
				<div class="ch-cd-icon" aria-hidden="true">📧</div>
				<div>
					<div class="ch-cd-label">Email Us</div>
					<div class="ch-cd-val">
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</div>
				</div>
			</div>
		<?php endif;

		foreach ( $contact_details as $key => $detail ) :
			if ( empty( $settings[ $key ] ) ) continue;
		?>
			<div class="ch-contact-detail">
				<div class="ch-cd-icon" aria-hidden="true"><?php echo $detail['icon']; ?></div>
				<div>
					<div class="ch-cd-label"><?php echo $detail['label']; ?></div>
					<div class="ch-cd-val"><?php echo esc_html( $settings[ $key ] ); ?></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="ch-contact-form fade-right">
		<div class="ch-form-title">Send Us a Message 🌿</div>

		<div id="ch-form-msg" class="ch-form-feedback" style="display:none;" role="alert"></div>

		<form id="ch-contact-form" novalidate>
			<?php wp_nonce_field( 'ch_contact_nonce', 'ch_contact_nonce_field' ); ?>
			<input type="hidden" name="action" value="ch_contact_submit">

			<div class="ch-form-group">
				<label class="ch-form-label" for="ch-name">Full Name</label>
				<input type="text" id="ch-name" name="ch_name" class="ch-form-input" placeholder="Your name" required>
			</div>

			<div class="ch-form-group">
				<label class="ch-form-label" for="ch-email">Email Address</label>
				<input type="email" id="ch-email" name="ch_email" class="ch-form-input" placeholder="you@email.com" required>
			</div>

			<div class="ch-form-group">
				<label class="ch-form-label" for="ch-phone">Phone Number</label>
				<input type="tel" id="ch-phone" name="ch_phone" class="ch-form-input" placeholder="+44 ..." required>
			</div>

			<div class="ch-form-group">
				<label class="ch-form-label" for="ch-enquiry">I'm enquiring about</label>
				<select id="ch-enquiry" name="ch_enquiry" class="ch-form-select">
					<option value="">Select enquiry type...</option>
					<?php foreach ( ch_get_enquiry_types() as $et ) : ?>
						<option value="<?php echo esc_attr( $et['value'] ); ?>"><?php echo esc_html( $et['label'] ); ?></option>
					<?php endforeach; ?>

				</select>
			</div>

			<div class="ch-form-group">
				<label class="ch-form-label" for="ch-message">Message</label>
				<textarea id="ch-message" name="ch_message" class="ch-form-textarea"
					placeholder="Tell us more - event date, location, expected guests..." required></textarea>
			</div>
			

			<!-- ── Disclaimer checkbox ───────────────────────────────────────── -->
			<div class="ch-form-group ch-disclaimer-group">
				<label class="ch-disclaimer-label" for="ch-consent">
					<input type="checkbox" id="ch-consent" name="ch_consent" class="ch-disclaimer-check" required>
					<span class="ch-disclaimer-box" aria-hidden="true"></span>
					<span class="ch-disclaimer-text">
						I agree to The Cane House
						<button type="button" class="ch-pp-trigger" id="ch-pp-trigger" aria-haspopup="dialog"><strong>Privacy Policy</strong></button>
						and consent to being contacted regarding my enquiry.
						We will never share your details with third parties.
					</span>
				</label>
				<span class="ch-field-error ch-consent-error" style="display:none;">
					Please tick the box to confirm you agree before sending.
				</span>
			</div>

			<!-- Modal is rendered at body level via components/privacy-policy-modal.php (footer) -->

			<button type="submit" class="ch-form-submit" id="ch-form-submit">
				Send Message 🥤
			</button>
		</form>
	</div>
</section>
