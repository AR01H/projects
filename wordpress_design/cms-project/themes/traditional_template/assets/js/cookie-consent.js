/**
 * assets/js/cookie-consent.js - consent banner + granular preferences.
 *
 * One class, NTCookieConsent, owning one cookie. The payload is JSON:
 *
 *   { "v": "<version>", "<category>": 0|1, …, "ts": <unix-ms> }
 *
 *   - any optional category ON  -> stored for `acceptDays`
 *   - every optional category OFF -> stored for `rejectHours` only, so the
 *     banner comes back on its own and the visitor is asked again later
 *
 * "necessary" cookies are never in the payload: they are always on and the
 * visitor is never offered a switch for them.
 *
 * VERSIONING. `acceptVersion` and `rejectVersion` come from PHP
 * (admin/data/cookies.json). A stored payload whose `v` no longer matches is
 * treated as undecided, so bumping a version in JSON re-asks everyone (or
 * only the people who declined) without the server being able to reach into
 * anyone's browser.
 *
 * NO copy lives here - the banner text, the category labels and the toast
 * come from window.ntConsent, built by NT_Consent::js_config().
 *
 * Public API (window.ntConsent.api, also window.NT.consent):
 *   .status()                     'accepted' | 'rejected' | ''  (''= undecided)
 *   .preferences()                { <category>: 0|1 } or null while undecided
 *   .granted(category)            true|false  (always true for 'necessary')
 *   .acceptAll() / .rejectAll()
 *   .save({ analytics: 1, … })    write a custom set
 *   .open()                       open the preferences dialog on demand
 *   .onChange(fn)                 fires on every saved decision, with the prefs
 *   .onCategory(key, on, off)     fires whenever ONE category's value changes
 *   .reset()                      erase the cookie (testing) - banner returns
 *
 * Gate a third-party tag like this:
 *
 *   NT.consent.onCategory('analytics', loadAnalytics, unloadAnalytics);
 */
(function () {
	'use strict';

	var CFG = window.ntConsent || {};
	if (!CFG.enabled) { return; }

	/* ── Cookie access, isolated so nothing else has to parse document.cookie ── */

	class NTCookieJar {
		static read(name) {
			var pairs = document.cookie.split(';');
			for (var i = 0; i < pairs.length; i++) {
				var pair = pairs[i].trim();
				var eq = pair.indexOf('=');
				if (eq === -1) { continue; }
				if (pair.slice(0, eq).trim() === name) {
					return decodeURIComponent(pair.slice(eq + 1));
				}
			}
			return '';
		}
		static write(name, value, days) {
			var expires = new Date(Date.now() + days * 86400000).toUTCString();
			document.cookie = name + '=' + encodeURIComponent(value)
				+ '; path=/; expires=' + expires + '; SameSite=Lax'
				+ (window.location.protocol === 'https:' ? '; Secure' : '');
		}
		static erase(name) {
			document.cookie = name + '=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
		}
	}

	/* ══════════════════════════════════════════════════════════════════════ */

	/* ── The view. Builds the banner and the preferences panel from the config
	   PHP shipped, so no consent markup sits in the document for a visitor who
	   already answered - and there is no flash of a bar that then vanishes.
	   Every string is data; only structure lives here. ─────────────────────── */

	class NTConsentView {
		constructor(cfg) {
			this.cfg = cfg;
			this.text = cfg.text || {};
			this.icons = cfg.icons || {};
		}

		banner() {
			var text = this.text;

			var wrap = document.createElement('div');
			wrap.className = 'nt-consent nt-consent--' + (this.cfg.position || 'bottom');
			wrap.setAttribute('role', 'region');
			wrap.setAttribute('aria-label', text.aria || '');

			var paper = document.createElement('div');
			paper.className = 'nt-consent__paper';

			if (this.icons.shield) {
				var icon = document.createElement('span');
				icon.className = 'nt-consent__icon';
				icon.setAttribute('aria-hidden', 'true');
				icon.innerHTML = this.icons.shield;         // trusted: PHP-generated SVG
				paper.appendChild(icon);
			}

			var copy = document.createElement('div');
			copy.className = 'nt-consent__copy';
			if (text.kicker) { copy.appendChild(NTConsentView.el('span', 'nt-consent__kicker', text.kicker)); }
			if (text.title) { copy.appendChild(NTConsentView.el('p', 'nt-consent__title', text.title)); }

			var body = NTConsentView.el('p', 'nt-consent__text', text.body || '');
			if (this.cfg.policyUrl && text.policyLabel) {
				body.appendChild(document.createTextNode(' '));
				var policy = document.createElement('a');
				policy.className = 'nt-consent__policy';
				policy.href = this.cfg.policyUrl;
				policy.textContent = text.policyLabel;
				body.appendChild(policy);
			}
			copy.appendChild(body);
			paper.appendChild(copy);

			var actions = document.createElement('div');
			actions.className = 'nt-consent__actions';
			actions.appendChild(NTConsentView.button('nt-consent__btn nt-consent__btn--ghost', text.manage, 'manage'));
			actions.appendChild(NTConsentView.button('nt-consent__btn nt-consent__btn--ghost', text.rejectAll, 'reject'));
			actions.appendChild(NTConsentView.button('nt-consent__btn nt-consent__btn--primary', text.acceptAll, 'accept'));
			paper.appendChild(actions);

			wrap.appendChild(paper);
			return wrap;
		}

		/** The preferences panel, built as a dialog in the shared vintage style. */
		dialog() {
			var text = this.text;

			var dlg = document.createElement('dialog');
			dlg.id = 'nt-dialog-cookie-preferences';
			dlg.className = 'nt-dialog nt-dialog--note nt-dialog--md nt-consent-dialog';

			var form = document.createElement('form');
			form.method = 'dialog';
			form.className = 'nt-dialog__shell';

			var paper = document.createElement('div');
			paper.className = 'nt-dialog__paper';

			var close = document.createElement('button');
			close.type = 'submit';
			close.value = 'close';
			close.className = 'nt-dialog__close';
			close.setAttribute('aria-label', text.close || '');
			close.textContent = '×';
			paper.appendChild(close);

			var head = document.createElement('header');
			head.className = 'nt-dialog__head';
			if (text.prefsKicker) { head.appendChild(NTConsentView.el('span', 'nt-dialog__kicker', text.prefsKicker)); }
			head.appendChild(NTConsentView.el('h2', 'nt-dialog__title', text.prefsTitle || ''));
			var rule = NTConsentView.el('span', 'nt-dialog__rule', '');
			rule.setAttribute('aria-hidden', 'true');
			head.appendChild(rule);
			paper.appendChild(head);

			var bodyWrap = document.createElement('div');
			bodyWrap.className = 'nt-dialog__body';
			if (text.prefsText) { bodyWrap.appendChild(NTConsentView.el('p', 'nt-dialog__text', text.prefsText)); }

			var list = document.createElement('ul');
			list.className = 'nt-consent__list';
			(this.cfg.categories || []).forEach(function (category) {
				list.appendChild(this.row(category));
			}, this);
			bodyWrap.appendChild(list);
			paper.appendChild(bodyWrap);

			var actions = document.createElement('footer');
			actions.className = 'nt-dialog__actions';
			actions.appendChild(NTConsentView.button('nt-dialog__btn nt-dialog__btn--ghost', text.rejectAll, 'reject'));
			actions.appendChild(NTConsentView.button('nt-dialog__btn nt-dialog__btn--primary', text.savePrefs, 'save'));
			paper.appendChild(actions);

			['tl', 'tr', 'bl', 'br'].forEach(function (pos) {
				var corner = document.createElement('span');
				corner.className = 'nt-dialog__corner nt-dialog__corner--' + pos;
				corner.setAttribute('aria-hidden', 'true');
				paper.appendChild(corner);
			});

			form.appendChild(paper);
			dlg.appendChild(form);
			return dlg;
		}

		/** One category: copy on the left, switch (or a locked badge) on the right. */
		row(category) {
			var li = document.createElement('li');
			li.className = 'nt-consent__row' + (category.required ? ' is-locked' : '');

			var copy = document.createElement('div');
			copy.className = 'nt-consent__row-copy';
			copy.appendChild(NTConsentView.el('span', 'nt-consent__row-label', category.label));
			if (category.text) { copy.appendChild(NTConsentView.el('span', 'nt-consent__row-text', category.text)); }
			li.appendChild(copy);

			if (category.required) {
				var always = document.createElement('span');
				always.className = 'nt-consent__always';
				if (this.icons.lock) { always.innerHTML = this.icons.lock; }   // trusted: PHP-generated SVG
				always.appendChild(document.createTextNode(this.text.alwaysOn || ''));
				li.appendChild(always);
			} else {
				var label = document.createElement('label');
				label.className = 'nt-consent__switch';

				var input = document.createElement('input');
				input.type = 'checkbox';
				input.setAttribute('data-nt-consent-category', category.key);
				input.checked = !!category.default;
				label.appendChild(input);

				var slider = document.createElement('span');
				slider.className = 'nt-consent__slider';
				slider.setAttribute('aria-hidden', 'true');
				label.appendChild(slider);

				var sr = document.createElement('span');
				sr.className = 'screen-reader-text';
				sr.textContent = category.label;
				label.appendChild(sr);

				li.appendChild(label);
			}
			return li;
		}

		static el(tag, className, text) {
			var node = document.createElement(tag);
			node.className = className;
			node.textContent = text;          // textContent = XSS-safe
			return node;
		}

		static button(className, label, action) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = className;
			btn.textContent = label || '';
			btn.setAttribute('data-nt-consent-' + action, '');
			return btn;
		}
	}

	/* ══════════════════════════════════════════════════════════════════════ */

	class NTCookieConsent {
		constructor(config) {
			this.cfg = config;
			this.name = config.cookieName || 'nt_cookie_consent';
			// Only the categories the visitor can change are stored; required
			// ones are always on and never written into the cookie.
			this.categories = (config.categories || [])
				.filter(function (c) { return !c.required; })
				.map(function (c) { return c.key; });
			this.view = new NTConsentView(config);
			this.banner = null;
			this.dialog = null;
			this.changeListeners = [];
			this.categoryListeners = {};
			this.lastKnown = null;
		}

		/* ── Stored decision ───────────────────────────────────────────── */

		/**
		 * The saved preferences, or null when undecided / stale / corrupt.
		 * A stale or unreadable cookie is erased so the banner returns.
		 */
		preferences() {
			var raw = NTCookieJar.read(this.name);
			if (!raw || raw.charAt(0) !== '{') {
				if (raw) { NTCookieJar.erase(this.name); }
				return null;
			}

			var parsed;
			try {
				parsed = JSON.parse(raw);
			} catch (e) {
				NTCookieJar.erase(this.name);
				return null;
			}
			if (!parsed || typeof parsed !== 'object') {
				NTCookieJar.erase(this.name);
				return null;
			}

			var prefs = {};
			var anyOn = false;
			this.categories.forEach(function (key) {
				prefs[key] = parsed[key] ? 1 : 0;
				if (prefs[key]) { anyOn = true; }
			});

			// A full rejection is versioned separately, so "re-ask the people
			// who said no" doesn't disturb everyone who said yes.
			var expected = anyOn ? this.cfg.acceptVersion : this.cfg.rejectVersion;
			if (String(parsed.v) !== String(expected)) {
				NTCookieJar.erase(this.name);
				return null;
			}

			// A rejection also expires on its own, without waiting for a bump.
			if (!anyOn) {
				var age = Date.now() - (parseInt(parsed.ts, 10) || 0);
				if (!(age >= 0) || age > (this.cfg.rejectHours || 20) * 3600000) {
					NTCookieJar.erase(this.name);
					return null;
				}
			}

			return prefs;
		}

		status() {
			var prefs = this.preferences();
			if (!prefs) { return ''; }
			var anyOn = this.categories.some(function (key) { return prefs[key] === 1; });
			return anyOn ? 'accepted' : 'rejected';
		}

		granted(category) {
			if (this.categories.indexOf(category) === -1) { return true; }  // necessary
			var prefs = this.preferences();
			return !!(prefs && prefs[category]);
		}

		/* ── Writing a decision ────────────────────────────────────────── */

		save(choices) {
			var payload = {};
			var anyOn = false;

			this.categories.forEach(function (key) {
				payload[key] = choices && choices[key] ? 1 : 0;
				if (payload[key]) { anyOn = true; }
			});

			payload.v = anyOn ? this.cfg.acceptVersion : this.cfg.rejectVersion;
			payload.ts = Date.now();

			var days = anyOn ? (this.cfg.acceptDays || 365) : ((this.cfg.rejectHours || 20) / 24);
			NTCookieJar.write(this.name, JSON.stringify(payload), days);

			this.hideBanner();
			this.notify(payload);

			var toast = (this.cfg.labels && this.cfg.labels.saved) || '';
			if (toast && window.NT && typeof window.NT.toast === 'function') {
				window.NT.toast(toast, 'success');
			}
			return payload;
		}

		acceptAll() {
			var choices = {};
			this.categories.forEach(function (key) { choices[key] = 1; });
			return this.save(choices);
		}

		rejectAll() {
			return this.save({});
		}

		reset() {
			NTCookieJar.erase(this.name);
			this.lastKnown = null;
			this.showBanner();
		}

		/* ── Listeners ─────────────────────────────────────────────────── */

		onChange(fn) {
			if (typeof fn !== 'function') { return this; }
			this.changeListeners.push(fn);
			var prefs = this.preferences();
			if (prefs) { fn(prefs); }          // already decided - fire immediately
			return this;
		}

		/**
		 * Persistent per-category subscription. Unlike onChange it fires every
		 * time that one category flips, which is what a tag loader needs when
		 * the visitor reopens Manage Preferences and changes their mind.
		 */
		onCategory(key, onEnable, onDisable) {
			if (!this.categoryListeners[key]) { this.categoryListeners[key] = []; }
			this.categoryListeners[key].push({ on: onEnable, off: onDisable });

			var prefs = this.preferences();
			if (prefs) {
				var handler = prefs[key] ? onEnable : onDisable;
				if (typeof handler === 'function') { handler(); }
			}
			return this;
		}

		notify(payload) {
			var self = this;
			var previous = this.lastKnown;
			this.lastKnown = payload;

			this.changeListeners.forEach(function (fn) {
				try { fn(payload); } catch (e) { /* one bad listener must not stop the rest */ }
			});

			Object.keys(this.categoryListeners).forEach(function (key) {
				var now = payload[key] ? 1 : 0;
				if (previous && previous[key] === now) { return; }   // unchanged
				self.categoryListeners[key].forEach(function (pair) {
					var handler = now ? pair.on : pair.off;
					if (typeof handler === 'function') {
						try { handler(); } catch (e) { /* ignore */ }
					}
				});
			});

			document.dispatchEvent(new CustomEvent('nt:consent', { detail: payload }));
		}

		/* ── UI ────────────────────────────────────────────────────────── */

		showBanner() {
			if (!this.banner) {
				this.banner = this.view.banner();
				document.body.appendChild(this.banner);
				this.bindWithin(this.banner);
			}
			var el = this.banner;
			window.requestAnimationFrame(function () { el.classList.add('is-in'); });
		}

		hideBanner() {
			if (!this.banner) { return; }
			var el = this.banner;
			el.classList.remove('is-in');
			window.setTimeout(function () {
				if (el.parentNode) { el.parentNode.removeChild(el); }
			}, 320);
			this.banner = null;
		}

		/** Build the preferences panel the first time it is asked for. */
		ensureDialog() {
			if (this.dialog && document.contains(this.dialog)) { return this.dialog; }
			this.dialog = this.view.dialog();
			document.body.appendChild(this.dialog);
			this.bindWithin(this.dialog);
			return this.dialog;
		}

		open() {
			var dlg = this.ensureDialog();

			// Reflect the stored decision in the switches before showing them.
			var prefs = this.preferences();
			if (prefs) {
				dlg.querySelectorAll('[data-nt-consent-category]').forEach(function (input) {
					input.checked = !!prefs[input.getAttribute('data-nt-consent-category')];
				});
			}

			// Reuse the shared dialog machinery (focus trap, backdrop, Esc)
			// when ui-kit.js is present; fall back to the native call if not.
			if (window.NT && window.NT.dialog && typeof window.NT.dialog.open === 'function') {
				window.NT.dialog.open(dlg);
			} else if (typeof dlg.showModal === 'function') {
				dlg.showModal();
			}
		}

		close() {
			if (this.dialog && typeof this.dialog.close === 'function' && this.dialog.open) {
				this.dialog.close();
			}
		}

		readSwitches() {
			var choices = {};
			if (!this.dialog) { return choices; }
			this.dialog.querySelectorAll('[data-nt-consent-category]').forEach(function (input) {
				choices[input.getAttribute('data-nt-consent-category')] = input.checked ? 1 : 0;
			});
			return choices;
		}

		/** Wire the four actions inside a piece of consent UI we just built. */
		bindWithin(scope) {
			var self = this;
			var wire = function (attr, handler) {
				scope.querySelectorAll('[' + attr + ']').forEach(function (btn) {
					btn.addEventListener('click', handler);
				});
			};
			wire('data-nt-consent-accept', function () { self.acceptAll(); self.close(); });
			wire('data-nt-consent-reject', function () { self.rejectAll(); self.close(); });
			wire('data-nt-consent-manage', function () { self.open(); });
			wire('data-nt-consent-save', function () { self.save(self.readSwitches()); self.close(); });
			return this;
		}

		init() {
			var self = this;

			// Anything, anywhere, can reopen the panel later - a footer link
			// marked data-nt-consent-open is the usual way to meet the "you
			// must be able to change your mind" requirement.
			document.querySelectorAll('[data-nt-consent-open]').forEach(function (btn) {
				btn.addEventListener('click', function (e) { e.preventDefault(); self.open(); });
			});

			var prefs = this.preferences();
			if (prefs) {
				this.lastKnown = prefs;
				this.notify(prefs);          // let tag loaders act on the stored answer
			} else {
				this.showBanner();
			}
			return this;
		}
	}

	var consent = new NTCookieConsent(CFG);

	function boot() { consent.init(); }
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	var api = {
		status: function () { return consent.status(); },
		preferences: function () { return consent.preferences(); },
		granted: function (key) { return consent.granted(key); },
		acceptAll: function () { return consent.acceptAll(); },
		rejectAll: function () { return consent.rejectAll(); },
		save: function (choices) { return consent.save(choices); },
		open: function () { return consent.open(); },
		onChange: function (fn) { return consent.onChange(fn); },
		onCategory: function (key, on, off) { return consent.onCategory(key, on, off); },
		reset: function () { return consent.reset(); },
		instance: consent
	};

	window.ntConsent.api = api;
	window.NT = Object.assign(window.NT || {}, { consent: api });
}());
