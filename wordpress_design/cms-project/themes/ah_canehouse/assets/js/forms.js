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

    // ── Booking Wizard ─────────────────────────────────────────────────────────
    function initBookingWizard() {
        var wizard = document.getElementById('ch-booking-form');
        if (!wizard) return;

        var section  = document.getElementById('booking');
        var steps    = Array.prototype.slice.call(wizard.querySelectorAll('.ch-bk-step'));
        var progSteps= Array.prototype.slice.call(section.querySelectorAll('.ch-bk-prog-step'));
        var progFill = section.querySelector('.ch-bk-prog-fill');
        var msg      = document.getElementById('ch-bk-msg');
        var submitBtn= document.getElementById('ch-bk-submit');
        var summary  = document.getElementById('ch-bk-summary');
        var total    = steps.length;
        var current  = 1;

        function showMsg(text, type) {
            if (!msg) return;
            msg.textContent   = text;
            msg.className     = 'ch-form-feedback ' + type;
            msg.style.display = 'block';
        }
        function hideMsg() { if (msg) msg.style.display = 'none'; }

        function goTo(step) {
            current = step;
            steps.forEach(function (s) {
                s.classList.toggle('active', parseInt(s.dataset.step, 10) === step);
            });
            progSteps.forEach(function (p) {
                var ps = parseInt(p.dataset.step, 10);
                p.classList.toggle('active', ps === step);
                p.classList.toggle('done',  ps <  step);
            });
            if (progFill) progFill.style.width = ((step - 1) / (total - 1) * 100) + '%';
            if (step === total) buildSummary();
            hideMsg();
            // Scroll wizard into view
            if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function selectedVal(name) {
            var el = wizard.querySelector('[name="' + name + '"]:checked, [name="' + name + '"]');
            return el ? el.value : '';
        }

        function selectedFlavours() {
            var checked = wizard.querySelectorAll('[name="bk_flavour[]"]:checked');
            return Array.prototype.map.call(checked, function (c) { return c.value; });
        }

        function buildSummary() {
            if (!summary) return;
            var flavours = selectedFlavours();
            var rows = [
                ['🥤 Size',    selectedVal('bk_size')],
                ['🌾 Cane',    selectedVal('bk_cane')],
                ['🍋 Flavours', flavours.join(', ')],
                ['🎉 Occasion',wizard.querySelector('[name="bk_occasion"]').value],
                ['📅 Date',    wizard.querySelector('[name="bk_date"]').value],
                ['👥 Guests',  wizard.querySelector('[name="bk_guests"]').value],
                ['📍 Location',wizard.querySelector('[name="bk_location"]').value]
            ];
            var html = '<div class="ch-bk-summary-title">Your Order Summary</div><div class="ch-bk-summary-grid">';
            rows.forEach(function (r) {
                if (!r[1]) return;
                html += '<div class="ch-bk-summary-row"><span class="ch-bk-summary-label">' + r[0] +
                        '</span><span class="ch-bk-summary-val">' + escapeHtml(r[1]) + '</span></div>';
            });
            html += '</div>';
            summary.innerHTML = html;
        }

        function escapeHtml(str) {
            var d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        // Next buttons
        wizard.querySelectorAll('.ch-bk-next').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = parseInt(btn.dataset.next, 10);
                // Require at least one flavour before leaving step 3
                if (current === 3 && selectedFlavours().length === 0) {
                    showMsg('Please pick at least one flavour. 🍋', 'error');
                    return;
                }
                goTo(target);
            });
        });
        // Back buttons
        wizard.querySelectorAll('.ch-bk-back').forEach(function (btn) {
            btn.addEventListener('click', function () {
                goTo(parseInt(btn.dataset.back, 10));
            });
        });
        // Click on a completed progress step to jump back
        progSteps.forEach(function (p) {
            p.addEventListener('click', function () {
                var ps = parseInt(p.dataset.step, 10);
                if (ps < current) goTo(ps);
            });
        });

        // ── Submit ─────────────────────────────────────────────────────────────
        wizard.addEventListener('submit', function (e) {
            e.preventDefault();

            var name  = wizard.querySelector('[name="bk_name"]');
            var email = wizard.querySelector('[name="bk_email"]');

            if (!name.value.trim()) { showMsg('Please enter your name.', 'error'); name.focus(); return; }
            if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                showMsg('Please enter a valid email address.', 'error'); email.focus(); return;
            }

            // Build the combined message
            var flavourList = selectedFlavours().join(', ') || '—';
            var lines = [
                '🥤 NEW ORDER REQUEST',
                '',
                'Size:     ' + selectedVal('bk_size'),
                'Cane:     ' + selectedVal('bk_cane'),
                'Flavours: ' + flavourList,
                '',
                'Occasion: ' + (wizard.querySelector('[name="bk_occasion"]').value || '—'),
                'Date:     ' + (wizard.querySelector('[name="bk_date"]').value     || '—'),
                'Guests:   ' + (wizard.querySelector('[name="bk_guests"]').value   || '—'),
                'Location: ' + (wizard.querySelector('[name="bk_location"]').value || '—'),
                '',
                'Notes:    ' + (wizard.querySelector('[name="bk_notes"]').value    || '—')
            ];
            var compiled = lines.join('\n');

            var originalText = submitBtn.textContent;
            submitBtn.disabled    = true;
            submitBtn.textContent = 'Sending… 🌿';

            var data = new FormData();
            data.append('action',     'ch_contact_submit');
            data.append('nonce',      chTheme.nonce);
            data.append('ch_name',    name.value.trim());
            data.append('ch_email',   email.value.trim());
            data.append('ch_phone',   wizard.querySelector('[name="bk_phone"]').value.trim());
            data.append('ch_enquiry', 'event');
            data.append('ch_message', compiled);

            fetch(chTheme.ajaxUrl, { method: 'POST', body: data })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success) {
                        wizard.querySelector('.ch-bk-step[data-step="5"]').innerHTML =
                            '<div class="ch-bk-success">' +
                            '<div class="ch-bk-success-icon">🎉</div>' +
                            '<h3>Order Request Sent!</h3>' +
                            '<p>' + escapeHtml(res.data.message || "Thanks! We'll be in touch very soon to confirm your order. 🌿") + '</p>' +
                            '</div>';
                        if (progFill) progFill.style.width = '100%';
                        progSteps.forEach(function (p) { p.classList.add('done'); });
                    } else {
                        showMsg(res.data.message || 'Something went wrong. Please try again.', 'error');
                        submitBtn.disabled    = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(function () {
                    showMsg('Connection error. Please try again.', 'error');
                    submitBtn.disabled    = false;
                    submitBtn.textContent = originalText;
                });
        });
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    $(document).ready(function () {
        initContactForm();
        initBookingWizard();
    });

}(jQuery));
