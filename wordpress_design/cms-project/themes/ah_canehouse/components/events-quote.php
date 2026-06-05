<?php
/**
 * Event enquiry / quote form section.
 *
 * Args (all optional):
 *  tag          (string)  Eyebrow tag.           Default: 'Get in Touch'
 *  title        (string)  Heading HTML.          Default: 'Request a <span ...>Free Quote</span>'
 *  body         (string)  Intro paragraph.       Default: preset copy
 *  form_title   (string)  Form card heading.     Default: 'Tell Us About Your Event 🌿'
 *  enquiry_type (string)  Hidden enquiry value.  Default: 'event'
 *  event_types  (array)   Options for event type select. Default: preset list
 */
defined( 'ABSPATH' ) || exit;

$settings = ch_get_settings();
$phone    = $settings['phone'] ?? ( defined( 'CONTACT_NUMBER' ) ? CONTACT_NUMBER : '' );

$_d         = CH_Hire_Data::events_quote_settings();
$tag        = $args['tag']          ?? $_d['tag']        ?? '';
$title      = $args['title']        ?? $_d['title']      ?? '';
$body       = $args['body']         ?? $_d['body']       ?? '';
$form_title = $args['form_title']   ?? $_d['form_title'] ?? '';
$enq_type   = $args['enquiry_type'] ?? 'event';

$event_types = $args['event_types'] ?? $_d['event_types'] ?? [];
$allowed     = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section id="quote" class="ch-events-quote-section">
	<div class="container">
		<div class="ch-quote-layout">
			<div class="fade-left" style="color:var(--client-color-11);">
				<div class="section-tag" style="color:var(--client-color-7);"><?php echo esc_html( $tag ); ?></div>
				<h2 class="section-title" style="color:var(--client-color-11);"><?php echo wp_kses( $title, $allowed ); ?></h2>
				<p class="section-body" style="color:rgba(255,255,255,0.7);"><?php echo esc_html( $body ); ?></p>
				<?php if ( $phone ) : ?>
					<div class="ch-contact-detail" style="margin-top:2rem;">
						<div class="ch-cd-icon">📞</div>
						<div>
							<div class="ch-cd-label">Call or WhatsApp</div>
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
					<input type="hidden" name="ch_enquiry" value="<?php echo esc_attr( $enq_type ); ?>">
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
							<?php foreach ( $event_types as $et ) : ?>
								<option><?php echo esc_html( $et ); ?></option>
							<?php endforeach; ?>
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
