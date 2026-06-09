<?php
/**
 * front-page.php — Site home page template.
 *
 * Data flow: intermediate_pages/home.php → components (carousels, forms)
 *
 * Sections:
 *   1. Hero
 *   2. Image strip     (carousel_image_view)
 *   3. Video showcase  (carousel_video_scroll)
 *   4. Mini video shelf (carousel_mini_video_scroll)
 *   5. CTA banner      (form_cta_banner)
 *   6. Consultation modal (form_step_modal) + inline boot script
 */

defined( 'ABSPATH' ) || exit;

$data = require get_template_directory() . '/intermediate_pages/home.php';

get_header();
?>

<main class="pt-home-page">

	<!-- ── 1. Hero ──────────────────────────────────────────────────────── -->
	<section class="pt-home-hero">
		<div class="pt-container">
			<p class="pt-section-tag">Welcome</p>
			<h1 class="pt-home-hero__title">Build Something <em>Remarkable</em></h1>
			<p class="pt-home-hero__sub">
				We partner with ambitious teams to design and deliver exceptional digital experiences — on time, on brief, and beyond expectations.
			</p>
			<div class="pt-home-hero__actions">
				<button type="button" class="pt-btn pt-btn--primary" id="pt-hero-consult-open">
					Book a Free Consultation
				</button>
				<a class="pt-home-hero__link" href="#pt-vs-section">
					Watch Our Work ↓
				</a>
			</div>
		</div>
	</section>

	<!-- ── 2. Image strip ───────────────────────────────────────────────── -->
	<section class="pt-home-strip" aria-label="Project showcase strip">
		<?php
		get_template_part( 'components/carousels/carousel_image_view', null, [
			'uid'       => 'home-strip-rtl',
			'direction' => 'rtl',
			'speed'     => 50,
			'items'     => $data['showcase_items'],
		] );
		?>
	</section>

	<!-- ── 3. Video showcase ────────────────────────────────────────────── -->
	<div id="pt-vs-section">
		<?php
		get_template_part( 'components/carousels/carousel_video_scroll', null, [
			'tag'   => $data['video_heading']['tag']   ?? '',
			'title' => $data['video_heading']['title'] ?? '',
			'body'  => $data['video_heading']['body']  ?? '',
			'csv'   => 'video-showcase',
		] );
		?>
	</div>

	<!-- ── 4. Mini video shelf ──────────────────────────────────────────── -->
	<?php
	get_template_part( 'components/carousels/carousel_mini_video_scroll', null, [
		'tag'   => $data['mini_video_heading']['tag']   ?? '',
		'title' => $data['mini_video_heading']['title'] ?? '',
		'body'  => $data['mini_video_heading']['body']  ?? '',
		'csv'   => 'mini-video-showcase',
	] );
	?>

	<!-- ── 5. CTA banner ────────────────────────────────────────────────── -->
	<?php
	get_template_part( 'components/forms/form_cta_banner', null, [
		'section_id'     => 'consultation',
		'section_class'  => 'pt-home-cta-section',
		'card_class'     => 'pt-ctab-card',
		'image_side'     => 'right',
		'content_class'  => 'pt-ctab-content',
		'visual_class'   => 'pt-ctab-visual',
		'badge_class'    => 'pt-ctab-badge',
		'badge'          => 'Free Consultation',
		'tag'            => 'Get Started Today',
		'tag_color'      => 'var(--pt-accent)',
		'title_class'    => 'pt-ctab-title',
		'heading'        => 'Ready to build something <em>great?</em>',
		'sub_class'      => 'pt-ctab-sub',
		'sub'            => "Let's talk about your goals. Our first session is completely free — no pitch, just strategy.",
		'features_class' => 'pt-ctab-features',
		'features'       => [
			[ 'icon' => '✓', 'text' => 'No obligation, ever'         ],
			[ 'icon' => '✓', 'text' => 'Response within 24 hours'    ],
			[ 'icon' => '✓', 'text' => 'Clear, upfront pricing'      ],
		],
		'image'          => get_template_directory_uri() . '/assets/images/cta-team.jpg',
		'image_alt'      => 'Our team collaborating on a project',
		'button_id'      => 'pt-consult-open',
		'button_class'   => 'pt-btn pt-btn--primary',
		'button_label'   => 'Book Free Call',
	] );
	?>

	<!-- ── 6. Consultation modal ─────────────────────────────────────────── -->
	<?php
	get_template_part( 'components/forms/form_step_modal', null, [
		'prefix'       => 'consult',
		'form_id'      => 'pt-consult-form',
		'modal_label'  => 'Book a free consultation',
		'nonce_action' => 'pt_contact_nonce',
		'nonce_name'   => 'pt_consult_nonce',
		'steps'        => [ 'Your Project', 'Your Details', 'Confirm' ],
		'steps_html'   => $data['consult_steps_html'],
	] );
	?>

</main>

<script>
/* Boot the consultation wizard once the page is interactive. */
document.addEventListener('DOMContentLoaded', function () {
	if (typeof window.ptStepModal !== 'function') return;

	var wizard = window.ptStepModal({
		prefix:         'consult',
		action:         'pt_consultation_submit',
		sendingLabel:   'Sending…',
		successIcon:    '✓',
		successTitle:   'Request Sent!',
		successMessage: "Thank you! We’ll be in touch within 24 hours.",

		validateStep: function (ctx, step) {
			ctx.clearErrors();
			if (step === 2) {
				var name  = ctx.val('consult_name');
				var email = ctx.val('consult_email');
				if (!name)  { ctx.showMsg('Please enter your name.',          'error'); return false; }
				if (!email) { ctx.showMsg('Please enter a valid email address.', 'error'); return false; }
			}
			return true;
		},

		buildSummary: function (ctx) {
			var serviceMap = {
				web_design:  'Web Design',
				branding:    'Branding & Identity',
				development: 'Development',
				ecommerce:   'E-Commerce',
				other:       'Other',
			};
			var budgetMap = {
				under_5k: 'Under $5,000',
				'5k_15k': '$5,000 – $15,000',
				'15k_50k':'$15,000 – $50,000',
				'50k_plus':'$50,000+',
			};
			var svc    = ctx.val('consult_service');
			var budget = ctx.val('consult_budget');
			var brief  = ctx.val('consult_brief');
			return '<table class="pt-bk-summary-table">' +
				'<tr><td>Service</td><td>' + ctx.escHtml(serviceMap[svc] || svc || '—')     + '</td></tr>' +
				'<tr><td>Budget</td><td>'  + ctx.escHtml(budgetMap[budget] || budget || '—') + '</td></tr>' +
				(brief ? '<tr><td>Brief</td><td>' + ctx.escHtml(brief) + '</td></tr>' : '') +
				'<tr><td>Name</td><td>'    + ctx.escHtml(ctx.val('consult_name'))   + '</td></tr>' +
				'<tr><td>Email</td><td>'   + ctx.escHtml(ctx.val('consult_email'))  + '</td></tr>' +
				(ctx.val('consult_phone') ? '<tr><td>Phone</td><td>' + ctx.escHtml(ctx.val('consult_phone')) + '</td></tr>' : '') +
				'</table>';
		},
	});

	/* Wire the hero CTA to the same modal */
	var heroBtn = document.getElementById('pt-hero-consult-open');
	if (heroBtn && wizard) {
		heroBtn.addEventListener('click', wizard.open);
	}
});
</script>

<?php get_footer(); ?>
