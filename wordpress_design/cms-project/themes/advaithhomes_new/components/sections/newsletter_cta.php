<?php
/**
 * components/sections/newsletter_cta.php - Section: Newsletter subscribe CTA
 *
 * Props: $newsletter { icon, title, description, placeholder, button_label, note }
 * Posts via AJAX to ah_newsletter_subscribe (AH_Ajax_Handlers).
 */

defined( 'ABSPATH' ) || exit;

$newsletter = isset( $newsletter ) && is_array( $newsletter ) ? $newsletter : array();
$nl_nonce   = wp_create_nonce( 'ah_newsletter_nonce' );
?>
<div class="newsletter-inner">
    <div class="newsletter-text">
        <div class="newsletter-inner-header">
            <div class="newsletter-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <polyline points="2,4 12,13 22,4"/>
                </svg>
            </div>
            <div class="newsletter-text-group">
                <h3><?php echo esc_html( isset( $newsletter['title'] ) ? $newsletter['title'] : '' ); ?></h3>
                <p><?php echo esc_html( isset( $newsletter['description'] ) ? $newsletter['description'] : '' ); ?></p>
            </div>
        </div>
    </div>
    <div class="newsletter-form-wrap">
        <form class="newsletter-form adn-nl-form" onsubmit="return false;" data-nonce="<?php echo esc_attr( $nl_nonce ); ?>" data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
            <div class="nl-input-row">
                <input type="email" name="nl_email" class="adn-nl-email"
                       placeholder="<?php echo esc_attr( isset( $newsletter['placeholder'] ) ? $newsletter['placeholder'] : adn_term( 'sidebar.newsletter_placeholder', 'Enter your email address' ) ); ?>"
                       aria-label="<?php echo esc_attr( isset( $newsletter['placeholder'] ) ? $newsletter['placeholder'] : 'Email address' ); ?>"
                       required />
                <button type="submit" class="adn-nl-btn"><?php echo esc_html( isset( $newsletter['button_label'] ) && $newsletter['button_label'] ? $newsletter['button_label'] : SITE_BTN_SUBSCRIBE ); ?></button>
            </div>
        </form>
        <div class="newsletter-spam"><?php echo esc_html( ! empty( $newsletter['note'] ) ? $newsletter['note'] : SITE_NEWSLETTER_CONSENT_NOTE ); ?></div>
        <div class="adn-nl-msg" style="display:none;margin-top:10px;font-size:13.5px;font-weight:500"></div>
    </div>
</div>
<script>
(function () {
    document.querySelectorAll('.adn-nl-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var email = form.querySelector('.adn-nl-email').value.trim();
            var btn   = form.querySelector('.adn-nl-btn');
            var msg   = form.parentNode.querySelector('.adn-nl-msg');
            if (!email) return;
            btn.disabled = true;
            var orig = btn.innerHTML;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="56" stroke-dashoffset="0" style="animation:nl-spin .8s linear infinite;transform-origin:center"/></svg>';
            var fd = new FormData();
            fd.append('action', 'ah_newsletter_subscribe');
            fd.append('nonce',  form.dataset.nonce);
            fd.append('email',  email);
            fd.append('source', 'website');
            fetch(form.dataset.ajaxurl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                    if (msg) {
                        msg.style.display = 'block';
                        msg.textContent   = res.data && res.data.message ? res.data.message : (res.success ? 'Thank you!' : 'Something went wrong.');
                        msg.style.color   = res.success ? '#16a34a' : '#dc2626';
                    }
                    if (res.success) { form.reset(); }
                })
                .catch(function () {
                    btn.disabled  = false;
                    btn.innerHTML = orig;
                    if (msg) { msg.style.display = 'block'; msg.textContent = 'Request failed. Please try again.'; msg.style.color = '#dc2626'; }
                });
        });
    });
})();
</script>
