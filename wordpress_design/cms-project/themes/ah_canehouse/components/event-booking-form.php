<?php
/**
 * Event Booking Form Component
 *
 * Renders a booking form for an event and fires Rules Engine trigger on valid submission.
 *
 * Args:
 *  event_id (int)     - The event ID (required)
 *  show_label (string) - Form label/title (default: auto-generated from event)
 *  button_text (string) - Submit button text (default: "Request Booking")
 *  success_message (string) - Message after successful submit (default: preset)
 */
defined( 'ABSPATH' ) || exit;

$event_id      = (int) ( $args['event_id'] ?? 0 );
$show_label    = $args['show_label'] ?? null;
$button_text   = $args['button_text'] ?? 'Request Booking';
$success_msg   = $args['success_message'] ?? 'Thanks! Your booking request has been received. We\'ll be in touch soon.';

if ( ! $event_id ) return;

// Load event details
if ( ! class_exists( 'AH_Events_Model' ) ) return;
$model = new AH_Events_Model();
$event = $model->find( $event_id );
if ( ! $event ) return;

$form_id       = 'event-booking-form-' . $event_id;
$notify_enabled = (int) ( $event->notify_on_booking ?? 0 );
$trigger_name  = $event->booking_trigger_name ?: 'booking_event_' . $event_id;

// Handle form submission
$success = false;
$errors  = array();
if ( isset( $_POST[ $form_id . '_nonce' ] ) ) {
	if ( ! wp_verify_nonce( $_POST[ $form_id . '_nonce' ], 'event_booking_' . $event_id ) ) {
		$errors[] = 'Security check failed.';
	} else {
		// Validate inputs
		$name    = sanitize_text_field( $_POST['booking_name'] ?? '' );
		$email   = sanitize_email( $_POST['booking_email'] ?? '' );
		$phone   = sanitize_text_field( $_POST['booking_phone'] ?? '' );
		$date    = sanitize_text_field( $_POST['booking_date'] ?? '' );
		$guests  = (int) ( $_POST['booking_guests'] ?? 0 );
		$message = wp_kses_post( $_POST['booking_message'] ?? '' );

		if ( ! $name ) {
			$errors[] = 'Name is required.';
		}
		if ( ! $email || ! is_email( $email ) ) {
			$errors[] = 'Valid email is required.';
		}
		if ( ! $phone ) {
			$errors[] = 'Phone number is required.';
		}
		if ( ! $date ) {
			$errors[] = 'Event date is required.';
		}
		if ( $guests < 1 ) {
			$errors[] = 'Number of guests must be at least 1.';
		}

		// If all validations pass, fire the trigger
		if ( empty( $errors ) ) {
			$success = true;

			// Fire Rules Engine trigger IF notifications are enabled for this event
			if ( $notify_enabled && class_exists( 'AH_Rules_Engine' ) ) {
				AH_Rules_Engine::evaluate( $trigger_name, array(
					'event_id'       => $event_id,
					'event_title'    => $event->title,
					'client_name'    => $name,
					'email'          => $email,
					'phone'          => $phone,
					'event_date'     => $date,
					'num_guests'     => $guests,
					'message'        => $message,
					'submitted_date' => current_time( 'Y-m-d H:i:s' ),
				) );
			}
		}
	}
}

// Default label if not provided
if ( ! $show_label ) {
	$show_label = 'Book ' . esc_html( $event->title ?? 'This Event' );
}
?>

<div class="ch-event-booking-form" id="<?php echo esc_attr( $form_id ); ?>">
	<?php if ( $success ) : ?>
		<div class="ch-booking-success" style="
			background:#dcfce7;border:1px solid #86efac;border-radius:8px;
			padding:16px;margin-bottom:20px;color:#166534;
		">
			<div style="font-size:1.3rem;margin-bottom:8px;">✅</div>
			<p style="margin:0;font-size:14px;"><?php echo esc_html( $success_msg ); ?></p>
		</div>
		<?php if ( $notify_enabled ) : ?>
			<p style="font-size:12px;color:#166534;margin:8px 0 0;">
				📧 A notification has been sent to our team.
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! $success ) : ?>
		<div style="margin-bottom:16px;">
			<h3 style="margin:0 0 4px;font-size:1.1rem;color:#1f2937;">
				<?php echo esc_html( $show_label ); ?>
			</h3>
			<p style="margin:0;font-size:13px;color:#6b7280;">
				Tell us about your event and we'll get back to you with availability and pricing.
				<?php if ( $notify_enabled ) : ?>
					<br><strong>📧 Notifications enabled</strong> — We'll send confirmation to our team immediately.
				<?php endif; ?>
			</p>
		</div>

		<?php if ( ! empty( $errors ) ) : ?>
			<div style="
				background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;
				padding:12px;margin-bottom:16px;color:#dc2626;font-size:13px;
			">
				<strong>Please fix the following:</strong>
				<ul style="margin:4px 0 0;padding-left:20px;">
					<?php foreach ( $errors as $err ) : ?>
						<li><?php echo esc_html( $err ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form method="post" style="display:grid;gap:12px;">
			<?php wp_nonce_field( 'event_booking_' . $event_id, $form_id . '_nonce' ); ?>

			<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
				<div class="ch-form-row">
					<label for="<?php echo esc_attr( $form_id ); ?>_name" style="
						display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#374151;
					">
						Your Name <span style="color:#dc2626;">*</span>
					</label>
					<input type="text" id="<?php echo esc_attr( $form_id ); ?>_name"
					       name="booking_name" value="<?php echo isset( $_POST['booking_name'] ) ? esc_attr( $_POST['booking_name'] ) : ''; ?>"
					       placeholder="John Doe" required style="
					       width:100%;padding:8px 10px;border:1.5px solid #d1d5db;border-radius:6px;
					       font-size:13px;box-sizing:border-box;
					       ">
				</div>
				<div class="ch-form-row">
					<label for="<?php echo esc_attr( $form_id ); ?>_email" style="
						display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#374151;
					">
						Email <span style="color:#dc2626;">*</span>
					</label>
					<input type="email" id="<?php echo esc_attr( $form_id ); ?>_email"
					       name="booking_email" value="<?php echo isset( $_POST['booking_email'] ) ? esc_attr( $_POST['booking_email'] ) : ''; ?>"
					       placeholder="john@example.com" required style="
					       width:100%;padding:8px 10px;border:1.5px solid #d1d5db;border-radius:6px;
					       font-size:13px;box-sizing:border-box;
					       ">
				</div>
			</div>

			<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
				<div class="ch-form-row">
					<label for="<?php echo esc_attr( $form_id ); ?>_phone" style="
						display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#374151;
					">
						Phone <span style="color:#dc2626;">*</span>
					</label>
					<input type="tel" id="<?php echo esc_attr( $form_id ); ?>_phone"
					       name="booking_phone" value="<?php echo isset( $_POST['booking_phone'] ) ? esc_attr( $_POST['booking_phone'] ) : ''; ?>"
					       placeholder="+44 1234 567890" required style="
					       width:100%;padding:8px 10px;border:1.5px solid #d1d5db;border-radius:6px;
					       font-size:13px;box-sizing:border-box;
					       ">
				</div>
				<div class="ch-form-row">
					<label for="<?php echo esc_attr( $form_id ); ?>_date" style="
						display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#374151;
					">
						Event Date <span style="color:#dc2626;">*</span>
					</label>
					<input type="date" id="<?php echo esc_attr( $form_id ); ?>_date"
					       name="booking_date" value="<?php echo isset( $_POST['booking_date'] ) ? esc_attr( $_POST['booking_date'] ) : ''; ?>"
					       required style="
					       width:100%;padding:8px 10px;border:1.5px solid #d1d5db;border-radius:6px;
					       font-size:13px;box-sizing:border-box;
					       ">
				</div>
			</div>

			<div class="ch-form-row">
				<label for="<?php echo esc_attr( $form_id ); ?>_guests" style="
					display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#374151;
				">
					Number of Guests <span style="color:#dc2626;">*</span>
				</label>
				<input type="number" id="<?php echo esc_attr( $form_id ); ?>_guests"
				       name="booking_guests" value="<?php echo isset( $_POST['booking_guests'] ) ? (int) $_POST['booking_guests'] : ''; ?>"
				       placeholder="25" min="1" required style="
				       width:100%;max-width:200px;padding:8px 10px;border:1.5px solid #d1d5db;border-radius:6px;
				       font-size:13px;box-sizing:border-box;
				       ">
			</div>

			<div class="ch-form-row">
				<label for="<?php echo esc_attr( $form_id ); ?>_message" style="
					display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#374151;
				">
					Special Requests <small style="font-weight:400;color:#6b7280;">(optional)</small>
				</label>
				<textarea id="<?php echo esc_attr( $form_id ); ?>_message"
				          name="booking_message" rows="4" placeholder="Any special requirements or notes…" style="
				          width:100%;padding:8px 10px;border:1.5px solid #d1d5db;border-radius:6px;
				          font-size:13px;box-sizing:border-box;font-family:inherit;
				          "><?php echo isset( $_POST['booking_message'] ) ? esc_textarea( $_POST['booking_message'] ) : ''; ?></textarea>
			</div>

			<button type="submit" class="btn-lime" style="
				margin-top:8px;display:inline-flex;align-items:center;gap:8px;
				padding:10px 20px;background:#84cc16;color:#fff;border:none;border-radius:6px;
				font-weight:600;cursor:pointer;font-size:14px;
			">
				<span>→</span> <?php echo esc_html( $button_text ); ?>
			</button>
		</form>
	<?php endif; ?>
</div>
