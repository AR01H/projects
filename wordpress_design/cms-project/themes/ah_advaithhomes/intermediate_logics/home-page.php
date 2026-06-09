<?php
defined( 'ABSPATH' ) || exit;

$hp = AH_Home_Data::get();

/* ── Section headings for carousel sections ──────────────────────────────── */
$hp['video_heading']      = class_exists( 'AH_Page_Data' ) ? AH_Page_Data::section_heading( 'video_showcase_home' ) : [];
$hp['mini_video_heading'] = class_exists( 'AH_Page_Data' ) ? AH_Page_Data::section_heading( 'mini_video_home' )     : [];
$hp['cta_heading']        = class_exists( 'AH_Page_Data' ) ? AH_Page_Data::section_heading( 'home_cta' )            : [];

/* ── Showcase image strip items ──────────────────────────────────────────── */
/* Replace src values with real property images before launch.               */
$theme_uri = get_template_directory_uri();
$hp['showcase_items'] = [
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/prop-1.jpg', 'label' => 'Kensington Townhouse'  ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/prop-2.jpg', 'label' => 'Notting Hill Flat'     ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/prop-3.jpg', 'label' => 'Cotswolds Cottage'     ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/prop-4.jpg', 'label' => 'City Penthouse'        ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/prop-5.jpg', 'label' => 'Surrey Family Home'   ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/prop-6.jpg', 'label' => 'Chelsea Mews'          ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/prop-7.jpg', 'label' => 'Bath Georgian Villa'  ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/prop-8.jpg', 'label' => 'Oxford Townhouse'      ],
];

/* ── Consultation modal steps HTML ───────────────────────────────────────── */
ob_start();
?>
<div class="ah-bk-step" data-step="1">
	<h3>Your Property Brief</h3>
	<p class="ah-bk-hint">Tell us a little about what you&rsquo;re looking for.</p>

	<div class="ah-bk-field">
		<label for="consult_type">Type of purchase</label>
		<select name="consult_type" id="consult_type">
			<option value="">— Select —</option>
			<option value="primary">Primary residence</option>
			<option value="investment">Investment property</option>
			<option value="holiday">Holiday home</option>
			<option value="relocation">Relocation</option>
		</select>
	</div>

	<div class="ah-bk-field">
		<label for="consult_budget">Approximate budget</label>
		<select name="consult_budget" id="consult_budget">
			<option value="">— Select —</option>
			<option value="under_500k">Under £500,000</option>
			<option value="500k_1m">£500,000 – £1,000,000</option>
			<option value="1m_2m">£1m – £2m</option>
			<option value="2m_plus">£2m+</option>
		</select>
	</div>

	<div class="ah-bk-field">
		<label for="consult_location">Preferred location / area</label>
		<input type="text" name="consult_location" id="consult_location"
			placeholder="e.g. South West London, Cotswolds…">
	</div>

	<button type="button" class="ah-bk-next btn btn-primary" data-next="2" style="margin-top:.5rem;">
		Next &rarr;
	</button>
</div>

<div class="ah-bk-step" data-step="2">
	<h3>Your Details</h3>
	<p class="ah-bk-hint">How should we reach you?</p>

	<div class="ah-bk-field">
		<label for="consult_name">Full name</label>
		<input type="text" name="consult_name" id="consult_name"
			placeholder="Jane Smith" autocomplete="name">
	</div>

	<div class="ah-bk-field">
		<label for="consult_email">Email address</label>
		<input type="email" name="consult_email" id="consult_email"
			placeholder="jane@example.com" autocomplete="email">
	</div>

	<div class="ah-bk-field">
		<label for="consult_phone">Phone <small style="font-weight:400;color:#64748b;">(optional)</small></label>
		<input type="tel" name="consult_phone" id="consult_phone"
			placeholder="+44 7700 900 000" autocomplete="tel">
	</div>

	<div class="ah-bk-actions">
		<button type="button" class="ah-bk-back" data-back="1">&larr; Back</button>
		<button type="button" class="ah-bk-next btn btn-primary" data-next="3">Review &rarr;</button>
	</div>
</div>

<div class="ah-bk-step" data-step="3">
	<h3>Confirm Your Request</h3>
	<p class="ah-bk-hint">Everything look right? We respond within one working day.</p>

	<div id="ah-consult-summary" class="ah-bk-summary"></div>

	<div class="ah-bk-actions">
		<button type="button" class="ah-bk-back" data-back="2">&larr; Back</button>
		<button type="submit" class="btn btn-primary" id="ah-consult-submit">Send Request</button>
	</div>
</div>
<?php
$hp['consult_steps_html'] = ob_get_clean();

return $hp;
