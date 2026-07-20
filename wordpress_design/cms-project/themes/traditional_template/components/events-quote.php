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

$settings = [];
$phone    = defined( 'CONTACT_NUMBER' ) ? CONTACT_NUMBER : '';

$content    = nt_data( 'content' )['events_quote'] ?? [];
$tag        = $args['tag']          ?? $content['tag']        ?? '';
$title      = $args['title']        ?? $content['heading']    ?? '';
$body       = $args['body']         ?? $content['body']       ?? '';
$form_title = $args['form_title']   ?? $content['form_title'] ?? 'Tell Us About Your Event 🌿';
$enq_type   = $args['enquiry_type'] ?? 'event';

$event_types = $args['event_types'] ?? ['Wedding', 'Corporate', 'Birthday', 'Other'];
$allowed     = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section id="quote" class="nt-events-quote-section">
	<div class="container">
		<div class="nt-quote-layout">
			<div class="fade-left" style="color:var(--client-color-11);">
				<div class="section-tag" style="color:var(--client-color-7);"><?php echo esc_html( $tag ); ?></div>
				<h2 class="section-title" style="color:var(--client-color-11);"><?php echo wp_kses( $title, $allowed ); ?></h2>
				<p class="section-body" style="color:rgba(255,255,255,0.7);"><?php echo esc_html( $body ); ?></p>
				<?php if ( $phone ) : ?>
					<div class="nt-contact-detail" style="margin-top:2rem;">
						<div class="nt-cd-icon">📞</div>
						<div>
							<div class="nt-cd-label">Call or WhatsApp</div>
							<div class="nt-cd-val">
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" style="color:white;">
									<?php echo esc_html( $phone ); ?>
								</a>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class="nt-contact-form fade-right">
				<div class="nt-form-title"><?php echo esc_html( $form_title ); ?></div>
				<div id="nt-form-msg" class="nt-form-feedback" style="display:none;" role="alert"></div>
				<?php
				// Map $event_types array to options associative array
				$type_options = ['' => 'Select event type...'];
				foreach ( $event_types as $et ) {
					$type_options[$et] = $et;
				}
				
				get_template_part( 'components/parts/generic-form', null, [
					'id'     => 'nt-contact-form',
					'action' => 'nt_contact_submit',
					'submit' => 'Send Event Enquiry 🥤',
					'fields' => [
						[
							'type'     => 'hidden',
							'id'       => 'nt-enquiry-type',
							'name'     => 'nt_enquiry',
							'value'    => $enq_type,
						],
						[
							'type'     => 'text',
							'id'       => 'nt_name',
							'name'     => 'nt_name',
							'label'    => 'Your Name',
							'placeholder' => 'Full name',
							'required' => true,
						],
						[
							'type'     => 'email',
							'id'       => 'nt_email',
							'name'     => 'nt_email',
							'label'    => 'Email',
							'placeholder' => 'you@email.com',
							'required' => true,
						],
						[
							'type'     => 'tel',
							'id'       => 'nt_phone',
							'name'     => 'nt_phone',
							'label'    => 'Phone / WhatsApp',
							'placeholder' => '+44 ...',
							'required' => false,
						],
						[
							'type'     => 'select',
							'id'       => 'nt_event_type',
							'name'     => 'nt_event_type',
							'label'    => 'Event Type',
							'options'  => $type_options,
							'required' => false,
						],
						[
							'type'     => 'textarea',
							'id'       => 'nt_message',
							'name'     => 'nt_message',
							'label'    => 'Message (date, location, guest count…)',
							'placeholder' => 'Tell us more - event date, venue, number of guests...',
							'required' => false,
						]
					]
				] );
				?>
			</div>
		</div>
	</div>
</section>
