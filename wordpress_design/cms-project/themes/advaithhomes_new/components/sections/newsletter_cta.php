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
    <div>
        <h3 class="newsletter-inner-header"><div class="newsletter-icon"><?php echo adn_icon( isset( $newsletter['icon'] ) ? $newsletter['icon'] : '' ); ?></div><?php echo esc_html( isset( $newsletter['title'] ) ? $newsletter['title'] : '' ); ?></h3>
        <p><?php echo esc_html( isset( $newsletter['description'] ) ? $newsletter['description'] : '' ); ?></p>
    </div>
    <div class="newsletter-form-wrap">
        <form class="newsletter-form adn-nl-form" onsubmit="return false;" data-nonce="<?php echo esc_attr( $nl_nonce ); ?>" data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
            <input type="email" name="nl_email" class="adn-nl-email"
                   placeholder="<?php echo esc_attr( isset( $newsletter['placeholder'] ) ? $newsletter['placeholder'] : 'Your email address' ); ?>"
                   aria-label="<?php echo esc_attr( isset( $newsletter['placeholder'] ) ? $newsletter['placeholder'] : 'Email address' ); ?>"
                   required />
            <button type="submit" class="btn btn-accent adn-nl-btn"><?php echo esc_html( isset( $newsletter['button_label'] ) ? $newsletter['button_label'] : 'Subscribe' ); ?></button>
        </form>
        <div class="newsletter-spam"><?php echo esc_html( isset( $newsletter['note'] ) ? $newsletter['note'] : '' ); ?></div>
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
            var orig = btn.textContent;
            btn.textContent = '…';
            var fd = new FormData();
            fd.append('action', 'ah_newsletter_subscribe');
            fd.append('nonce',  form.dataset.nonce);
            fd.append('email',  email);
            fd.append('source', 'website');
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
                    btn.disabled    = false;
                    btn.textContent = orig;
                    if (msg) { msg.style.display = 'block'; msg.textContent = 'Request failed. Please try again.'; msg.style.color = '#dc2626'; }
                });
        });
    });
})();
</script>
