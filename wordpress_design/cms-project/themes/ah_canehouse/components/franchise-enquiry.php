<?php
/**
 * Franchise enquiry form section.
 *
 * Args (all optional):
 *  tag         (string)  Eyebrow tag.    Default: 'Enquire Today'
 *  title       (string)  Heading HTML.   Default: 'Take the First <span ...>Step</span>'
 *  body        (string)  Intro text.     Default: preset copy
 *  form_title  (string)  Form card heading. Default: 'Franchise Enquiry 🌿'
 */
defined( 'ABSPATH' ) || exit;

$settings = ch_get_settings();
$phone    = $settings['phone'] ?? ( defined( 'CONTACT_NUMBER' ) ? CONTACT_NUMBER : '' );

$tag        = $args['tag']        ?? 'Enquire Today';
$title      = $args['title']      ?? 'Take the First <span class="accent" style="color:var(--ch-lime);">Step</span>';
$body       = $args['body']       ?? 'Franchise enquiries are handled personally by our founder. Expect a response within 24 hours. All enquiries treated with complete confidentiality.';
$form_title = $args['form_title'] ?? 'Franchise Enquiry 🌿';
$allowed    = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section id="franchise-enquiry" class="ch-franchise-enquiry-section">
	<div class="container">
		<div class="ch-quote-layout">
			<div class="fade-left" style="color:var(--ch-white);">
				<div class="section-tag" style="color:var(--ch-lime);"><?php echo esc_html( $tag ); ?></div>
				<h2 class="section-title" style="color:var(--ch-white);"><?php echo wp_kses( $title, $allowed ); ?></h2>
				<p class="section-body" style="color:rgba(255,255,255,0.7);"><?php echo esc_html( $body ); ?></p>
				<?php if ( $phone ) : ?>
					<div class="ch-contact-detail" style="margin-top:2rem;">
						<div class="ch-cd-icon">📞</div>
						<div>
							<div class="ch-cd-label">Direct Line</div>
							<div class="ch-cd-val">
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" style="color:white;">
									<?php echo esc_html( $phone ); ?>
								</a>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class="ch-contact-form fade-right">
				<div class="ch-form-title"><?php echo esc_html( $form_title ); ?></div>
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
