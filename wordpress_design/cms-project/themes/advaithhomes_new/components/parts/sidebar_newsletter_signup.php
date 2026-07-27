<?php
/**
 * components/parts/sidebar_newsletter_signup.php - Compact newsletter signup in sidebar.
 *
 * Props: $newsletter { heading, description, placeholder, button_label, note }
 * Posts via AJAX to ah_newsletter_subscribe (AH_Ajax_Handlers) - same handler
 * newsletter_cta.php/post_sidebar_newsletter.php use, so a successful new
 * signup also fires AH_Workflow_Manager::evaluate('newsletter_subscribe', ...)
 * exactly like those.
 * Usage: adn_component( 'parts/sidebar_newsletter_signup', array( 'newsletter' => $ctx['sidebar']['newsletter'] ) );
 */

defined( 'ABSPATH' ) || exit;

$newsletter = isset( $newsletter ) && is_array( $newsletter ) ? $newsletter : array();

if ( empty( $newsletter ) ) {
	return;
}

$_nl_nonce = wp_create_nonce( 'ah_newsletter_nonce' );
?>
<div class="news-sb-box">
	<?php if ( ! empty( $newsletter['heading'] ) ) : ?>
		<div class="news-sb-title"><?php echo esc_html( $newsletter['heading'] ); ?></div>
	<?php endif; ?>

	<div class="sb-newsletter-body">
		<?php if ( ! empty( $newsletter['description'] ) ) : ?>
			<p class="sb-nl-desc"><?php echo esc_html( $newsletter['description'] ); ?></p>
		<?php endif; ?>

		<form class="sb-nl-form" onsubmit="return false;" novalidate
		      aria-label="<?php echo esc_attr__( 'Newsletter signup', ADN_TEXT_DOMAIN ); ?>"
		      data-nonce="<?php echo esc_attr( $_nl_nonce ); ?>"
		      data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
			<input
				type="email"
				name="nl_email"
				class="sb-nl-input"
				placeholder="<?php echo esc_attr( isset( $newsletter['placeholder'] ) ? $newsletter['placeholder'] : '' ); ?>"
				aria-label="<?php echo esc_attr__( 'Email address', ADN_TEXT_DOMAIN ); ?>"
				required
			/>
			<button type="submit" class="btn btn-accent sb-nl-btn">
				<?php echo esc_html( isset( $newsletter['button_label'] ) ? $newsletter['button_label'] : '' ); ?>
			</button>
		</form>

		<p class="sb-nl-note"><?php echo esc_html( ! empty( $newsletter['note'] ) ? $newsletter['note'] : SITE_NEWSLETTER_CONSENT_NOTE ); ?></p>
		<div class="sb-nl-msg" style="display:none;margin-top:8px;font-size:13px;font-weight:500"></div>
	</div>
</div>
<script>
(function () {
	// This component can render more than once on the same page (e.g. twice on
	// the News page) - each instance emits this same script, so guard against
	// re-binding a form an earlier instance's script already wired up.
	document.querySelectorAll('.sb-nl-form').forEach(function (form) {
		if (form.dataset.nlBound) return;
		form.dataset.nlBound = '1';
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var email = form.querySelector('.sb-nl-input').value.trim();
			var btn   = form.querySelector('.sb-nl-btn');
			var msg   = form.parentNode.querySelector('.sb-nl-msg');
			if (!email) return;
			btn.disabled = true;
			var orig = btn.textContent;
			btn.textContent = '...';
			var fd = new FormData();
			fd.append('action', 'ah_newsletter_subscribe');
			fd.append('nonce',  form.dataset.nonce);
			fd.append('email',  email);
			fd.append('source', 'sidebar');
			fetch(form.dataset.ajaxurl, { method: 'POST', body: fd })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					btn.disabled = false;
					btn.textContent = orig;
					if (msg) {
						msg.style.display = 'block';
						msg.textContent   = res.data && res.data.message ? res.data.message : (res.success ? 'Thank you!' : 'Something went wrong.');
						msg.style.color   = res.success ? '#16a34a' : '#dc2626';
					}
					if (res.success) { form.reset(); }
				})
				.catch(function () {
					btn.disabled  = false;
					btn.textContent = orig;
					if (msg) { msg.style.display = 'block'; msg.textContent = 'Request failed. Please try again.'; msg.style.color = '#dc2626'; }
				});
		});
	});
})();
</script>
