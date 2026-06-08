<?php
defined( 'ABSPATH' ) || exit;

$s           = ch_get_settings();
$_d          = CH_About_Data::franchise_enquiry_settings();
$frn_heading = $s['franchise_wiz_heading'] ?? $_d['heading'] ?? '';
$frn_sub     = $s['franchise_wiz_sub']     ?? $_d['sub']     ?? '';
$frn_image   = $s['franchise_wiz_image']   ?? 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80';

/* ── Banner that opens the modal ─────────────────────────────────────────────── */
get_template_part( 'components/forms/form_cta_banner', null, [
	'section_id'     => 'franchise-enquiry',
	'section_class'  => 'ch-frn-section',
	'card_class'     => 'ch-frn-card fade-up',
	'image_side'     => 'left',
	'content_class'  => 'ch-frn-content',
	'visual_class'   => 'ch-frn-visual',
	'badge_class'    => 'ch-frn-visual-badge',
	'badge'          => 'Be Your Own Boss 🌿',
	'tag'            => 'Franchise Opportunity',
	'title_class'    => 'ch-frn-title',
	'heading'        => $frn_heading,
	'sub_class'      => 'ch-frn-sub',
	'sub'            => $frn_sub,
	'features_class' => 'ch-frn-features',
	'features'       => $_d['features'] ?? [],
	'image'          => $frn_image,
	'image_alt'      => 'Franchise opportunity',
	'button_id'      => 'ch-frn-open',
	'button_class'   => 'ch-frn-open btn-lime',
	'button_label'   => '🌿 Enquire Now',
] );

/* ── Step markup (buffered, passed to the reusable modal shell) ──────────────── */
ob_start();
?>
	<!-- ── STEP 1: Location interest ─────────────────────────────────── -->
	<div class="ch-bk-step active" data-step="1">
		<h3 class="ch-bk-step-title">Where do you want to open?</h3>
		<p class="ch-bk-step-desc">Tell us your preferred city or area and what type of unit interests you.</p>

		<div class="ch-bk-fields">
			<div class="ch-bk-field">
				<label>City / Area *</label>
				<input type="text" name="frn_city" class="ch-form-input" placeholder="e.g. Manchester, Leeds, Birmingham…" required>
			</div>
			<div class="ch-bk-field">
				<label>Franchise Type *</label>
				<select name="frn_type" class="ch-form-select" required>
					<option value="">Select a type…</option>
					<?php foreach ( $_d['unit_types'] ?? [] as $ut ) : ?>
						<option><?php echo esc_html( $ut ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="ch-bk-field">
				<label>When are you looking to start? *</label>
				<select name="frn_timeline" class="ch-form-select" required>
					<option value="">Select a timeline…</option>
					<?php foreach ( $_d['timelines'] ?? [] as $tl ) : ?>
						<option><?php echo esc_html( $tl ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="ch-bk-nav">
			<span></span>
			<button type="button" class="ch-bk-next btn-lime" data-next="2">Next: Your Background →</button>
		</div>
	</div>

	<!-- ── STEP 2: Background + Investment ───────────────────────────── -->
	<div class="ch-bk-step" data-step="2">
		<h3 class="ch-bk-step-title">Tell us about yourself</h3>
		<p class="ch-bk-step-desc">Helps us match you to the right franchise package.</p>

		<div class="ch-bk-fields">
			<div class="ch-bk-field">
				<label>Investment Range *</label>
				<select name="frn_investment" class="ch-form-select" required>
					<option value="">Select a range…</option>
					<?php foreach ( $_d['investment_ranges'] ?? [] as $ir ) : ?>
						<option><?php echo esc_html( $ir ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="ch-bk-field">
				<label>Business / Food Experience <small>(optional)</small></label>
				<select name="frn_experience" class="ch-form-select">
					<option value="">Select…</option>
					<option>No prior business experience</option>
					<option>I've run a small business before</option>
					<option>Food &amp; hospitality background</option>
					<option>Multi-unit / franchise experience</option>
				</select>
			</div>
			<div class="ch-bk-field">
				<label>Any questions or comments? <small>(optional)</small></label>
				<textarea name="frn_message" class="ch-form-textarea" rows="3" placeholder="Anything you'd like us to know…"></textarea>
			</div>
		</div>

		<div class="ch-bk-nav">
			<button type="button" class="ch-bk-back btn-outline" data-back="1">← Back</button>
			<button type="button" class="ch-bk-next btn-lime" data-next="3">Next: Your Details →</button>
		</div>
	</div>

	<!-- ── STEP 3: Contact + Confirm ─────────────────────────────────── -->
	<div class="ch-bk-step" data-step="3">
		<h3 class="ch-bk-step-title">Almost there! 🌿</h3>
		<p class="ch-bk-step-desc">We'll reply personally within 24 hours. All enquiries are fully confidential.</p>

		<div class="ch-bk-summary" id="ch-frn-summary"></div>

		<div class="ch-bk-fields">
			<div class="ch-bk-field-row">
				<div class="ch-bk-field">
					<label>Your Name *</label>
					<input type="text" name="frn_name" class="ch-form-input" placeholder="Full name" required>
				</div>
				<div class="ch-bk-field">
					<label>Email *</label>
					<input type="email" name="frn_email" class="ch-form-input" placeholder="you@email.com" required>
				</div>
			</div>
			<div class="ch-bk-field">
				<label>Phone / WhatsApp <small>(optional)</small></label>
				<input type="tel" name="frn_phone" class="ch-form-input" placeholder="+44 …">
			</div>
		</div>

		<div class="ch-bk-nav">
			<button type="button" class="ch-bk-back btn-outline" data-back="2">← Back</button>
			<button type="submit" class="ch-bk-submit btn-lime" id="ch-frn-submit">Submit My Enquiry 💼</button>
		</div>
	</div>
<?php
$steps_html = ob_get_clean();

/* ── Reusable modal shell ────────────────────────────────────────────────────── */
get_template_part( 'components/forms/form_step_modal', null, [
	'prefix'       => 'frn',
	'form_id'      => 'ch-frn-form',
	'modal_label'  => 'Franchise enquiry',
	'nonce_action' => 'ch_contact_nonce',
	'nonce_name'   => 'ch_frn_nonce_field',
	'steps'        => $_d['steps'] ?? [],
	'steps_html'   => $steps_html,
] );
