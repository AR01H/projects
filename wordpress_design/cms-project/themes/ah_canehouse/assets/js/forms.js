/* The Cane House - Forms JS
   Multi-step wizards are driven by the shared chStepModal controller
   (assets/js/form-step-modal.js); this file holds the contact form, the
   native-share button, and each wizard's form-specific config. */
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
            form.querySelectorAll('.ch-field-error:not(.ch-consent-error)').forEach(function (el) { el.remove(); });
            form.querySelectorAll('.invalid').forEach(function (el) { el.classList.remove('invalid'); });
            var consentErr = form.querySelector('.ch-consent-error');
            if (consentErr) consentErr.style.display = 'none';
            var dg = form.querySelector('.ch-disclaimer-group');
            if (dg) dg.classList.remove('has-error');
        }

        function showFieldError(field, message) {
            field.classList.add('invalid');
            var err = document.createElement('span');
            err.className   = 'ch-field-error';
            err.textContent = message;
            field.insertAdjacentElement('afterend', err);
        }

        function validate() {
            var ok      = true;
            var name    = document.getElementById('ch-name');
            var email   = document.getElementById('ch-email');
            var consent = document.getElementById('ch-consent');
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
            if (consent && !consent.checked) {
                var consentErr = form.querySelector('.ch-consent-error');
                if (consentErr) consentErr.style.display = 'block';
                consent.closest('.ch-disclaimer-group').classList.add('has-error');
                ok = false;
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
                        var formContainer = form.closest('.ch-contact-form');
                        var message = res.data.message || "Thanks! We'll be in touch soon. 🌿";

                        var successBox = document.createElement('div');
                        successBox.style.cssText = 'display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:500px;text-align:center;padding:4rem 2.5rem;';

                        var emoji = document.createElement('div');
                        emoji.textContent = '🌿';
                        emoji.style.cssText = 'font-size:5.5rem;margin-bottom:2rem;line-height:1;';

                        var title = document.createElement('h3');
                        title.textContent = 'Message Sent!';
                        title.style.cssText = 'font-family:var(--ch-font-display);font-size:2.2rem;font-weight:900;color:var(--client-color-1);margin-bottom:1rem;letter-spacing:-0.01em;';

                        var msgText = document.createElement('p');
                        msgText.textContent = message;
                        msgText.style.cssText = 'font-size:1.05rem;color:var(--client-color-15-muted);line-height:1.75;max-width:480px;margin:0;';

                        successBox.appendChild(emoji);
                        successBox.appendChild(title);
                        successBox.appendChild(msgText);

                        formContainer.innerHTML = '';
                        formContainer.appendChild(successBox);
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

    // ── Native Share Button ─────────────────────────────────────────────────────
    function initNativeShare() {
        var shareBtn = document.getElementById('ch-native-share');
        if (!shareBtn) return;

        shareBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var url = window.location.href;
            var title = document.querySelector('h1') ? document.querySelector('h1').textContent : document.title;

            if (navigator.share) {
                navigator.share({ title: title, text: 'Check this out!', url: url })
                    .catch(function (err) { if (err.name !== 'AbortError') console.error('Share error:', err); });
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    var originalText = shareBtn.innerHTML;
                    shareBtn.innerHTML = '✓ Link copied!';
                    setTimeout(function () { shareBtn.innerHTML = originalText; }, 2000);
                }).catch(function () {
                    alert('Copy to clipboard failed. URL: ' + url);
                });
            } else {
                prompt('Copy link:', url);
            }
        });
    }

    // ── Booking Wizard ──────────────────────────────────────────────────────────
    function initBookingWizard() {
        if (typeof chStepModal !== 'function') return;
        chStepModal({
            prefix:         'bk',
            formId:         'ch-booking-form',
            action:         'ch_booking_submit',
            successTitle:   'Order Request Sent!',
            successMessage: "Thanks! We'll be in touch very soon to confirm your order. 🌿",

            validateStep: function (ctx, step) {
                ctx.clearErrors();
                var canes = ctx.form.querySelectorAll('[name="bk_cane[]"]:checked').length;
                var flav  = ctx.form.querySelectorAll('[name="bk_flavour[]"]:checked').length;
                if (step === 1 && canes === 0) { ctx.showMsg('Please choose at least one cane type. 🌾', 'error'); return false; }
                if (step === 2 && flav  === 0) { ctx.showMsg('Please pick at least one flavour. 🍋', 'error'); return false; }
                if (step === 4) {
                    var ok = true;
                    var occasion = ctx.form.querySelector('[name="bk_occasion"]');
                    var date     = ctx.form.querySelector('[name="bk_date"]');
                    var guests   = ctx.form.querySelector('[name="bk_guests"]');
                    var location = ctx.form.querySelector('[name="bk_location"]');
                    if (!occasion.value.trim()) { ctx.fieldError(occasion.closest('.ch-bk-field'), 'Please select an occasion.'); ok = false; }
                    if (!date.value.trim())     { ctx.fieldError(date.closest('.ch-bk-field'), 'Please enter the event date.'); ok = false; }
                    if (!guests.value.trim() || parseInt(guests.value, 10) < 1) { ctx.fieldError(guests.closest('.ch-bk-field'), 'Please enter the number of guests (minimum 1).'); ok = false; }
                    if (!location.value.trim()) { ctx.fieldError(location.closest('.ch-bk-field'), 'Please enter the venue location.'); ok = false; }
                    if (!ok) { ctx.showMsg('Please fill in all required event details. 📋', 'error'); return false; }
                }
                return true;
            },

            buildSummary: function (ctx) {
                var canes = Array.prototype.map.call(ctx.form.querySelectorAll('[name="bk_cane[]"]:checked'),    function (c) { return c.value; });
                var flav  = Array.prototype.map.call(ctx.form.querySelectorAll('[name="bk_flavour[]"]:checked'), function (c) { return c.value; });
                var rows = [
                    ['🌾 Cane',     canes.join(', ')],
                    ['🍋 Flavours', flav.join(', ')],
                    ['🎉 Occasion', ctx.val('bk_occasion')],
                    ['📅 Date',     ctx.val('bk_date')],
                    ['👥 Guests',   ctx.val('bk_guests')],
                    ['📍 Location', ctx.val('bk_location')]
                ];
                var html = '<div class="ch-bk-summary-title">Your Order Summary</div><div class="ch-bk-summary-grid">';
                rows.forEach(function (r) {
                    if (!r[1]) return;
                    html += '<div class="ch-bk-summary-row"><span class="ch-bk-summary-label">' + r[0] + '</span><span class="ch-bk-summary-val">' + ctx.escHtml(r[1]) + '</span></div>';
                });
                return html + '</div>';
            },

            collectData: function (ctx) {
                var d = new FormData();
                d.append('bk_name',     ctx.val('bk_name'));
                d.append('bk_email',    ctx.val('bk_email'));
                d.append('bk_phone',    ctx.val('bk_phone'));
                d.append('bk_occasion', ctx.val('bk_occasion'));
                d.append('bk_date',     ctx.val('bk_date'));
                d.append('bk_guests',   ctx.val('bk_guests'));
                d.append('bk_location', ctx.val('bk_location'));
                d.append('bk_notes',    ctx.val('bk_notes'));
                ctx.form.querySelectorAll('[name="bk_cane[]"]:checked').forEach(function (c)    { d.append('bk_cane[]',    c.value); });
                ctx.form.querySelectorAll('[name="bk_flavour[]"]:checked').forEach(function (c) { d.append('bk_flavour[]', c.value); });
                return d;
            }
        });
    }

    // ── Franchise Enquiry Wizard ─────────────────────────────────────────────────
    function initFranchiseWizard() {
        if (typeof chStepModal !== 'function') return;
        chStepModal({
            prefix:         'frn',
            formId:         'ch-frn-form',
            action:         'ch_franchise_submit',
            successTitle:   'Enquiry Sent!',
            successMessage: "Thank you! We'll be in touch within 24 hours. 🌿",

            validateStep: function (ctx, step) {
                ctx.clearErrors();
                if (step === 1) {
                    var ok = true;
                    var city = ctx.form.querySelector('[name="frn_city"]');
                    var type = ctx.form.querySelector('[name="frn_type"]');
                    var time = ctx.form.querySelector('[name="frn_timeline"]');
                    if (!city.value.trim()) { ctx.fieldError(city.closest('.ch-bk-field'), 'Please enter a city or area.'); ok = false; }
                    if (!type.value.trim()) { ctx.fieldError(type.closest('.ch-bk-field'), 'Please select a franchise type.'); ok = false; }
                    if (!time.value.trim()) { ctx.fieldError(time.closest('.ch-bk-field'), 'Please select a timeline.'); ok = false; }
                    if (!ok) { ctx.showMsg('Please fill in all required fields.', 'error'); return false; }
                }
                if (step === 2) {
                    var inv = ctx.form.querySelector('[name="frn_investment"]');
                    if (!inv.value.trim()) { ctx.fieldError(inv.closest('.ch-bk-field'), 'Please select an investment range.'); ctx.showMsg('Please select your investment range.', 'error'); return false; }
                }
                return true;
            },

            buildSummary: function (ctx) {
                var rows = [
                    ['📍 City / Area',    ctx.val('frn_city')],
                    ['🏪 Franchise Type', ctx.val('frn_type')],
                    ['⏱ Timeline',        ctx.val('frn_timeline')],
                    ['💼 Investment',     ctx.val('frn_investment')]
                ];
                var html = '<div class="ch-bk-summary-title">Your Enquiry Summary</div><div class="ch-bk-summary-grid">';
                rows.forEach(function (r) {
                    if (!r[1]) return;
                    html += '<div class="ch-bk-summary-row"><span class="ch-bk-summary-label">' + r[0] + '</span><span class="ch-bk-summary-val">' + ctx.escHtml(r[1]) + '</span></div>';
                });
                return html + '</div>';
            },

            collectData: function (ctx) {
                var d = new FormData();
                d.append('frn_name',       ctx.val('frn_name'));
                d.append('frn_email',      ctx.val('frn_email'));
                d.append('frn_phone',      ctx.val('frn_phone'));
                d.append('frn_city',       ctx.val('frn_city'));
                d.append('frn_type',       ctx.val('frn_type'));
                d.append('frn_timeline',   ctx.val('frn_timeline'));
                d.append('frn_investment', ctx.val('frn_investment'));
                d.append('frn_experience', ctx.val('frn_experience'));
                d.append('frn_message',    ctx.val('frn_message'));
                return d;
            }
        });
    }

    // ── Order-to-Deliver Wizard ──────────────────────────────────────────────────
    function initOrderWizard() {
        if (typeof chStepModal !== 'function') return;
        chStepModal({
            prefix:         'otd',
            formId:         'ch-otd-form',
            action:         'ch_order_submit',
            successTitle:   'Order Request Sent!',
            successMessage: "Thanks! We'll review your order and contact you shortly. 🌿",
            // payload = all named fields (default new FormData(form))

            onInit: function (ctx) {
                // Quantity +/- buttons (otd-specific)
                ctx.form.querySelectorAll('.ch-otd-qty-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var input = btn.closest('.ch-otd-qty-wrap').querySelector('.ch-otd-qty-input');
                        var v = parseInt(input.value, 10) || 1;
                        input.value = btn.classList.contains('ch-otd-qty-plus') ? v + 1 : Math.max(1, v - 1);
                    });
                });
            },

            validateStep: function (ctx, step) {
                ctx.clearErrors();
                if (step === 1) {
                    var items  = ctx.form.querySelectorAll('[name="otd_items[]"]:checked').length;
                    var custom = ctx.form.querySelector('[name="otd_custom_item"]');
                    if (items === 0 && (!custom || !custom.value.trim())) { ctx.showMsg('Please select at least one item. 🥤', 'error'); return false; }
                }
                if (step === 2) {
                    var ok = true;
                    var addr = ctx.form.querySelector('[name="otd_address"]');
                    var area = ctx.form.querySelector('[name="otd_area"]');
                    if (!addr.value.trim()) { ctx.fieldError(addr.closest('.ch-bk-field'), 'Please enter your delivery address.'); ok = false; }
                    if (!area.value.trim()) { ctx.fieldError(area.closest('.ch-bk-field'), 'Please enter your area or city.'); ok = false; }
                    if (!ok) { ctx.showMsg('Please fill in your delivery details. 📦', 'error'); return false; }
                }
                return true;
            },

            buildSummary: function (ctx) {
                var items = Array.prototype.map.call(ctx.form.querySelectorAll('[name="otd_items[]"]:checked'), function (c) { return c.value; });
                var custom = (ctx.form.querySelector('[name="otd_custom_item"]') || {}).value || '';
                var itemLines = items.map(function (name) {
                    var qtyEl = ctx.form.querySelector('[name="otd_qty[' + name + ']"]');
                    return name + ' &times;' + (qtyEl ? qtyEl.value : '1');
                });
                if (custom) {
                    var cqty = (ctx.form.querySelector('[name="otd_custom_qty"]') || {}).value || '1';
                    itemLines.push(custom + ' &times;' + cqty);
                }
                var rows = [
                    ['🥤 Items',   itemLines.join('<br>')],
                    ['📦 Address', ctx.escHtml(ctx.val('otd_address'))],
                    ['📍 Area',    ctx.escHtml(ctx.val('otd_area'))],
                    ['📅 Date',    ctx.escHtml(ctx.val('otd_date'))],
                    ['🕐 Time',    ctx.escHtml(ctx.val('otd_time'))]
                ];
                var html = '<div class="ch-bk-summary-title">Your Order Summary</div><div class="ch-bk-summary-grid">';
                rows.forEach(function (r) {
                    if (!r[1]) return;
                    html += '<div class="ch-bk-summary-row"><span class="ch-bk-summary-label">' + r[0] + '</span><span class="ch-bk-summary-val">' + r[1] + '</span></div>';
                });
                return html + '</div>';
            }
        });
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    $(document).ready(function () {
        initContactForm();
        initBookingWizard();
        initFranchiseWizard();
        initOrderWizard();
        initNativeShare();
    });

}(jQuery));
