<?php
defined( 'ABSPATH' ) || exit;

$sizes       = ch_get_menu_sizes();
$cane_types  = ch_get_cane_types();
$flavours    = ch_get_flavours();
$show_prices = function_exists( 'ch_show_prices' ) ? ch_show_prices() : false;
$s           = ch_get_settings();

$wiz_heading = $s['booking_heading'] ?? 'Book Your <span class="accent">Order</span>';
$wiz_sub     = $s['booking_sub']     ?? 'Build your perfect fresh cane juice order in a few easy steps. Pressed live, just for you.';

// occasion options
$occasions = [ 'Wedding / Walima', 'Mehndi / Sangeet', 'Eid Celebration', 'Birthday Party', 'Corporate Event', 'Community Festival', 'Other' ];
?>

<section id="booking" class="ch-bk-section">
	<div class="container">

		<div class="ch-bk-header fade-up">
			<div class="section-tag">Easy Booking</div>
			<h2 class="section-title"><?php echo wp_kses( $wiz_heading, [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h2>
			<p class="section-body"><?php echo esc_html( $wiz_sub ); ?></p>
		</div>

		<div class="ch-bk-wizard fade-up">

			<!-- ── Progress bar ───────────────────────────────────────────────── -->
			<div class="ch-bk-progress">
				<?php
				$step_labels = [ '🥤 Size', '🌾 Cane', '🍋 Flavour', '📅 Details', '✅ Confirm' ];
				foreach ( $step_labels as $i => $lbl ) :
				?>
					<div class="ch-bk-prog-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo $i + 1; ?>">
						<div class="ch-bk-prog-dot"><?php echo $i + 1; ?></div>
						<span class="ch-bk-prog-label"><?php echo esc_html( $lbl ); ?></span>
					</div>
				<?php endforeach; ?>
				<div class="ch-bk-prog-line"><span class="ch-bk-prog-fill"></span></div>
			</div>

			<form id="ch-booking-form" novalidate>
				<?php wp_nonce_field( 'ch_contact_nonce', 'ch_booking_nonce_field' ); ?>

				<!-- feedback -->
				<div id="ch-bk-msg" class="ch-form-feedback" style="display:none;" role="alert"></div>

				<!-- ── STEP 1: Size ─────────────────────────────────────────────── -->
				<div class="ch-bk-step active" data-step="1">
					<h3 class="ch-bk-step-title">Choose your size</h3>
					<p class="ch-bk-step-desc">How much fresh cane goodness do you want?</p>
					<div class="ch-bk-options">
						<?php foreach ( $sizes as $i => $sz ) :
							$sz = (array) $sz;
						?>
							<label class="ch-bk-option">
								<input type="radio" name="bk_size" value="<?php echo esc_attr( $sz['name'] ?? '' ); ?>"
									<?php echo $i === 0 ? 'checked' : ''; ?> required>
								<span class="ch-bk-option-card">
									<span class="ch-bk-option-icon"><?php echo esc_html( $sz['icon'] ?? '🥤' ); ?></span>
									<span class="ch-bk-option-name"><?php echo esc_html( $sz['name'] ?? '' ); ?></span>
									<span class="ch-bk-option-desc"><?php echo esc_html( $sz['desc'] ?? '' ); ?></span>
									<?php if ( $show_prices && ! empty( $sz['price'] ) ) : ?>
										<span class="ch-bk-option-price"><?php echo esc_html( $sz['price'] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $sz['badge'] ) ) : ?>
										<span class="ch-bk-option-badge"><?php echo esc_html( $sz['badge'] ); ?></span>
									<?php endif; ?>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="ch-bk-nav">
						<span></span>
						<button type="button" class="ch-bk-next btn-lime" data-next="2">Next: Cane Type →</button>
					</div>
				</div>

				<!-- ── STEP 2: Cane Type ────────────────────────────────────────── -->
				<div class="ch-bk-step" data-step="2">
					<h3 class="ch-bk-step-title">Pick your cane</h3>
					<p class="ch-bk-step-desc">Each cane has its own natural character.</p>
					<div class="ch-bk-options">
						<?php foreach ( $cane_types as $i => $c ) :
							$c = (array) $c;
						?>
							<label class="ch-bk-option">
								<input type="radio" name="bk_cane" value="<?php echo esc_attr( $c['name'] ?? '' ); ?>"
									<?php echo $i === 0 ? 'checked' : ''; ?> required>
								<span class="ch-bk-option-card">
									<span class="ch-bk-option-icon"><?php echo esc_html( $c['icon'] ?? '🌾' ); ?></span>
									<span class="ch-bk-option-name"><?php echo esc_html( $c['name'] ?? '' ); ?></span>
									<span class="ch-bk-option-desc"><?php echo esc_html( $c['desc'] ?? '' ); ?></span>
									<?php if ( ! empty( $c['badge'] ) ) : ?>
										<span class="ch-bk-option-badge"><?php echo esc_html( $c['badge'] ); ?></span>
									<?php endif; ?>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="ch-bk-nav">
						<button type="button" class="ch-bk-back btn-outline" data-back="1">← Back</button>
						<button type="button" class="ch-bk-next btn-lime" data-next="3">Next: Flavour →</button>
					</div>
				</div>

				<!-- ── STEP 3: Flavour (multi-select) ───────────────────────────── -->
				<div class="ch-bk-step" data-step="3">
					<h3 class="ch-bk-step-title">Choose your flavours</h3>
					<p class="ch-bk-step-desc">Pick as many as you like — mix and match for your event! 🌿</p>
					<div class="ch-bk-options ch-bk-options--chips">
						<?php foreach ( $flavours as $i => $fl ) :
							$fl = (array) $fl;
						?>
							<label class="ch-bk-option ch-bk-option--chip">
								<input type="checkbox" name="bk_flavour[]" value="<?php echo esc_attr( $fl['name'] ?? '' ); ?>"
									<?php echo $i === 0 ? 'checked' : ''; ?>>
								<span class="ch-bk-chip-card">
									<span class="ch-bk-chip-check">✓</span>
									<span class="ch-bk-chip-emoji"><?php echo esc_html( $fl['emoji'] ?? '🌿' ); ?></span>
									<span class="ch-bk-chip-name"><?php echo esc_html( $fl['name'] ?? '' ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="ch-bk-nav">
						<button type="button" class="ch-bk-back btn-outline" data-back="2">← Back</button>
						<button type="button" class="ch-bk-next btn-lime" data-next="4">Next: Event Details →</button>
					</div>
				</div>

				<!-- ── STEP 4: Event + Personal Details ─────────────────────────── -->
				<div class="ch-bk-step" data-step="4">
					<h3 class="ch-bk-step-title">Tell us about your event</h3>
					<p class="ch-bk-step-desc">So we can give you the perfect quote.</p>

					<div class="ch-bk-fields">
						<div class="ch-bk-field">
							<label>Occasion</label>
							<select name="bk_occasion" class="ch-form-select">
								<option value="">Select occasion…</option>
								<?php foreach ( $occasions as $occ ) : ?>
									<option value="<?php echo esc_attr( $occ ); ?>"><?php echo esc_html( $occ ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="ch-bk-field-row">
							<div class="ch-bk-field">
								<label>Event Date</label>
								<input type="date" name="bk_date" class="ch-form-input">
							</div>
							<div class="ch-bk-field">
								<label>Approx. Guests</label>
								<input type="number" name="bk_guests" class="ch-form-input" min="1" placeholder="e.g. 150">
							</div>
						</div>
						<div class="ch-bk-field">
							<label>Location / Venue</label>
							<input type="text" name="bk_location" class="ch-form-input" placeholder="City or venue name">
						</div>
						<div class="ch-bk-field">
							<label>Anything else? <small>(optional)</small></label>
							<textarea name="bk_notes" class="ch-form-textarea" rows="3" placeholder="Special requests, timings, questions…"></textarea>
						</div>
					</div>

					<div class="ch-bk-nav">
						<button type="button" class="ch-bk-back btn-outline" data-back="3">← Back</button>
						<button type="button" class="ch-bk-next btn-lime" data-next="5">Next: Confirm →</button>
					</div>
				</div>

				<!-- ── STEP 5: Confirm + Contact ───────────────────────────────── -->
				<div class="ch-bk-step" data-step="5">
					<h3 class="ch-bk-step-title">Almost done! 🌿</h3>
					<p class="ch-bk-step-desc">Review your order and leave your contact details.</p>

					<!-- Order summary (built by JS) -->
					<div class="ch-bk-summary" id="ch-bk-summary"></div>

					<div class="ch-bk-fields">
						<div class="ch-bk-field-row">
							<div class="ch-bk-field">
								<label>Your Name *</label>
								<input type="text" name="bk_name" class="ch-form-input" placeholder="Full name" required>
							</div>
							<div class="ch-bk-field">
								<label>Email *</label>
								<input type="email" name="bk_email" class="ch-form-input" placeholder="you@email.com" required>
							</div>
						</div>
						<div class="ch-bk-field">
							<label>Phone / WhatsApp</label>
							<input type="tel" name="bk_phone" class="ch-form-input" placeholder="+44 …">
						</div>
					</div>

					<div class="ch-bk-nav">
						<button type="button" class="ch-bk-back btn-outline" data-back="4">← Back</button>
						<button type="submit" class="ch-bk-submit btn-lime" id="ch-bk-submit">Send My Order Request 🥤</button>
					</div>
				</div>

			</form>
		</div>
	</div>
</section>
