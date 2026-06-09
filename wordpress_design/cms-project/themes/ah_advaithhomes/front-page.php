<?php
/**
 * front-page.php — Advaith Homes home page.
 *
 * Data flow: intermediate_logics/home-page.php → components
 *
 * Sections:
 *   1. Hero, Bento, Topics, Articles  (existing)
 *   2. Property image strip           (carousel_image_view)
 *   3. Video showcase                 (carousel_video_scroll)
 *   4. Client shorts shelf            (carousel_mini_video_scroll)
 *   5. Free consultation CTA + modal  (form_cta_banner + form_step_modal)
 *   6. CTA section                    (existing)
 */

defined( 'ABSPATH' ) || exit;
get_header();
$hp = require get_template_directory() . '/intermediate_logics/home-page.php';
?>

<div class="nhp-wrap">
<?php
/* ── 1. Existing sections ─────────────────────────────────────────────────── */
get_template_part( 'components/home/hero',     null, $hp );
get_template_part( 'components/home/bento',    null, $hp );
get_template_part( 'components/home/topics',   null, $hp );
get_template_part( 'components/home/articles', null, $hp );
?>
</div>

<!-- ── 2. Property image strip ──────────────────────────────────────────── -->
<section class="ah-home-strip" aria-label="Property showcase">
<?php
get_template_part( 'components/carousels/carousel_image_view', null, [
	'uid'       => 'home-prop-strip',
	'direction' => 'rtl',
	'speed'     => 45,
	'items'     => $hp['showcase_items'],
] );
?>
</section>

<!-- ── 3. Video showcase ─────────────────────────────────────────────────── -->
<?php
get_template_part( 'components/carousels/carousel_video_scroll', null, [
	'tag'   => $hp['video_heading']['tag']   ?? '',
	'title' => $hp['video_heading']['title'] ?? '',
	'body'  => $hp['video_heading']['body']  ?? '',
	'csv'   => 'video-showcase',
] );
?>

<!-- ── 4. Client shorts shelf ────────────────────────────────────────────── -->
<?php
get_template_part( 'components/carousels/carousel_mini_video_scroll', null, [
	'tag'   => $hp['mini_video_heading']['tag']   ?? '',
	'title' => $hp['mini_video_heading']['title'] ?? '',
	'body'  => $hp['mini_video_heading']['body']  ?? '',
	'csv'   => 'mini-video-showcase',
] );
?>

<!-- ── 5. Free consultation CTA ──────────────────────────────────────────── -->
<?php
get_template_part( 'components/forms/form_cta_banner', null, [
	'section_id'     => 'consultation',
	'section_class'  => 'ah-ctab-section',
	'card_class'     => 'ah-ctab-card',
	'image_side'     => 'right',
	'content_class'  => 'ah-ctab-content',
	'visual_class'   => 'ah-ctab-visual',
	'badge_class'    => 'ah-ctab-badge',
	'badge'          => 'Free · No Obligation',
	'tag'            => $hp['cta_heading']['tag']   ?? 'Free Consultation',
	'tag_color'      => 'var(--gold-400)',
	'title_class'    => 'ah-ctab-title',
	'heading'        => $hp['cta_heading']['title'] ?? 'Start With a Free <em>30-Minute Call</em>',
	'sub_class'      => 'ah-ctab-sub',
	'sub'            => $hp['cta_heading']['body']  ?? "We'll understand your brief, share what's possible, and explain exactly how we work — no commitment required.",
	'features_class' => 'ah-ctab-features',
	'features'       => [
		[ 'icon' => '✓', 'text' => 'No obligation or hard sell'     ],
		[ 'icon' => '✓', 'text' => 'Expert, independent advice'     ],
		[ 'icon' => '✓', 'text' => 'Response within one working day' ],
	],
	'image'          => get_template_directory_uri() . '/assets/images/consultation-team.jpg',
	'image_alt'      => "Advaith Homes buyer's agents team",
	'button_id'      => 'ah-consult-open',
	'button_class'   => 'btn btn-primary',
	'button_label'   => 'Book Free Call',
] );

/* Consultation modal */
get_template_part( 'components/forms/form_step_modal', null, [
	'prefix'       => 'consult',
	'form_id'      => 'ah-consult-form',
	'modal_label'  => 'Book a free consultation',
	'nonce_action' => 'ah_contact_nonce',
	'nonce_name'   => 'ah_consult_nonce_field',
	'steps'        => [ 'Your Brief', 'Your Details', 'Confirm' ],
	'steps_html'   => $hp['consult_steps_html'],
] );
?>

<script>
/* Boot the consultation wizard. */
document.addEventListener('DOMContentLoaded', function () {
	if (typeof window.ahStepModal !== 'function') return;

	window.ahStepModal({
		prefix:         'consult',
		action:         'ah_consultation_submit',
		sendingLabel:   'Sending…',
		successIcon:    '✓',
		successTitle:   'Request Received!',
		successMessage: "Thank you — we'll be in touch within one working day.",

		validateStep: function (ctx, step) {
			ctx.clearErrors();
			if (step === 2) {
				var name  = ctx.val('consult_name');
				var email = ctx.val('consult_email');
				if (!name)  { ctx.showMsg('Please enter your name.',              'error'); return false; }
				if (!email) { ctx.showMsg('Please enter a valid email address.',  'error'); return false; }
			}
			return true;
		},

		buildSummary: function (ctx) {
			var typeMap = {
				primary:    'Primary residence',
				investment: 'Investment property',
				holiday:    'Holiday home',
				relocation: 'Relocation',
			};
			var budgetMap = {
				'under_500k': 'Under £500,000',
				'500k_1m':    '£500,000 – £1,000,000',
				'1m_2m':      '£1m – £2m',
				'2m_plus':    '£2m+',
			};
			var ptype    = ctx.val('consult_type');
			var budget   = ctx.val('consult_budget');
			var location = ctx.val('consult_location');
			return '<table class="ah-bk-summary-table">' +
				'<tr><td>Purchase type</td><td>' + ctx.escHtml(typeMap[ptype]     || ptype   || '—') + '</td></tr>' +
				'<tr><td>Budget</td><td>'        + ctx.escHtml(budgetMap[budget]  || budget  || '—') + '</td></tr>' +
				(location ? '<tr><td>Location</td><td>' + ctx.escHtml(location) + '</td></tr>' : '') +
				'<tr><td>Name</td><td>'   + ctx.escHtml(ctx.val('consult_name'))  + '</td></tr>' +
				'<tr><td>Email</td><td>'  + ctx.escHtml(ctx.val('consult_email')) + '</td></tr>' +
				(ctx.val('consult_phone') ? '<tr><td>Phone</td><td>' + ctx.escHtml(ctx.val('consult_phone')) + '</td></tr>' : '') +
				'</table>';
		},
	});
});
</script>

<!-- ── 6. Existing CTA section ────────────────────────────────────────────── -->
<?php
get_template_part( 'components/cta-section', null, [] );
get_footer();
