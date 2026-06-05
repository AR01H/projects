<?php
defined( 'ABSPATH' ) || exit;

$products     = ch_get_delivery_products();
$_b           = CH_Order_Data::banner_settings();
$otd_tag      = $_b['tag'];
$otd_heading  = $_b['heading'];
$otd_sub      = $_b['sub'];
$otd_image    = $_b['image'];
$otd_features = CH_Order_Data::features();

$time_slots = [ 'Morning (8am–12pm)', 'Afternoon (12pm–5pm)', 'Evening (5pm–8pm)', 'Flexible','Now' ];
?>

<!-- ═══ ORDER-TO-DELIVER BANNER ════════════════════════════════════════════ -->
<section id="order-to-deliver" class="ch-frn-section ch-otd-section">
	<div class="container">
		<div class="ch-frn-card ch-otd-card fade-up">

			<!-- Left: image -->
			<div class="ch-frn-visual">
				<img src="<?php echo esc_url( $otd_image ); ?>" alt="Fresh sugarcane juice delivery" loading="lazy">
				<div class="ch-frn-visual-badge">Order &amp; Deliver 🌿</div>
			</div>

			<!-- Right: content -->
			<div class="ch-frn-content">
				<div class="section-tag" style="color:var(--client-color-7);"><?php echo esc_html( $otd_tag ); ?></div>
				<h2 class="ch-frn-title"><?php echo wp_kses( $otd_heading, [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ] ); ?></h2>
				<p class="ch-frn-sub"><?php echo esc_html( $otd_sub ); ?></p>

				<?php if ( ! empty( $otd_features ) ) : ?>
				<ul class="ch-frn-features">
					<?php foreach ( $otd_features as $feat ) :
						$feat = (array) $feat;
					?>
						<li><?php echo esc_html( $feat['icon'] ?? '' ); ?> <?php echo esc_html( $feat['text'] ?? '' ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<button type="button" class="ch-frn-open btn-lime ch-otd-open" id="ch-otd-open">
					🥤 Order Now
				</button>
			</div>

		</div>
	</div>
</section>

<!-- ═══ ORDER MODAL ════════════════════════════════════════════════════════ -->
<div class="ch-bk-modal" id="ch-otd-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Order to Deliver">
	<div class="ch-bk-modal-backdrop" data-otd-close></div>

	<div class="ch-bk-modal-box">
		<button type="button" class="ch-bk-modal-close" data-otd-close aria-label="Close">&times;</button>

		<div class="ch-bk-modal-scroll">

			<!-- Progress bar -->
			<div class="ch-bk-progress">
				<?php
				$step_labels = [ 'Select Items', 'Delivery Details', 'Your Details' ];
				foreach ( $step_labels as $i => $lbl ) :
				?>
					<div class="ch-bk-prog-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo $i + 1; ?>">
						<div class="ch-bk-prog-dot"><?php echo $i + 1; ?></div>
						<span class="ch-bk-prog-label"><?php echo esc_html( $lbl ); ?></span>
					</div>
				<?php endforeach; ?>
				<div class="ch-bk-prog-line"><span class="ch-bk-prog-fill"></span></div>
			</div>

			<form id="ch-otd-form" novalidate>
				<?php wp_nonce_field( 'ch_contact_nonce', 'ch_otd_nonce_field' ); ?>
				<div id="ch-otd-msg" class="ch-form-feedback" style="display:none;" role="alert"></div>

				<!-- ── STEP 1: Select items + quantities ──────────────────────── -->
				<div class="ch-bk-step active" data-step="1">
					<h3 class="ch-bk-step-title">What would you like? 🥤</h3>
					<p class="ch-bk-step-desc">Select items and set the quantity for each.</p>

					<div class="ch-otd-products">
						<?php foreach ( $products as $p ) :
							$p    = (array) $p;
							$name = $p['name'] ?? '';
							$slug = sanitize_key( $name );
						?>
						<label class="ch-otd-product-row" for="otd-item-<?php echo esc_attr( $slug ); ?>">
							<input type="checkbox" id="otd-item-<?php echo esc_attr( $slug ); ?>"
								name="otd_items[]" value="<?php echo esc_attr( $name ); ?>"
								class="ch-otd-product-chk">
							<span class="ch-otd-product-icon"><?php echo esc_html( $p['icon'] ?? '🌿' ); ?></span>
							<span class="ch-otd-product-info">
								<span class="ch-otd-product-name"><?php echo esc_html( $name ); ?></span>
								<span class="ch-otd-product-desc"><?php echo esc_html( $p['desc'] ?? '' ); ?></span>
							</span>
							<span class="ch-otd-product-size"><?php echo esc_html( $p['size'] ?? '' ); ?></span>
							<span class="ch-otd-qty-wrap">
								<button type="button" class="ch-otd-qty-btn ch-otd-qty-minus" aria-label="Decrease">−</button>
								<input type="number" name="otd_qty[<?php echo esc_attr( $name ); ?>]"
									class="ch-otd-qty-input" value="1" min="1" max="99"
									aria-label="Quantity for <?php echo esc_attr( $name ); ?>">
								<button type="button" class="ch-otd-qty-btn ch-otd-qty-plus" aria-label="Increase">+</button>
							</span>
						</label>
						<?php endforeach; ?>
					</div>


					<div class="ch-bk-nav">
						<span></span>
						<button type="button" class="ch-bk-next btn-lime" data-next="2">Next: Delivery →</button>
					</div>
				</div>

				<!-- ── STEP 2: Delivery details ───────────────────────────────── -->
				<div class="ch-bk-step" data-step="2">
					<h3 class="ch-bk-step-title">Delivery details 📦</h3>
					<p class="ch-bk-step-desc">Where and when should we deliver?</p>

					<div class="ch-bk-fields">
						<div class="ch-bk-field">
							<label>Delivery Address *</label>
							<textarea name="otd_address" class="ch-form-textarea" rows="3"
								placeholder="Full delivery address including flat/house number, street, postcode…" required></textarea>
						</div>
						<div class="ch-bk-field">
							<label>Area / City *</label>
							<input type="text" name="otd_area" class="ch-form-input"
								placeholder="e.g. Bengaluru, Koramangala" required>
						</div>
						<div class="ch-bk-field-row">
							<div class="ch-bk-field">
								<label>Preferred Date <small>(optional)</small></label>
								<input type="date" name="otd_date" class="ch-form-input">
							</div>
							<div class="ch-bk-field">
								<label>Preferred Time <small>(optional)</small></label>
								<select name="otd_time" class="ch-form-select">
									<option value="">Any time</option>
									<?php foreach ( $time_slots as $slot ) : ?>
										<option value="<?php echo esc_attr( $slot ); ?>"><?php echo esc_html( $slot ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>

					<div class="ch-bk-nav">
						<button type="button" class="ch-bk-back btn-outline" data-back="1">← Back</button>
						<button type="button" class="ch-bk-next btn-lime" data-next="3">Next: Your Details →</button>
					</div>
				</div>

				<!-- ── STEP 3: Contact + notes + summary + submit ─────────────── -->
				<div class="ch-bk-step" data-step="3">
					<h3 class="ch-bk-step-title">Almost done! 🌿</h3>
					<p class="ch-bk-step-desc">Review your order and leave your contact details.</p>

					<div class="ch-bk-summary" id="ch-otd-summary"></div>

					<div class="ch-bk-fields">
						<div class="ch-bk-field-row">
							<div class="ch-bk-field">
								<label>Your Name *</label>
								<input type="text" name="otd_name" class="ch-form-input" placeholder="Full name" required>
							</div>
							<div class="ch-bk-field">
								<label>Email *</label>
								<input type="email" name="otd_email" class="ch-form-input" placeholder="you@email.com" required>
							</div>
						</div>
						<div class="ch-bk-field">
							<label>Phone / WhatsApp <small>(optional)</small></label>
							<input type="tel" name="otd_phone" class="ch-form-input" placeholder="+91 …">
						</div>
						<div class="ch-bk-field">
							<label>Special Requirements <small>(optional)</small></label>
							<textarea name="otd_notes" class="ch-form-textarea" rows="3"
								placeholder="Allergies, packaging preferences, gate code, anything helpful…"></textarea>
						</div>
					</div>

					<div class="ch-bk-nav">
						<button type="button" class="ch-bk-back btn-outline" data-back="2">← Back</button>
						<button type="submit" class="btn-lime" id="ch-otd-submit">Submit Order Request 🌿</button>
					</div>
				</div>

			</form>

		</div><!-- .ch-bk-modal-scroll -->
	</div>
</div>
