<?php
defined( 'ABSPATH' ) || exit;

$s           = ch_get_settings();
$_d          = CH_About_Data::franchise_enquiry_settings();
$frn_heading = $s['franchise_wiz_heading'] ?? $_d['heading'] ?? '';
$frn_sub     = $s['franchise_wiz_sub']     ?? $_d['sub']     ?? '';
$frn_image   = $s['franchise_wiz_image']   ?? 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80';
?>

<!-- ═══ FRANCHISE BANNER ════════════════════════════════════════════════════════ -->
<section id="franchise-enquiry" class="ch-frn-section">
	<div class="container">
		<div class="ch-frn-card fade-up">

			<!-- Left: image -->
			<div class="ch-frn-visual">
				<img src="<?php echo esc_url( $frn_image ); ?>" alt="Franchise opportunity" loading="lazy">
				<div class="ch-frn-visual-badge">Be Your Own Boss 🌿</div>
			</div>

			<!-- Right: content -->
			<div class="ch-frn-content">
				<div class="section-tag" style="color:var(--client-color-7);">Franchise Opportunity</div>
				<h2 class="ch-frn-title"><?php echo wp_kses( $frn_heading, [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h2>
				<p class="ch-frn-sub"><?php echo esc_html( $frn_sub ); ?></p>

				<ul class="ch-frn-features">
					<li>💼 Full training &amp; ongoing support</li>
					<li>📍 Exclusive territory rights</li>
					<li>🚀 Launch-ready in weeks, not months</li>
				</ul>

				<button type="button" class="ch-frn-open btn-lime" id="ch-frn-open">
					🌿 Enquire Now
				</button>
			</div>

		</div>
	</div>
</section>

<!-- ═══ FRANCHISE ENQUIRY MODAL ════════════════════════════════════════════════ -->
<div class="ch-bk-modal" id="ch-frn-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Franchise enquiry">
	<div class="ch-bk-modal-backdrop" data-frn-close></div>

	<div class="ch-bk-modal-box">
		<button type="button" class="ch-bk-modal-close" data-frn-close aria-label="Close">&times;</button>

		<div class="ch-bk-modal-scroll">

			<!-- Progress bar -->
			<div class="ch-bk-progress">
				<?php
				$frn_steps = $_d['steps'] ?? [];
				foreach ( $frn_steps as $i => $lbl ) :
				?>
					<div class="ch-bk-prog-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo $i + 1; ?>">
						<div class="ch-bk-prog-dot"><?php echo $i + 1; ?></div>
						<span class="ch-bk-prog-label"><?php echo esc_html( $lbl ); ?></span>
					</div>
				<?php endforeach; ?>
				<div class="ch-bk-prog-line"><span class="ch-bk-prog-fill"></span></div>
			</div>

			<form id="ch-frn-form" novalidate>
				<?php wp_nonce_field( 'ch_contact_nonce', 'ch_frn_nonce_field' ); ?>
				<div id="ch-frn-msg" class="ch-form-feedback" style="display:none;" role="alert"></div>

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

			</form>

		</div><!-- .ch-bk-modal-scroll -->
	</div>
</div>
