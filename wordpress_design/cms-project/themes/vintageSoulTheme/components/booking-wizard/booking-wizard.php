<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\View;

$id = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'booking-wizard';

$cane_options = isset( $cane_options ) && is_array( $cane_options ) ? $cane_options : array();
$cane_options = array_values(
	array_filter(
		array_map(
			static function ( $opt ) {
				$opt = (array) $opt;
				return array(
					'value' => trim( (string) ( $opt['value'] ?? '' ) ),
					'label' => trim( (string) ( $opt['label'] ?? '' ) ),
					'desc'  => (string) ( $opt['desc'] ?? '' ),
					'image' => (string) ( $opt['image'] ?? '' ),
				);
			},
			$cane_options
		),
		static function ( $opt ) {
			return '' !== $opt['value'] && '' !== $opt['label'];
		}
	)
);

$flavour_options = isset( $flavour_options ) && is_array( $flavour_options ) ? $flavour_options : array();
$flavour_options = array_values(
	array_filter(
		array_map(
			static function ( $opt ) {
				$opt = (array) $opt;
				return array(
					'value' => trim( (string) ( $opt['value'] ?? '' ) ),
					'label' => trim( (string) ( $opt['label'] ?? '' ) ),
					'icon'  => (string) ( $opt['icon'] ?? '' ),
				);
			},
			$flavour_options
		),
		static function ( $opt ) {
			return '' !== $opt['value'] && '' !== $opt['label'];
		}
	)
);

$event_types = isset( $event_types ) && is_array( $event_types ) && ! empty( $event_types )
	? array_values( array_filter( array_map( 'strval', $event_types ) ) )
	: array( 'Wedding', 'Private Party', 'Corporate Event', 'Festival', 'Other' );

if ( empty( $cane_options ) || empty( $flavour_options ) ) {
	return;
}

$steps = array( 'Cane', 'Flavours', 'Details', 'Confirm' );
?>
<div class="dialog booking-wizard" id="<?php echo esc_attr( $id ); ?>" data-vs-dialog hidden>
	<div class="dialog__backdrop" data-vs-dialog-close></div>
	<div class="dialog__panel booking-wizard__panel" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
		<button type="button" class="dialog__close booking-wizard__close" data-vs-dialog-close aria-label="<?php esc_attr_e( 'Close', 'vintagesoul' ); ?>">&times;</button>
		<h2 class="u-visually-hidden" id="<?php echo esc_attr( $id ); ?>-title"><?php esc_html_e( 'Book Your Event', 'vintagesoul' ); ?></h2>

		<ol class="booking-wizard__steps" data-wizard-indicator>
			<?php foreach ( $steps as $i => $step_label ) : ?>
				<li class="booking-wizard__step<?php echo ( 0 === $i ) ? ' is-active' : ''; ?>">
					<span class="booking-wizard__step-circle"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
					<span class="booking-wizard__step-label"><?php echo esc_html( $step_label ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>

		<form class="booking-wizard__form" data-vs-booking-wizard novalidate>

			<div class="booking-wizard__panel-step is-active" data-wizard-panel="0">
				<p class="booking-wizard__step-eyebrow"><?php esc_html_e( 'Step 1', 'vintagesoul' ); ?></p>
				<h3 class="booking-wizard__title"><?php esc_html_e( 'Choose Your Cane', 'vintagesoul' ); ?></h3>
				<p class="booking-wizard__subtitle"><?php esc_html_e( 'Pick your preferred sugarcane type.', 'vintagesoul' ); ?></p>
				<div class="booking-wizard__options booking-wizard__options--cane">
					<?php foreach ( $cane_options as $i => $opt ) : ?>
						<label class="booking-wizard__option">
							<input type="radio" name="cane_type" value="<?php echo esc_attr( $opt['value'] ); ?>"<?php checked( 0 === $i ); ?>>
							<span class="booking-wizard__option-card">
								<span class="booking-wizard__option-check" aria-hidden="true"></span>
								<?php if ( '' !== $opt['image'] ) : ?>
									<img class="booking-wizard__option-image" src="<?php echo esc_url( $opt['image'] ); ?>" alt="">
								<?php endif; ?>
								<span class="booking-wizard__option-name"><?php echo esc_html( $opt['label'] ); ?></span>
								<?php if ( '' !== $opt['desc'] ) : ?>
									<span class="booking-wizard__option-desc"><?php echo esc_html( $opt['desc'] ); ?></span>
								<?php endif; ?>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
				<div class="booking-wizard__actions">
					<span></span>
					<button type="button" class="btn" data-wizard-next><?php esc_html_e( 'Next: Flavours', 'vintagesoul' ); ?> &rarr;</button>
				</div>
			</div>

			<div class="booking-wizard__panel-step" data-wizard-panel="1" inert>
				<p class="booking-wizard__step-eyebrow"><?php esc_html_e( 'Step 2', 'vintagesoul' ); ?></p>
				<h3 class="booking-wizard__title"><?php esc_html_e( 'Choose Your Flavours', 'vintagesoul' ); ?></h3>
				<p class="booking-wizard__subtitle"><?php esc_html_e( 'Pick as many as you like - mix and match for your event.', 'vintagesoul' ); ?></p>
				<div class="booking-wizard__options booking-wizard__options--flavour">
					<?php foreach ( $flavour_options as $opt ) : ?>
						<label class="booking-wizard__option">
							<input type="checkbox" name="flavours[]" value="<?php echo esc_attr( $opt['value'] ); ?>">
							<span class="booking-wizard__option-card booking-wizard__option-card--sm">
								<span class="booking-wizard__option-check" aria-hidden="true"></span>
								<?php if ( '' !== $opt['icon'] ) : ?>
									<span class="booking-wizard__option-icon" aria-hidden="true"><?php echo esc_html( $opt['icon'] ); ?></span>
								<?php endif; ?>
								<span class="booking-wizard__option-name"><?php echo esc_html( $opt['label'] ); ?></span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
				<div class="booking-wizard__actions">
					<button type="button" class="btn btn--outline" data-wizard-back>&larr; <?php esc_html_e( 'Back', 'vintagesoul' ); ?></button>
					<button type="button" class="btn" data-wizard-next><?php esc_html_e( 'Next: Details', 'vintagesoul' ); ?> &rarr;</button>
				</div>
			</div>

			<div class="booking-wizard__panel-step" data-wizard-panel="2" inert>
				<p class="booking-wizard__step-eyebrow"><?php esc_html_e( 'Step 3', 'vintagesoul' ); ?></p>
				<h3 class="booking-wizard__title"><?php esc_html_e( 'Event Details', 'vintagesoul' ); ?></h3>
				<p class="booking-wizard__subtitle"><?php esc_html_e( 'Tell us about your event.', 'vintagesoul' ); ?></p>
				<div class="form-group">
					<label class="form-label" for="<?php echo esc_attr( $id ); ?>-event-type"><?php esc_html_e( 'Event Type', 'vintagesoul' ); ?></label>
					<select class="form-select" id="<?php echo esc_attr( $id ); ?>-event-type" name="event_type">
						<option value=""><?php esc_html_e( 'Select event type', 'vintagesoul' ); ?></option>
						<?php foreach ( $event_types as $type ) : ?>
							<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<label class="form-label" for="<?php echo esc_attr( $id ); ?>-event-date"><?php esc_html_e( 'Event Date', 'vintagesoul' ); ?></label>
					<input class="form-input" type="date" id="<?php echo esc_attr( $id ); ?>-event-date" name="event_date">
				</div>
				<div class="form-group">
					<label class="form-label" for="<?php echo esc_attr( $id ); ?>-location"><?php esc_html_e( 'Location', 'vintagesoul' ); ?></label>
					<input class="form-input" type="text" id="<?php echo esc_attr( $id ); ?>-location" name="location" placeholder="<?php esc_attr_e( 'Enter location', 'vintagesoul' ); ?>">
				</div>
				<div class="form-group">
					<label class="form-label" for="<?php echo esc_attr( $id ); ?>-guests"><?php esc_html_e( 'Guest Count', 'vintagesoul' ); ?></label>
					<input class="form-input" type="number" min="1" id="<?php echo esc_attr( $id ); ?>-guests" name="guest_count" placeholder="<?php esc_attr_e( 'Approx. number of guests', 'vintagesoul' ); ?>">
				</div>
				<div class="form-group">
					<label class="form-label" for="<?php echo esc_attr( $id ); ?>-notes"><?php esc_html_e( 'Special Requests', 'vintagesoul' ); ?></label>
					<textarea class="form-textarea" id="<?php echo esc_attr( $id ); ?>-notes" name="notes" placeholder="<?php esc_attr_e( 'Any additional requests...', 'vintagesoul' ); ?>"></textarea>
				</div>
				<div class="booking-wizard__actions">
					<button type="button" class="btn btn--outline" data-wizard-back>&larr; <?php esc_html_e( 'Back', 'vintagesoul' ); ?></button>
					<button type="button" class="btn" data-wizard-next><?php esc_html_e( 'Next: Confirm', 'vintagesoul' ); ?> &rarr;</button>
				</div>
			</div>

			<div class="booking-wizard__panel-step" data-wizard-panel="3" inert>
				<p class="booking-wizard__step-eyebrow"><?php esc_html_e( 'Step 4', 'vintagesoul' ); ?></p>
				<h3 class="booking-wizard__title"><?php esc_html_e( 'Confirm Booking', 'vintagesoul' ); ?></h3>
				<p class="booking-wizard__subtitle"><?php esc_html_e( 'Review your details and confirm.', 'vintagesoul' ); ?></p>
				<div class="booking-wizard__summary">
					<h4 class="booking-wizard__summary-heading"><?php esc_html_e( 'Your Order Summary', 'vintagesoul' ); ?></h4>
					<dl class="booking-wizard__summary-list" data-wizard-summary></dl>
					<div class="booking-wizard__summary-stamp">
						<?php
						View::component(
							'stamp/stamp',
							array(
								'id'     => $id . '-stamp',
								'center' => 'Freshly Pressed',
								'bottom' => 'Just For You',
								'size'   => 100,
							)
						);
						?>
					</div>
				</div>
				<p class="booking-wizard__note"><?php esc_html_e( "We'll contact you shortly to confirm everything!", 'vintagesoul' ); ?></p>
				<div class="booking-wizard__actions">
					<button type="button" class="btn btn--outline" data-wizard-back>&larr; <?php esc_html_e( 'Back', 'vintagesoul' ); ?></button>
					<button type="submit" class="btn" data-wizard-submit><?php esc_html_e( 'Confirm Booking', 'vintagesoul' ); ?> &#10003;</button>
				</div>
			</div>

		</form>
	</div>
</div>
