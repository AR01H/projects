/* The Cane House - Forms JS */
(function ($) {
    'use strict';

    if (typeof chTheme === 'undefined') return;

    // ── Contact Form ───────────────────────────────────────────────────────────
    function initContactForm() {
        var form   = document.getElementById('ch-contact-form');
        var msg    = document.getElementById('ch-form-msg');
        var submit = document.getElementById('ch-form-submit');
        if (!form) return;

        function showMsg(text, type) {
            if (!msg) return;
            msg.textContent = text;
            msg.className   = 'ch-form-feedback ' + type;
            msg.style.display = 'block';
            msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function clearErrors() {
            form.querySelectorAll('.ch-field-error').forEach(function (el) { el.remove(); });
            form.querySelectorAll('.invalid').forEach(function (el) { el.classList.remove('invalid'); });
        }

        function showFieldError(field, message) {
            field.classList.add('invalid');
            var err = document.createElement('span');
            err.className   = 'ch-field-error';
            err.textContent = message;
            field.insertAdjacentElement('afterend', err);
        }

        function validate() {
            var ok    = true;
            var name  = document.getElementById('ch-name');
            var email = document.getElementById('ch-email');
            clearErrors();

            if (name && name.value.trim() === '') {
                showFieldError(name, 'Please enter your name.');
                ok = false;
            }
            if (email) {
                if (email.value.trim() === '') {
                    showFieldError(email, 'Please enter your email address.');
                    ok = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                    showFieldError(email, 'Please enter a valid email address.');
                    ok = false;
                }
            }
            return ok;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!validate()) return;

            var originalText = submit.textContent;
            submit.disabled    = true;
            submit.textContent = 'Sending... 🌿';

            var data = new FormData(form);
            data.append('action', 'ch_contact_submit');
            data.append('nonce',  chTheme.nonce);

            fetch(chTheme.ajaxUrl, { method: 'POST', body: data })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success) {
                        showMsg(res.data.message || "Thanks! We'll be in touch soon. 🌿", 'success');
                        submit.textContent = '✓ Sent! 🌿';
                        submit.style.background = 'linear-gradient(135deg,#5a9a2a,#7ac040)';
                        form.reset();
                    } else {
                        showMsg(res.data.message || 'Something went wrong. Please try again.', 'error');
                        submit.disabled    = false;
                        submit.textContent = originalText;
                    }
                })
                .catch(function () {
                    showMsg('Connection error. Please try again.', 'error');
                    submit.disabled    = false;
                    submit.textContent = originalText;
                });
        });
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    $(document).ready(function () {
        initContactForm();
    });

}(jQuery));
