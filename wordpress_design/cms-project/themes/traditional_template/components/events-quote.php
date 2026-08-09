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

$content    = App_Helpers::data( 'content' )['events_quote'] ?? [];
$tag        = $args['tag']          ?? $content['tag']        ?? '';
$title      = $args['title']        ?? $content['heading']    ?? '';
$body       = $args['body']         ?? $content['body']       ?? '';
$form_title = $args['form_title']   ?? $content['form_title'] ?? 'Tell Us About Your Event 🌿';
$enq_type   = $args['enquiry_type'] ?? 'event';

$event_types = $args['event_types'] ?? ['Wedding', 'Corporate', 'Birthday', 'Other'];
$allowed     = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section id="quote" class="app-events-quote-section">
	<div class="container">
		<div class="app-quote-layout">
			<div class="fade-left" style="color:var(--client-color-11);">
				<div class="section-tag" style="color:var(--client-color-7);"><?php echo esc_html( $tag ); ?></div>
				<h2 class="section-title" style="color:var(--client-color-11);"><?php echo wp_kses( $title, $allowed ); ?></h2>
				<p class="section-body" style="color:rgba(255,255,255,0.7);"><?php echo esc_html( $body ); ?></p>
				<?php if ( $phone ) : ?>
					<div class="app-contact-detail" style="margin-top:2rem;">
						<div class="app-cd-icon">📞</div>
						<div>
							<div class="app-cd-label">Call or WhatsApp</div>
							<div class="app-cd-val">
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" style="color:white;">
									<?php echo esc_html( $phone ); ?>
								</a>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class="app-contact-form fade-right">
				<div class="app-form-title"><?php echo esc_html( $form_title ); ?></div>
				<div id="app-form-msg" class="app-form-feedback" style="display:none;" role="alert"></div>
				<?php
				// Map $event_types array to options associative array
				$type_options = ['' => 'Select event type...'];
				foreach ( $event_types as $et ) {
					$type_options[$et] = $et;
				}
				
				get_template_part( 'components/parts/generic-form', null, [
					'id'     => 'app-contact-form',
					'action' => 'app_contact_submit',
					'submit' => 'Send Event Enquiry 🥤',
					'fields' => [
						[
							'type'     => 'hidden',
							'id'       => 'app-enquiry-type',
							'name'     => 'app_enquiry',
							'value'    => $enq_type,
						],
						[
							'type'     => 'text',
							'id'       => 'app_name',
							'name'     => 'app_name',
							'label'    => 'Your Name',
							'placeholder' => 'Full name',
							'required' => true,
						],
						[
							'type'     => 'email',
							'id'       => 'app_email',
							'name'     => 'app_email',
							'label'    => 'Email',
							'placeholder' => 'you@email.com',
							'required' => true,
						],
						[
							'type'     => 'tel',
							'id'       => 'app_phone',
							'name'     => 'app_phone',
							'label'    => 'Phone / WhatsApp',
							'placeholder' => '+44 ...',
							'required' => false,
						],
						[
							'type'     => 'select',
							'id'       => 'app_event_type',
							'name'     => 'app_event_type',
							'label'    => 'Event Type',
							'options'  => $type_options,
							'required' => false,
						],
						[
							'type'     => 'textarea',
							'id'       => 'app_message',
							'name'     => 'app_message',
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
