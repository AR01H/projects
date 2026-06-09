/* ==========================================================================
   ptStepModal — reusable multi-step modal wizard controller.

   Pairs with components/forms/form_step_modal.php.
   Drives open/close, step nav, progress bar, summary, submit + success
   for ANY wizard. Each form supplies only what differs via callbacks.

   Required DOM (derived from prefix, e.g. 'consult'):
     #pt-{prefix}-form     the <form>            (or opts.formId)
     #pt-{prefix}-modal    the modal wrapper
     #pt-{prefix}-open     the button that opens it
     #pt-{prefix}-msg      feedback element
     #pt-{prefix}-submit   submit button
     #pt-{prefix}-summary  summary container
     [data-{prefix}-close] any close trigger
     .pt-bk-step / .pt-bk-next / .pt-bk-back / .pt-bk-prog-step

   Options:
     prefix         (string)  required
     formId         (string)  override form id
     action         (string)  WP ajax action
     sendingLabel   (string)  submit button label while sending
     successIcon / successTitle / successMessage
     allowJumpBack  (bool)    default true
     validateStep   (fn ctx,step → bool)
     buildSummary   (fn ctx → html)
     collectData    (fn ctx → FormData)
     onInit         (fn ctx)

   ctx: form, modal, step(), total, showMsg, hideMsg,
        clearErrors, fieldError, val(name), escHtml(str)
   ========================================================================== */
(function () {
	'use strict';

	var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

	function escHtml(s) {
		var d = document.createElement('div');
		d.textContent = (s == null ? '' : s);
		return d.innerHTML;
	}

	window.ptStepModal = function (opts) {
		opts = opts || {};
		var prefix = opts.prefix;
		if (!prefix || typeof ptTheme === 'undefined') return;

		var form    = document.getElementById(opts.formId || ('pt-' + prefix + '-form'));
		var modal   = document.getElementById('pt-' + prefix + '-modal');
		var openBtn = document.getElementById('pt-' + prefix + '-open');
		if (!form || !modal) return;

		var box       = modal.querySelector('.pt-bk-modal-box');
		var steps     = Array.prototype.slice.call(form.querySelectorAll('.pt-bk-step'));
		var progSteps = Array.prototype.slice.call(modal.querySelectorAll('.pt-bk-prog-step'));
		var progFill  = modal.querySelector('.pt-bk-prog-fill');
		var msgEl     = document.getElementById('pt-' + prefix + '-msg');
		var submitBtn = document.getElementById('pt-' + prefix + '-submit');
		var summary   = document.getElementById('pt-' + prefix + '-summary');
		var total     = steps.length;
		var current   = 1;
		var closeAttr = 'data-' + prefix + '-close';

		/* ── helpers ──────────────────────────────────────────────────────── */
		function showMsg(text, type) { if (!msgEl) return; msgEl.textContent = text; msgEl.className = 'pt-form-feedback ' + type; msgEl.style.display = 'block'; }
		function hideMsg() { if (msgEl) msgEl.style.display = 'none'; }
		function clearErrors() {
			form.querySelectorAll('.pt-field-error').forEach(function (e) { e.remove(); });
			form.querySelectorAll('.pt-bk-field').forEach(function (e) { e.classList.remove('invalid'); });
		}
		function fieldError(field, message) {
			if (!field) return;
			field.classList.add('invalid');
			var e = document.createElement('span');
			e.className = 'pt-field-error';
			e.textContent = message;
			field.appendChild(e);
		}
		function val(name) { var el = form.querySelector('[name="' + name + '"]'); return el ? String(el.value).trim() : ''; }

		var ctx = {
			form: form, modal: modal,
			step: function () { return current; }, total: total,
			showMsg: showMsg, hideMsg: hideMsg,
			clearErrors: clearErrors, fieldError: fieldError,
			val: val, escHtml: escHtml
		};

		/* ── open / close ─────────────────────────────────────────────────── */
		function openModal() {
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
			var prog = modal.querySelector('.pt-bk-progress');
			if (prog) prog.style.display = '';
			goTo(1);
		}
		function closeModal() {
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
		}
		if (openBtn) openBtn.addEventListener('click', openModal);
		modal.querySelectorAll('[' + closeAttr + ']').forEach(function (el) { el.addEventListener('click', closeModal); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal(); });

		/* ── step navigation ──────────────────────────────────────────────── */
		function goTo(step) {
			current = step;
			steps.forEach(function (s) { s.classList.toggle('active', parseInt(s.dataset.step, 10) === step); });
			progSteps.forEach(function (p) {
				var ps = parseInt(p.dataset.step, 10);
				p.classList.toggle('active', ps === step);
				p.classList.toggle('done', ps < step);
			});
			if (progFill && total > 1) progFill.style.width = ((step - 1) / (total - 1) * 100) + '%';
			if (step === total && typeof opts.buildSummary === 'function' && summary) {
				summary.innerHTML = opts.buildSummary(ctx) || '';
			}
			hideMsg();
			if (box) box.scrollTop = 0;
		}

		form.querySelectorAll('.pt-bk-next').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (typeof opts.validateStep === 'function' && !opts.validateStep(ctx, current)) return;
				goTo(parseInt(btn.dataset.next, 10));
			});
		});
		form.querySelectorAll('.pt-bk-back').forEach(function (btn) {
			btn.addEventListener('click', function () { goTo(parseInt(btn.dataset.back, 10)); });
		});
		if (opts.allowJumpBack !== false) {
			progSteps.forEach(function (p) {
				p.addEventListener('click', function () {
					var ps = parseInt(p.dataset.step, 10);
					if (ps < current) goTo(ps);
				});
			});
		}

		/* ── submit ───────────────────────────────────────────────────────── */
		form.addEventListener('submit', function (e) {
			e.preventDefault();

			var nameEl  = form.querySelector('[name="' + prefix + '_name"]');
			var emailEl = form.querySelector('[name="' + prefix + '_email"]');
			if (nameEl && !nameEl.value.trim()) { showMsg('Please enter your name.', 'error'); nameEl.focus(); return; }
			if (emailEl && (!emailEl.value.trim() || !EMAIL_RE.test(emailEl.value.trim()))) {
				showMsg('Please enter a valid email address.', 'error'); emailEl.focus(); return;
			}

			var originalText = submitBtn ? submitBtn.textContent : '';
			if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = opts.sendingLabel || 'Sending…'; }

			var data = (typeof opts.collectData === 'function') ? opts.collectData(ctx) : new FormData(form);
			data.append('action', opts.action);
			data.append('nonce', ptTheme.nonce);

			fetch(ptTheme.ajaxUrl, { method: 'POST', body: data })
				.then(function (r) { return r.json(); })
				.catch(function () { return null; })
				.then(function (res) {
					if (res && res.success) {
						renderSuccess(res.data && res.data.message);
					} else {
						showMsg((res && res.data && res.data.message) ? res.data.message : 'Something went wrong. Please try again.', 'error');
						if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
					}
				});

			function renderSuccess(message) {
				var lastStep = form.querySelector('.pt-bk-step[data-step="' + total + '"]');
				if (lastStep) {
					lastStep.innerHTML =
						'<div class="pt-bk-success">' +
						'<div class="pt-bk-success-icon">' + (opts.successIcon || '✓') + '</div>' +
						'<h3>' + (opts.successTitle || 'Thank you!') + '</h3>' +
						'<p>' + escHtml(message || opts.successMessage || "We'll be in touch shortly.") + '</p>' +
						'<button type="button" class="pt-btn pt-btn--primary" ' + closeAttr + ' style="margin-top:1.2rem;">Close</button>' +
						'</div>';
					var cb = lastStep.querySelector('[' + closeAttr + ']');
					if (cb) cb.addEventListener('click', closeModal);
				}
				var prog = modal.querySelector('.pt-bk-progress');
				if (prog) prog.style.display = 'none';
			}
		});

		if (typeof opts.onInit === 'function') opts.onInit(ctx);

		return { open: openModal, close: closeModal, goTo: goTo };
	};
})();
