/* ── Cane House Contact Form — WordPress AJAX ──
   Replaces the Google Apps Script submission with
   a direct WordPress AJAX call that stores to DB.
   Also adds IP / page_url as hidden fields.
*/

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        var form     = document.querySelector('.contact-form');
        if (!form) return;

        var submitBtn = document.getElementById('submitBtn');
        var btnText   = document.getElementById('btnText');
        var statusDiv = document.getElementById('formStatus');

        if (!submitBtn) return;

        // Remove any old onclick
        submitBtn.removeAttribute('onclick');
        submitBtn.setAttribute('type', 'button');
        submitBtn.addEventListener('click', handleSubmit);

        function handleSubmit() {
            var name       = (document.getElementById('name')?.value       || '').trim();
            var email      = (document.getElementById('email')?.value      || '').trim();
            var mobile     = (document.getElementById('mobile')?.value     || '').trim();
            var queryType  = (document.getElementById('enquiry-type')?.value || '').trim();
            var query      = (document.getElementById('query')?.value      || '').trim();

            if (!name || !email || !query) {
                showStatus('Please fill in your name, email and message.', 'error');
                return;
            }
            if (!validateEmail(email)) {
                showStatus('Please enter a valid email address.', 'error');
                return;
            }

            submitBtn.disabled = true;
            btnText.textContent = 'Sending...';
            showStatus('', '');

            // Build form data
            var data = new FormData();
            data.append('action',     'ch_submit_lead');
            data.append('nonce',      (typeof CH_FORM !== 'undefined') ? CH_FORM.nonce : '');
            data.append('name',       name);
            data.append('email',      email);
            data.append('mobile',     mobile);
            data.append('query_type', queryType);
            data.append('query',      query);
            data.append('page_url',   window.location.href);

            var ajax_url = (typeof CH_FORM !== 'undefined') ? CH_FORM.ajax_url : '/wp-admin/admin-ajax.php';

            fetch(ajax_url, { method: 'POST', body: data })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    submitBtn.disabled = false;
                    if (res.success) {
                        btnText.textContent = "✓ Sent! We'll be in touch soon 🌿";
                        submitBtn.style.background = 'linear-gradient(135deg,#5a9a2a,#7ac040)';
                        showStatus(res.data.message, 'success');
                        // Clear fields
                        ['name','email','mobile','query'].forEach(function(id) {
                            var el = document.getElementById(id);
                            if (el) el.value = '';
                        });
                        var et = document.getElementById('enquiry-type');
                        if (et) et.value = '';
                        // Reset button after delay
                        setTimeout(function() {
                            btnText.textContent = 'Send Message 🥤';
                            submitBtn.style.background = '';
                        }, 5000);
                    } else {
                        btnText.textContent = 'Send Message 🥤';
                        showStatus(res.data?.message || 'Something went wrong. Please call us.', 'error');
                    }
                })
                .catch(function() {
                    submitBtn.disabled = false;
                    btnText.textContent = 'Send Message 🥤';
                    showStatus('Connection error. Please call us on +44 7887 699 208.', 'error');
                });
        }

        function showStatus(msg, type) {
            if (!statusDiv) return;
            if (!msg) { statusDiv.style.display = 'none'; statusDiv.textContent = ''; return; }
            statusDiv.textContent  = msg;
            statusDiv.className    = 'form-status ' + type;
            statusDiv.style.display = 'block';
        }

        function validateEmail(e) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);
        }
    });

})();
