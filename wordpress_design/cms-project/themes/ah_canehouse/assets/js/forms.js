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

    // ── Booking Wizard (modal) ──────────────────────────────────────────────────
    function initBookingWizard() {
        var wizard = document.getElementById('ch-booking-form');
        var modal  = document.getElementById('ch-bk-modal');
        var openBtn= document.getElementById('ch-bk-open');
        if (!wizard || !modal) return;

        var box      = modal.querySelector('.ch-bk-modal-box');
        var steps    = Array.prototype.slice.call(wizard.querySelectorAll('.ch-bk-step'));
        var progSteps= Array.prototype.slice.call(modal.querySelectorAll('.ch-bk-prog-step'));
        var progFill = modal.querySelector('.ch-bk-prog-fill');
        var msg      = document.getElementById('ch-bk-msg');
        var submitBtn= document.getElementById('ch-bk-submit');
        var summary  = document.getElementById('ch-bk-summary');
        var total    = steps.length;
        var current  = 1;

        // ── Modal open / close ──────────────────────────────────────────────────
        function openModal() {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            var prog = modal.querySelector('.ch-bk-progress');
            if (prog) prog.style.display = ''; // restore if hidden after a previous success
            goTo(1);
        }
        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
        if (openBtn) openBtn.addEventListener('click', openModal);
        modal.querySelectorAll('[data-bk-close]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });

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
            if (box) box.scrollTop = 0;
        }

        function selectedCanes() {
            return Array.prototype.map.call(
                wizard.querySelectorAll('[name="bk_cane[]"]:checked'),
                function (c) { return c.value; }
            );
        }
        function selectedFlavours() {
            return Array.prototype.map.call(
                wizard.querySelectorAll('[name="bk_flavour[]"]:checked'),
                function (c) { return c.value; }
            );
        }

        function buildSummary() {
            if (!summary) return;
            var rows = [
                ['🌾 Cane',     selectedCanes().join(', ')],
                ['🍋 Flavours', selectedFlavours().join(', ')],
                ['🎉 Occasion', wizard.querySelector('[name="bk_occasion"]').value],
                ['📅 Date',     wizard.querySelector('[name="bk_date"]').value],
                ['👥 Guests',   wizard.querySelector('[name="bk_guests"]').value],
                ['📍 Location', wizard.querySelector('[name="bk_location"]').value]
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

        // Validate before advancing each step
        function clearFieldErrors() {
            wizard.querySelectorAll('.ch-field-error').forEach(function (el) { el.remove(); });
            wizard.querySelectorAll('.ch-bk-field').forEach(function (el) { el.classList.remove('invalid'); });
        }
        function showFieldError(field, message) {
            field.classList.add('invalid');
            var err = document.createElement('span');
            err.className   = 'ch-field-error';
            err.textContent = message;
            field.appendChild(err);
        }
        function validateStep(step) {
            clearFieldErrors();
            if (step === 1 && selectedCanes().length === 0) {
                showMsg('Please choose at least one cane type. 🌾', 'error'); return false;
            }
            if (step === 2 && selectedFlavours().length === 0) {
                showMsg('Please pick at least one flavour. 🍋', 'error'); return false;
            }
            if (step === 4) {
                var occasion = wizard.querySelector('[name="bk_occasion"]');
                var date     = wizard.querySelector('[name="bk_date"]');
                var guests   = wizard.querySelector('[name="bk_guests"]');
                var location = wizard.querySelector('[name="bk_location"]');
                var ok       = true;

                if (!occasion.value.trim()) {
                    showFieldError(occasion.closest('.ch-bk-field'), 'Please select an occasion.');
                    ok = false;
                }
                if (!date.value.trim()) {
                    showFieldError(date.closest('.ch-bk-field'), 'Please enter the event date.');
                    ok = false;
                }
                if (!guests.value.trim() || parseInt(guests.value, 10) < 1) {
                    showFieldError(guests.closest('.ch-bk-field'), 'Please enter the number of guests (minimum 1).');
                    ok = false;
                }
                if (!location.value.trim()) {
                    showFieldError(location.closest('.ch-bk-field'), 'Please enter the venue location.');
                    ok = false;
                }
                if (!ok) {
                    showMsg('Please fill in all required event details. 📋', 'error');
                    return false;
                }
            }
            return true;
        }

        // Next buttons
        wizard.querySelectorAll('.ch-bk-next').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!validateStep(current)) return;
                goTo(parseInt(btn.dataset.next, 10));
            });
        });
        // Back buttons
        wizard.querySelectorAll('.ch-bk-back').forEach(function (btn) {
            btn.addEventListener('click', function () { goTo(parseInt(btn.dataset.back, 10)); });
        });
        // Jump back via progress steps
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

            var originalText = submitBtn.textContent;
            submitBtn.disabled    = true;
            submitBtn.textContent = 'Sending… 🌿';

            var data = new FormData();
            data.append('action',      'ch_booking_submit');
            data.append('nonce',       chTheme.nonce);
            data.append('bk_name',     name.value.trim());
            data.append('bk_email',    email.value.trim());
            data.append('bk_phone',    wizard.querySelector('[name="bk_phone"]').value.trim());
            data.append('bk_occasion', wizard.querySelector('[name="bk_occasion"]').value.trim());
            data.append('bk_date',     wizard.querySelector('[name="bk_date"]').value.trim());
            data.append('bk_guests',   wizard.querySelector('[name="bk_guests"]').value.trim());
            data.append('bk_location', wizard.querySelector('[name="bk_location"]').value.trim());
            data.append('bk_notes',    wizard.querySelector('[name="bk_notes"]').value.trim());
            selectedCanes().forEach(function(v)    { data.append('bk_cane[]',    v); });
            selectedFlavours().forEach(function(v) { data.append('bk_flavour[]', v); });

            fetch(chTheme.ajaxUrl, { method: 'POST', body: data })
                .then(function (r) { return r.json(); })
                .catch(function () { return null; }) // network / parse failure only
                .then(function (res) {
                    if (res && res.success) {
                        renderSuccess(res.data && res.data.message);
                    } else if (res) {
                        showMsg((res.data && res.data.message) || 'Something went wrong. Please try again.', 'error');
                        submitBtn.disabled    = false;
                        submitBtn.textContent = originalText;
                    } else {
                        showMsg('Connection error. Please try again.', 'error');
                        submitBtn.disabled    = false;
                        submitBtn.textContent = originalText;
                    }
                });

            function renderSuccess(message) {
                var step = wizard.querySelector('.ch-bk-step[data-step="5"]');
                if (step) {
                    step.innerHTML =
                        '<div class="ch-bk-success">' +
                        '<div class="ch-bk-success-icon">🎉</div>' +
                        '<h3>Order Request Sent!</h3>' +
                        '<p>' + escapeHtml(message || "Thanks! We'll be in touch very soon to confirm your order. 🌿") + '</p>' +
                        '<button type="button" class="btn-lime" data-bk-close style="margin-top:1.2rem;">Close</button>' +
                        '</div>';
                    var cb = step.querySelector('[data-bk-close]');
                    if (cb) cb.addEventListener('click', closeModal);
                }
                // Hide the step progress bar on the success screen
                var prog = modal.querySelector('.ch-bk-progress');
                if (prog) prog.style.display = 'none';
            }
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
                navigator.share({
                    title: title,
                    text: 'Check this out!',
                    url: url
                }).catch(function (err) {
                    if (err.name !== 'AbortError') console.error('Share error:', err);
                });
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    var originalText = shareBtn.innerHTML;
                    shareBtn.innerHTML = '✓ Link copied!';
                    setTimeout(function () {
                        shareBtn.innerHTML = originalText;
                    }, 2000);
                }).catch(function () {
                    alert('Copy to clipboard failed. URL: ' + url);
                });
            } else {
                prompt('Copy link:', url);
            }
        });
    }

    // ── Franchise Enquiry Wizard ────────────────────────────────────────────────
    function initFranchiseWizard() {
        var wizard = document.getElementById('ch-frn-form');
        var modal  = document.getElementById('ch-frn-modal');
        var openBtn= document.getElementById('ch-frn-open');
        if (!wizard || !modal) return;

        var box       = modal.querySelector('.ch-bk-modal-box');
        var steps     = Array.prototype.slice.call(wizard.querySelectorAll('.ch-bk-step'));
        var progSteps = Array.prototype.slice.call(modal.querySelectorAll('.ch-bk-prog-step'));
        var progFill  = modal.querySelector('.ch-bk-prog-fill');
        var msgEl     = document.getElementById('ch-frn-msg');
        var submitBtn = document.getElementById('ch-frn-submit');
        var summary   = document.getElementById('ch-frn-summary');
        var total     = steps.length;
        var current   = 1;

        function openModal()  { modal.classList.add('is-open');    modal.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; goTo(1); }
        function closeModal() { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true');  document.body.style.overflow = ''; }

        if (openBtn) openBtn.addEventListener('click', openModal);
        modal.querySelectorAll('[data-frn-close]').forEach(function(el){ el.addEventListener('click', closeModal); });
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal(); });

        function showMsg(text, type) { if (!msgEl) return; msgEl.textContent = text; msgEl.className = 'ch-form-feedback ' + type; msgEl.style.display = 'block'; }
        function hideMsg() { if (msgEl) msgEl.style.display = 'none'; }

        function goTo(step) {
            current = step;
            steps.forEach(function(s){ s.classList.toggle('active', parseInt(s.dataset.step,10) === step); });
            progSteps.forEach(function(p){ var ps = parseInt(p.dataset.step,10); p.classList.toggle('active', ps === step); p.classList.toggle('done', ps < step); });
            if (progFill) progFill.style.width = ((step - 1) / (total - 1) * 100) + '%';
            if (step === total) buildSummary();
            hideMsg();
            if (box) box.scrollTop = 0;
        }

        function val(name) { var el = wizard.querySelector('[name="' + name + '"]'); return el ? el.value.trim() : ''; }

        function buildSummary() {
            if (!summary) return;
            var rows = [
                ['📍 City / Area',     val('frn_city')],
                ['🏪 Franchise Type',  val('frn_type')],
                ['⏱ Timeline',         val('frn_timeline')],
                ['💼 Investment',       val('frn_investment')],
            ];
            var html = '<div class="ch-bk-summary-title">Your Enquiry Summary</div><div class="ch-bk-summary-grid">';
            rows.forEach(function(r){ if (!r[1]) return; html += '<div class="ch-bk-summary-row"><span class="ch-bk-summary-label">' + r[0] + '</span><span class="ch-bk-summary-val">' + r[1] + '</span></div>'; });
            html += '</div>';
            summary.innerHTML = html;
        }

        function clearErrors() { wizard.querySelectorAll('.ch-field-error').forEach(function(e){ e.remove(); }); wizard.querySelectorAll('.ch-bk-field').forEach(function(e){ e.classList.remove('invalid'); }); }
        function fieldError(field, msg) { field.classList.add('invalid'); var e = document.createElement('span'); e.className = 'ch-field-error'; e.textContent = msg; field.appendChild(e); }

        function validateStep(step) {
            clearErrors();
            if (step === 1) {
                var city = wizard.querySelector('[name="frn_city"]');
                var type = wizard.querySelector('[name="frn_type"]');
                var time = wizard.querySelector('[name="frn_timeline"]');
                var ok = true;
                if (!city.value.trim())  { fieldError(city.closest('.ch-bk-field'), 'Please enter a city or area.'); ok = false; }
                if (!type.value.trim())  { fieldError(type.closest('.ch-bk-field'), 'Please select a franchise type.'); ok = false; }
                if (!time.value.trim())  { fieldError(time.closest('.ch-bk-field'), 'Please select a timeline.'); ok = false; }
                if (!ok) { showMsg('Please fill in all required fields.', 'error'); return false; }
            }
            if (step === 2) {
                var inv = wizard.querySelector('[name="frn_investment"]');
                if (!inv.value.trim()) { fieldError(inv.closest('.ch-bk-field'), 'Please select an investment range.'); showMsg('Please select your investment range.', 'error'); return false; }
            }
            return true;
        }

        wizard.querySelectorAll('.ch-bk-next').forEach(function(btn){ btn.addEventListener('click', function(){ if (!validateStep(current)) return; goTo(parseInt(btn.dataset.next,10)); }); });
        wizard.querySelectorAll('.ch-bk-back').forEach(function(btn){ btn.addEventListener('click', function(){ goTo(parseInt(btn.dataset.back,10)); }); });

        wizard.addEventListener('submit', function(e) {
            e.preventDefault();
            var name  = wizard.querySelector('[name="frn_name"]');
            var email = wizard.querySelector('[name="frn_email"]');
            if (!name.value.trim())  { showMsg('Please enter your name.', 'error'); name.focus(); return; }
            if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { showMsg('Please enter a valid email address.', 'error'); email.focus(); return; }

            var originalText = submitBtn.textContent;
            submitBtn.disabled = true; submitBtn.textContent = 'Sending… 🌿';

            var data = new FormData();
            data.append('action',          'ch_franchise_submit');
            data.append('nonce',           chTheme.nonce);
            data.append('frn_name',        name.value.trim());
            data.append('frn_email',       email.value.trim());
            data.append('frn_phone',       val('frn_phone'));
            data.append('frn_city',        val('frn_city'));
            data.append('frn_type',        val('frn_type'));
            data.append('frn_timeline',    val('frn_timeline'));
            data.append('frn_investment',  val('frn_investment'));
            data.append('frn_experience',  val('frn_experience'));
            data.append('frn_message',     val('frn_message'));

            fetch(chTheme.ajaxUrl, { method: 'POST', body: data })
                .then(function(r){ return r.json(); })
                .catch(function(){ return null; })
                .then(function(res) {
                    if (res && res.success) {
                        var step = wizard.querySelector('.ch-bk-step[data-step="3"]');
                        if (step) {
                            step.innerHTML = '<div class="ch-bk-success"><div class="ch-bk-success-icon">🎉</div><h3>Enquiry Sent!</h3><p>' + (res.data.message || "Thank you! We'll be in touch within 24 hours. 🌿") + '</p><button type="button" class="btn-lime" data-frn-close style="margin-top:1.2rem;">Close</button></div>';
                            var cb = step.querySelector('[data-frn-close]');
                            if (cb) cb.addEventListener('click', closeModal);
                        }
                        var prog = modal.querySelector('.ch-bk-progress');
                        if (prog) prog.style.display = 'none';
                    } else {
                        showMsg((res && res.data && res.data.message) ? res.data.message : 'Something went wrong. Please try again.', 'error');
                        submitBtn.disabled = false; submitBtn.textContent = originalText;
                    }
                });
        });
    }

    // ── Order-to-Deliver Wizard ────────────────────────────────────────────────
    function initOrderWizard() {
        var form    = document.getElementById('ch-otd-form');
        var modal   = document.getElementById('ch-otd-modal');
        var openBtn = document.getElementById('ch-otd-open');
        if (!form || !modal) return;

        var box       = modal.querySelector('.ch-bk-modal-box');
        var steps     = Array.prototype.slice.call(form.querySelectorAll('.ch-bk-step'));
        var progSteps = Array.prototype.slice.call(modal.querySelectorAll('.ch-bk-prog-step'));
        var progFill  = modal.querySelector('.ch-bk-prog-fill');
        var msgEl     = document.getElementById('ch-otd-msg');
        var submitBtn = document.getElementById('ch-otd-submit');
        var summary   = document.getElementById('ch-otd-summary');
        var total     = steps.length;
        var current   = 1;

        // ── Modal open / close ───────────────────────────────────────────────
        function openModal()  { modal.classList.add('is-open');    modal.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; goTo(1); }
        function closeModal() { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true');  document.body.style.overflow=''; }

        if (openBtn) openBtn.addEventListener('click', openModal);
        modal.querySelectorAll('[data-otd-close]').forEach(function(el){ el.addEventListener('click', closeModal); });
        document.addEventListener('keydown', function(e){ if (e.key==='Escape' && modal.classList.contains('is-open')) closeModal(); });

        function showMsg(text, type) { if (!msgEl) return; msgEl.textContent=text; msgEl.className='ch-form-feedback '+type; msgEl.style.display='block'; }
        function hideMsg() { if (msgEl) msgEl.style.display='none'; }

        function goTo(step) {
            current = step;
            steps.forEach(function(s){ s.classList.toggle('active', parseInt(s.dataset.step,10)===step); });
            progSteps.forEach(function(p){ var ps=parseInt(p.dataset.step,10); p.classList.toggle('active',ps===step); p.classList.toggle('done',ps<step); });
            if (progFill) progFill.style.width = ((step-1)/(total-1)*100)+'%';
            if (step===total) buildSummary();
            hideMsg();
            if (box) box.scrollTop=0;
        }

        // ── Quantity +/- buttons ─────────────────────────────────────────────
        form.querySelectorAll('.ch-otd-qty-btn').forEach(function(btn){
            btn.addEventListener('click', function(){
                var input = btn.closest('.ch-otd-qty-wrap').querySelector('.ch-otd-qty-input');
                var v = parseInt(input.value,10) || 1;
                input.value = btn.classList.contains('ch-otd-qty-plus') ? v+1 : Math.max(1,v-1);
            });
        });

        // ── Validation ───────────────────────────────────────────────────────
        function clearFieldErrors() {
            form.querySelectorAll('.ch-field-error').forEach(function(e){ e.remove(); });
            form.querySelectorAll('.ch-bk-field').forEach(function(e){ e.classList.remove('invalid'); });
        }
        function fieldError(field, msg) {
            field.classList.add('invalid');
            var e = document.createElement('span'); e.className='ch-field-error'; e.textContent=msg; field.appendChild(e);
        }

        function selectedItems() {
            return Array.prototype.map.call(
                form.querySelectorAll('[name="otd_items[]"]:checked'),
                function(c){ return c.value; }
            );
        }

        function validateStep(step) {
            clearFieldErrors();
            if (step===1) {
                var custom = form.querySelector('[name="otd_custom_item"]');
                if (selectedItems().length===0 && (!custom || !custom.value.trim())) {
                    showMsg('Please select at least one item. 🥤', 'error'); return false;
                }
            }
            if (step===2) {
                var addr = form.querySelector('[name="otd_address"]');
                var area = form.querySelector('[name="otd_area"]');
                var ok = true;
                if (!addr.value.trim()) { fieldError(addr.closest('.ch-bk-field'), 'Please enter your delivery address.'); ok=false; }
                if (!area.value.trim()) { fieldError(area.closest('.ch-bk-field'), 'Please enter your area or city.'); ok=false; }
                if (!ok) { showMsg('Please fill in your delivery details. 📦', 'error'); return false; }
            }
            return true;
        }

        // ── Summary ──────────────────────────────────────────────────────────
        function escHtml(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

        function buildSummary() {
            if (!summary) return;
            var items = selectedItems();
            var custom = (form.querySelector('[name="otd_custom_item"]') || {}).value || '';
            var itemLines = items.map(function(name){
                var qtyEl = form.querySelector('[name="otd_qty['+name+']"]');
                var qty   = qtyEl ? qtyEl.value : '1';
                return name + ' &times;' + qty;
            });
            if (custom) {
                var cqty = (form.querySelector('[name="otd_custom_qty"]') || {}).value || '1';
                itemLines.push(custom + ' &times;' + cqty);
            }
            function fv(n){ var el=form.querySelector('[name="'+n+'"]'); return el ? el.value.trim() : ''; }
            var rows = [
                ['🥤 Items',   itemLines.join('<br>')],
                ['📦 Address', escHtml(fv('otd_address'))],
                ['📍 Area',    escHtml(fv('otd_area'))],
                ['📅 Date',    escHtml(fv('otd_date'))],
                ['🕐 Time',    escHtml(fv('otd_time'))],
            ];
            var html = '<div class="ch-bk-summary-title">Your Order Summary</div><div class="ch-bk-summary-grid">';
            rows.forEach(function(r){ if(!r[1]) return; html+='<div class="ch-bk-summary-row"><span class="ch-bk-summary-label">'+r[0]+'</span><span class="ch-bk-summary-val">'+r[1]+'</span></div>'; });
            html += '</div>';
            summary.innerHTML = html;
        }

        // ── Navigation ───────────────────────────────────────────────────────
        form.querySelectorAll('.ch-bk-next').forEach(function(btn){
            btn.addEventListener('click', function(){ if (!validateStep(current)) return; goTo(parseInt(btn.dataset.next,10)); });
        });
        form.querySelectorAll('.ch-bk-back').forEach(function(btn){
            btn.addEventListener('click', function(){ goTo(parseInt(btn.dataset.back,10)); });
        });

        // ── Submit ───────────────────────────────────────────────────────────
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var name  = form.querySelector('[name="otd_name"]');
            var email = form.querySelector('[name="otd_email"]');
            if (!name.value.trim())  { showMsg('Please enter your name.', 'error'); name.focus(); return; }
            if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                showMsg('Please enter a valid email address.', 'error'); email.focus(); return;
            }

            var originalText = submitBtn.textContent;
            submitBtn.disabled = true; submitBtn.textContent = 'Sending… 🌿';

            var data = new FormData(form);
            data.append('action', 'ch_order_submit');
            data.append('nonce',  chTheme.nonce);

            fetch(chTheme.ajaxUrl, { method:'POST', body:data })
                .then(function(r){ return r.json(); })
                .catch(function(){ return null; })
                .then(function(res){
                    if (res && res.success) {
                        var lastStep = form.querySelector('.ch-bk-step[data-step="'+total+'"]');
                        if (lastStep) {
                            lastStep.innerHTML =
                                '<div class="ch-bk-success">'+
                                '<div class="ch-bk-success-icon">🎉</div>'+
                                '<h3>Order Request Sent!</h3>'+
                                '<p>'+ escHtml(res.data && res.data.message ? res.data.message : "Thanks! We'll review your order and contact you shortly. 🌿") +'</p>'+
                                '<button type="button" class="btn-lime" data-otd-close style="margin-top:1.2rem;">Close</button>'+
                                '</div>';
                            var cb = lastStep.querySelector('[data-otd-close]');
                            if (cb) cb.addEventListener('click', closeModal);
                        }
                        var prog = modal.querySelector('.ch-bk-progress');
                        if (prog) prog.style.display = 'none';
                    } else {
                        showMsg((res && res.data && res.data.message) ? res.data.message : 'Something went wrong. Please try again.', 'error');
                        submitBtn.disabled = false; submitBtn.textContent = originalText;
                    }
                });
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
