<?php
/**
 * intermediate_pages/home.php — data layer for front-page.php.
 *
 * Loads section headings, prepares showcase strip items, and
 * builds the consultation modal steps HTML via ob_start().
 *
 * Required by: front-page.php  (require … → $data)
 * Data class:  PT_Real_Loader
 */

defined( 'ABSPATH' ) || exit;

/* ── Section headings ─────────────────────────────────────────────────────── */
$video_heading      = class_exists( 'PT_Real_Loader' ) ? PT_Real_Loader::section_heading( 'video_showcase_home' ) : [];
$mini_video_heading = class_exists( 'PT_Real_Loader' ) ? PT_Real_Loader::section_heading( 'mini_video_home' )     : [];

/* ── Image strip items ────────────────────────────────────────────────────── */
/* Replace src values with real project images before launch.                 */
$theme_uri     = get_template_directory_uri();
$showcase_items = [
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/img-1.jpg', 'label' => 'Brand Identity'    ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/img-2.jpg', 'label' => 'Web Design'        ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/img-3.jpg', 'label' => 'UI / UX'           ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/img-4.jpg', 'label' => 'Mobile App'        ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/img-5.jpg', 'label' => 'E-Commerce'        ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/img-6.jpg', 'label' => 'Dashboard'         ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/img-7.jpg', 'label' => 'Landing Page'      ],
	[ 'type' => 'image', 'src' => $theme_uri . '/assets/images/showcase/img-8.jpg', 'label' => 'Content Platform'  ],
];

/* ── Consultation modal steps HTML ───────────────────────────────────────── */
ob_start();
?>
<div class="pt-bk-step" data-step="1">
	<h3>Your Project</h3>
	<p class="pt-bk-hint">Tell us a little about what you need.</p>

	<div class="pt-bk-field">
		<label for="consult_service">What are you looking for?</label>
		<select name="consult_service" id="consult_service">
			<option value="">— Select a service —</option>
			<option value="web_design">Web Design</option>
			<option value="branding">Branding &amp; Identity</option>
			<option value="development">Development</option>
			<option value="ecommerce">E-Commerce</option>
			<option value="other">Other</option>
		</select>
	</div>

	<div class="pt-bk-field">
		<label for="consult_budget">Approximate budget</label>
		<select name="consult_budget" id="consult_budget">
			<option value="">— Select a range —</option>
			<option value="under_5k">Under $5,000</option>
			<option value="5k_15k">$5,000 – $15,000</option>
			<option value="15k_50k">$15,000 – $50,000</option>
			<option value="50k_plus">$50,000+</option>
		</select>
	</div>

	<div class="pt-bk-field">
		<label for="consult_brief">Briefly describe your project</label>
		<textarea name="consult_brief" id="consult_brief" rows="3" placeholder="What's the goal? What problem are you solving?"></textarea>
	</div>

	<button type="button" class="pt-bk-next pt-btn pt-btn--primary" data-next="2" style="margin-top:.5rem;">
		Next &rarr;
	</button>
</div>

<div class="pt-bk-step" data-step="2">
	<h3>Your Details</h3>
	<p class="pt-bk-hint">How should we reach you?</p>

	<div class="pt-bk-field">
		<label for="consult_name">Full name</label>
		<input type="text" name="consult_name" id="consult_name"
			placeholder="Jane Smith" autocomplete="name">
	</div>

	<div class="pt-bk-field">
		<label for="consult_email">Email address</label>
		<input type="email" name="consult_email" id="consult_email"
			placeholder="jane@example.com" autocomplete="email">
	</div>

	<div class="pt-bk-field">
		<label for="consult_phone">Phone <small style="font-weight:400;color:#5a6e8a;">(optional)</small></label>
		<input type="tel" name="consult_phone" id="consult_phone"
			placeholder="+1 555 000 0000" autocomplete="tel">
	</div>

	<div class="pt-bk-actions">
		<button type="button" class="pt-bk-back pt-btn pt-btn--ghost" data-back="1">&larr; Back</button>
		<button type="button" class="pt-bk-next pt-btn pt-btn--primary" data-next="3">Review &rarr;</button>
	</div>
</div>

<div class="pt-bk-step" data-step="3">
	<h3>Confirm Your Request</h3>
	<p class="pt-bk-hint">Everything look right? Hit Send and we&rsquo;ll be in touch within 24 hours.</p>

	<div id="pt-consult-summary" class="pt-bk-summary"></div>

	<div class="pt-bk-actions">
		<button type="button" class="pt-bk-back pt-btn pt-btn--ghost" data-back="2">&larr; Back</button>
		<button type="submit" class="pt-btn pt-btn--primary" id="pt-consult-submit">Send Request</button>
	</div>
</div>
<?php
$consult_steps_html = ob_get_clean();

/* ── Return data array ────────────────────────────────────────────────────── */
return [
	'video_heading'      => $video_heading,
	'mini_video_heading' => $mini_video_heading,
	'showcase_items'     => $showcase_items,
	'consult_steps_html' => $consult_steps_html,
];
